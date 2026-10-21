<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Auth;

use Closure;
use Simtabi\NetSapiens\Exceptions\ApiException;
use Simtabi\NetSapiens\Exceptions\AuthenticationException;
use Simtabi\NetSapiens\Http\Request;
use Simtabi\NetSapiens\Http\Transport;

/**
 * Logs in with a username and password, keeps the token in a store, renews it before it expires,
 * and logs in again when renewal fails or the API rejects it.
 */
abstract class TokenAuthenticator implements Authenticator
{
    private const SECRET_FIELDS = ['password', 'client_secret', 'refresh_token', 'refresh', 'passcode', 'access_token'];

    private readonly Closure $clock;

    /**
     * @param (Closure(): int)|null $clock unix time; defaults to time()
     */
    public function __construct(
        protected readonly Transport $transport,
        protected readonly PasswordCredentials $credentials,
        private readonly TokenStore $store = new InMemoryTokenStore(),
        ?Closure $clock = null,
    ) {
        $this->clock = $clock ?? static fn (): int => time();
    }

    /**
     * Exchange the credentials for a token.
     */
    abstract protected function issue(): Token;

    /**
     * Exchange a refresh token for a new token, or null when this flow cannot.
     */
    abstract protected function renew(string $refreshToken): ?Token;

    /**
     * Distinguishes this flow's tokens from another flow's in a shared store.
     */
    abstract protected function scope(): string;

    public function authorize(Request $request): Request
    {
        return $request->withHeader('Authorization', 'Bearer ' . $this->token()->accessToken);
    }

    public function refresh(): bool
    {
        $this->store->forget($this->key());

        return true;
    }

    public function token(): Token
    {
        $token = $this->store->get($this->key());

        if ($token !== null && !$token->isExpired($this->now())) {
            return $token;
        }

        $fresh = null;
        if ($token?->refreshToken !== null) {
            try {
                $fresh = $this->renew($token->refreshToken);
            } catch (ApiException) {
                $fresh = null; // a spent or revoked refresh token: log in again
            }
        }

        $fresh ??= $this->issue();
        $this->store->put($this->key(), $fresh);

        return $fresh;
    }

    protected function now(): int
    {
        return ($this->clock)();
    }

    /**
     * POST a grant and return the decoded body, which must carry $field as a non-empty string.
     *
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    protected function post(string $path, array $body, string $field): array
    {
        $request  = new Request('POST', $path, [], ['Accept' => 'application/json'], $body);
        $response = $this->transport->send($request);

        if (!$response->isSuccessful()) {
            throw ApiException::fromResponse(self::redacted($request), $response);
        }

        $data = $response->decoded();

        if (!is_array($data) || !isset($data[$field]) || !is_string($data[$field]) || $data[$field] === '') {
            throw new AuthenticationException(sprintf('POST %s returned no %s', $path, $field), $response->status, self::redacted($request), $response);
        }

        return $data;
    }

    /**
     * The request as an exception may carry it: exceptions get logged and reported, so the
     * password, secrets and tokens in a grant are replaced.
     */
    private static function redacted(Request $request): Request
    {
        $body = $request->body ?? [];
        foreach (self::SECRET_FIELDS as $field) {
            if (array_key_exists($field, $body)) {
                $body[$field] = '[redacted]';
            }
        }

        return new Request($request->method, $request->path, $request->query, $request->headers, $body, $request->multipart);
    }

    protected function expiresIn(mixed $seconds): ?int
    {
        return is_numeric($seconds) ? $this->now() + (int) $seconds : null;
    }

    private function key(): string
    {
        return $this->credentials->storeKey($this->scope());
    }
}
