<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\V2;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response as PsrResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Simtabi\NetSapiens\Auth\PasswordCredentials;
use Simtabi\NetSapiens\Client;
use Simtabi\NetSapiens\ClientConfig;
use Simtabi\NetSapiens\Exceptions\NotFoundException;
use Simtabi\NetSapiens\Http\GuzzleTransport;
use Simtabi\NetSapiens\Tests\Support\Credentials;

/**
 * The real Guzzle transport, client and authenticator together, with Guzzle's mock handler in
 * place of the network: what actually goes on the wire.
 */
final class EndToEndTest extends TestCase
{
    /** @var list<array{request: RequestInterface, options: array<string, mixed>}> */
    private array $history = [];

    private function transport(PsrResponse ...$responses): GuzzleTransport
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        return new GuzzleTransport(ClientConfig::forServer('pbx.example.test')->baseUri, new GuzzleClient(['handler' => $stack]));
    }

    private static function json(int $status, mixed $data): PsrResponse
    {
        return new PsrResponse($status, ['Content-Type' => 'application/json'], json_encode($data));
    }

    public function test_an_access_token_login_then_a_call_on_the_wire(): void
    {
        $transport = $this->transport(
            self::json(200, ['access_token' => 'at1', 'refresh_token' => 'rt1', 'expires_in' => 3600]),
            self::json(200, [['user' => '1001', 'domain' => 'example.test']]),
        );
        $ns = Client::withAccessToken(
            ClientConfig::forServer('pbx.example.test', logTag: 'e2e'),
            new PasswordCredentials('client', Credentials::secret(), '1001@example.test', Credentials::password()),
            transport: $transport,
        );

        $users = $ns->users()->getUsers('example.test', limit: 25);

        [$login, $call] = array_column($this->history, 'request');
        $this->assertSame('POST https://pbx.example.test/ns-api/v2/tokens', $login->getMethod() . ' ' . $login->getUri());
        $this->assertSame('application/json', $login->getHeaderLine('Content-Type'));
        $this->assertSame('password', json_decode((string) $login->getBody(), true)['grant_type']);

        $this->assertSame('GET https://pbx.example.test/ns-api/v2/domains/example.test/users?limit=25', $call->getMethod() . ' ' . $call->getUri());
        $this->assertSame('Bearer at1', $call->getHeaderLine('Authorization'));
        $this->assertSame('application/json', $call->getHeaderLine('Accept'));
        $this->assertSame('e2e', $call->getHeaderLine('X-NetSapiens-Log'));
        $this->assertSame('', (string) $call->getBody());
        $this->assertNotFalse($this->history[1]['options']['verify'] ?? true, 'TLS verification is not turned off');

        $this->assertSame('1001', $users[0]->user);
    }

    public function test_a_write_sends_json_and_a_404_becomes_not_found(): void
    {
        $transport = $this->transport(
            self::json(202, ['code' => 202, 'message' => 'Accepted']),
            self::json(404, ['code' => 404, 'message' => 'Domain not found']),
        );
        $ns = Client::withApiKey('pbx.example.test', Credentials::key(), $transport);

        $result = $ns->domains()->updateDomain('example.test', ['description' => 'Example']);

        $put = $this->history[0]['request'];
        $this->assertSame('PUT', $put->getMethod());
        $this->assertSame('https://pbx.example.test/ns-api/v2/domains/example.test', (string) $put->getUri());
        $this->assertSame('{"description":"Example"}', (string) $put->getBody());
        $this->assertSame('Accepted', $result->message);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('GET /domains/missing.test returned 404: Domain not found');

        $ns->domains()->getDomain('missing.test');
    }
}
