<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\V1;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Simtabi\NetSapiens\Resources\Auth\OAuth2;
use Simtabi\NetSapiens\Resources\Objects\Domain;
use Simtabi\NetSapiens\Tests\Support\Credentials;

/**
 * The v1 resources turned TLS certificate checks off unless the caller turned them on, so a
 * man in the middle could read the bearer token on every call. Verification is now on unless
 * the caller turns it off.
 */
final class TlsVerificationTest extends TestCase
{
    /** @var list<array{options: array<string, mixed>}> */
    private array $history = [];

    /** @param array<string, mixed> $extra */
    private function read(array $extra): void
    {
        $stack = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'at'])),
            new Response(200, [], '[]'),
        ]));
        $stack->push(Middleware::history($this->history));
        $config = ['handler' => $stack] + $extra;

        $oauth = (new OAuth2('https://pbx.example.test'))
            ->setGuzzleConfig($config)
            ->setClientId('client')
            ->setClientSecret(Credentials::secret())
            ->setUsername('1001@example.test')
            ->setPassword(Credentials::password());

        (new Domain($oauth, null, $config))->read(['domain' => 'example.test']);
    }

    public function test_certificates_are_verified_by_default(): void
    {
        $this->read([]);

        $this->assertCount(2, $this->history);
        $this->assertTrue($this->history[1]['options']['verify']);
    }

    public function test_a_caller_can_still_turn_verification_off(): void
    {
        $this->read(['verify' => false]);

        $this->assertFalse($this->history[1]['options']['verify']);
    }

    public function test_a_caller_can_pass_a_ca_bundle(): void
    {
        $this->read(['verify' => '/etc/ssl/internal-ca.pem']);

        $this->assertSame('/etc/ssl/internal-ca.pem', $this->history[1]['options']['verify']);
    }
}
