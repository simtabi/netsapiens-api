<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens;

use Simtabi\NetSapiens\Auth\AccessTokenAuthenticator;
use Simtabi\NetSapiens\Auth\Authenticator;
use Simtabi\NetSapiens\Auth\BearerTokenAuthenticator;
use Simtabi\NetSapiens\Auth\InMemoryTokenStore;
use Simtabi\NetSapiens\Auth\JwtAuthenticator;
use Simtabi\NetSapiens\Auth\PasswordCredentials;
use Simtabi\NetSapiens\Auth\TokenStore;
use Simtabi\NetSapiens\Exceptions\ApiException;
use Simtabi\NetSapiens\Exceptions\TransportException;
use Simtabi\NetSapiens\Http\GuzzleTransport;
use Simtabi\NetSapiens\Http\Request;
use Simtabi\NetSapiens\Http\Response;
use Simtabi\NetSapiens\Http\Transport;
use Simtabi\NetSapiens\Resources\V2\Accessors;

/**
 * The NetSapiens API v2 client.
 *
 *     $ns = Client::withApiKey('pbx.example.com', $apiKey);
 *     $ns->domains()->getDomains(limit: 100);
 *
 * One resource accessor per section of docs.ns-api.com (see Accessors), plus call() for anything
 * the generated methods do not cover.
 */
final class Client
{
    use Accessors;

    /** @var array<class-string, object> */
    private array $resources = [];

    public function __construct(
        private readonly Transport $transport,
        private readonly ?Authenticator $authenticator = null,
        private readonly ?ClientConfig $config = null,
    ) {
    }

    /**
     * Server-to-server access with an API key (nss_…) issued in the Admin UI or by POST /apikeys.
     */
    public static function withApiKey(ClientConfig|string $server, string $apiKey, ?Transport $transport = null): self
    {
        return self::withToken($server, $apiKey, $transport);
    }

    /**
     * A bearer token you already hold: an access token or JWT obtained elsewhere.
     */
    public static function withToken(ClientConfig|string $server, string $token, ?Transport $transport = null): self
    {
        $config = self::config($server);

        return new self($transport ?? self::transportFor($config), new BearerTokenAuthenticator($token), $config);
    }

    /**
     * OAuth2 access tokens from POST /tokens, renewed with the refresh token before they expire.
     */
    public static function withAccessToken(
        ClientConfig|string $server,
        PasswordCredentials $credentials,
        TokenStore $store = new InMemoryTokenStore(),
        ?Transport $transport = null,
    ): self {
        $config    = self::config($server);
        $transport ??= self::transportFor($config);

        return new self($transport, new AccessTokenAuthenticator($transport, $credentials, $store), $config);
    }

    /**
     * JSON Web Tokens from POST /jwt, renewed with the refresh JWT before they expire.
     */
    public static function withJwt(
        ClientConfig|string $server,
        PasswordCredentials $credentials,
        TokenStore $store = new InMemoryTokenStore(),
        ?Transport $transport = null,
    ): self {
        $config    = self::config($server);
        $transport ??= self::transportFor($config);

        return new self($transport, new JwtAuthenticator($transport, $credentials, $store), $config);
    }

    /**
     * Send a request, retrying once after a 401 when the authenticator could renew its credentials.
     *
     * @throws ApiException       the API answered with an error status
     * @throws TransportException no answer arrived
     */
    public function send(Request $request): Response
    {
        $request = $this->prepare($request);

        $response = $this->transport->send($this->authorize($request));

        if ($response->status === 401 && $this->authenticator?->refresh() === true) {
            $response = $this->transport->send($this->authorize($request));
        }

        if (!$response->isSuccessful()) {
            throw ApiException::fromResponse($request, $response);
        }

        return $response;
    }

    /**
     * Call any path under /ns-api/v2 and get the decoded JSON back (or the raw body when $raw).
     *
     * @param array<string, scalar|null>      $query null values are left out
     * @param array<array-key, mixed>|null    $body  sent as JSON
     * @param list<array<string, mixed>>|null $multipart
     */
    public function call(
        string $method,
        string $path,
        array $query = [],
        ?array $body = null,
        ?array $multipart = null,
        bool $raw = false,
    ): mixed {
        $query    = array_filter($query, static fn (mixed $value): bool => $value !== null);
        $query    = array_map(static fn (mixed $value): mixed => is_bool($value) ? ($value ? 'true' : 'false') : $value, $query);
        $response = $this->send(new Request(strtoupper($method), $path, $query, [], $body, $multipart));

        return $raw ? $response->body : $response->decoded();
    }

    public function transport(): Transport
    {
        return $this->transport;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function resource(string $class): object
    {
        /** @var T */
        return $this->resources[$class] ??= new $class($this);
    }

    private function prepare(Request $request): Request
    {
        $request = $request
            ->withHeader('Accept', $request->header('Accept') ?? 'application/json')
            ->withHeader('User-Agent', $this->config?->userAgent ?? 'simtabi/netsapiens-api');

        $logTag = $this->config?->logTag;

        return $logTag === null ? $request : $request->withHeader('X-NetSapiens-Log', $logTag);
    }

    private function authorize(Request $request): Request
    {
        return $this->authenticator === null ? $request : $this->authenticator->authorize($request);
    }

    private static function config(ClientConfig|string $server): ClientConfig
    {
        return $server instanceof ClientConfig ? $server : ClientConfig::forServer($server);
    }

    private static function transportFor(ClientConfig $config): Transport
    {
        return new GuzzleTransport($config->baseUri, null, $config->timeout);
    }
}
