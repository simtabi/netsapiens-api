<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\V1;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Simtabi\NetSapiens\Resources\Auth\OAuth2;
use Simtabi\NetSapiens\Resources\Objects\Device\Device;
use Simtabi\NetSapiens\Resources\Objects\Domain;
use Simtabi\NetSapiens\Tests\Support\Credentials;

/**
 * The v1 surface is deprecated, but four defects stopped it working at all. These pin the repairs:
 * D1 Domain::create sent action=read, D2 every body was a PHP array Guzzle refuses to send,
 * D3 a null cacher was a TypeError, D4 the refresh grant sent the password as the refresh token.
 */
final class LegacyRepairTest extends TestCase
{
    /** @var list<array{request: RequestInterface}> */
    private array $history = [];

    private function guzzleConfig(Response ...$responses): array
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        return ['handler' => $stack];
    }

    private function token(): Response
    {
        return new Response(200, [], json_encode(['access_token' => 'at', 'refresh_token' => 'rt', 'expires_in' => '3600']));
    }

    private function oauth(array $guzzleConfig): OAuth2
    {
        return (new OAuth2('https://pbx.example.test'))
            ->setGuzzleConfig($guzzleConfig)
            ->setClientId('client')
            ->setClientSecret(Credentials::secret())
            ->setUsername('1001@example.test')
            ->setPassword(Credentials::password());
    }

    /** @return array<string, string> */
    private function formOf(int $index): array
    {
        parse_str((string) $this->history[$index]['request']->getBody(), $form);

        return $form;
    }

    public function test_a_resource_accepts_a_null_cacher_and_sends_form_params(): void
    {
        $config = $this->guzzleConfig($this->token(), new Response(200, [], '[]'));

        $result = (new Domain($this->oauth($config), null, $config))->read(['domain' => 'example.test']);

        $this->assertTrue($result['status']);
        $this->assertCount(2, $this->history);
        $this->assertSame('application/x-www-form-urlencoded', $this->history[1]['request']->getHeaderLine('Content-Type'));
        $this->assertSame(['object' => 'domain', 'action' => 'read', 'domain' => 'example.test'], $this->formOf(1));
        $this->assertSame('Bearer at', $this->history[1]['request']->getHeaderLine('Authorization'));
    }

    public function test_domain_create_sends_the_create_action(): void
    {
        $config = $this->guzzleConfig($this->token(), new Response(200, [], ''));

        (new Domain($this->oauth($config), null, $config))->create(['domain' => 'example.test']);

        $this->assertSame('create', $this->formOf(1)['action']);
        $this->assertStringContainsString('action=create', $this->history[1]['request']->getUri()->getQuery());
    }

    public function test_set_device_password_targets_the_device_object(): void
    {
        $config = $this->guzzleConfig($this->token(), new Response(200, [], ''));

        (new Device($this->oauth($config), null, $config))->setDevicePassword([
            'contact' => '1001', 'domain' => 'example.test', 'user' => '1001', 'auth-key' => Credentials::secret(),
        ]);

        $this->assertSame('object=device&action=update', $this->history[1]['request']->getUri()->getQuery());
    }

    public function test_the_refresh_grant_sends_the_given_refresh_token(): void
    {
        $config = $this->guzzleConfig($this->token());
        $oauth  = $this->oauth($config);

        $this->assertTrue($oauth->generateRefreshToken('the-refresh-token', static fn (array $args): mixed => $args[0]));

        $sent = json_decode((string) $this->history[0]['request']->getBody(), true);
        $this->assertSame('refresh_token', $sent['grant_type']);
        $this->assertSame('the-refresh-token', $sent['refresh_token']);
        $this->assertSame(Credentials::password(), $oauth->getPassword(), 'the password is untouched');
    }
}
