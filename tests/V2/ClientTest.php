<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\V2;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Simtabi\NetSapiens\Auth\Authenticator;
use Simtabi\NetSapiens\Client;
use Simtabi\NetSapiens\ClientConfig;
use Simtabi\NetSapiens\Exceptions\ApiException;
use Simtabi\NetSapiens\Exceptions\AuthenticationException;
use Simtabi\NetSapiens\Exceptions\AuthorizationException;
use Simtabi\NetSapiens\Exceptions\ConflictException;
use Simtabi\NetSapiens\Exceptions\NetSapiensException;
use Simtabi\NetSapiens\Exceptions\NotFoundException;
use Simtabi\NetSapiens\Exceptions\ServerException;
use Simtabi\NetSapiens\Exceptions\ValidationException;
use Simtabi\NetSapiens\Http\Request;
use Simtabi\NetSapiens\Http\Response;
use Simtabi\NetSapiens\Testing\FakeTransport;
use Simtabi\NetSapiens\Tests\Support\Credentials;

final class ClientTest extends TestCase
{
    public function test_an_api_key_is_sent_as_a_bearer_with_json_accept_and_a_user_agent(): void
    {
        $fake = (new FakeTransport())->pushJson(['total' => 3]);

        $total = Client::withApiKey('pbx.example.test', Credentials::key(), $fake)->domains()->countDomains()->total;

        $this->assertSame(3, $total);
        $this->assertSame('Bearer ' . Credentials::key(), $fake->last()?->header('Authorization'));
        $this->assertSame('application/json', $fake->last()?->header('Accept'));
        $this->assertSame('simtabi/netsapiens-api', $fake->last()?->header('User-Agent'));
        $this->assertNull($fake->last()?->header('X-NetSapiens-Log'));
    }

    public function test_a_log_tag_is_sent_as_the_netsapiens_log_header(): void
    {
        $fake = (new FakeTransport())->pushJson([]);

        Client::withApiKey(ClientConfig::forServer('pbx.example.test', logTag: 'ticket-42'), Credentials::key(), $fake)->call('GET', 'version');

        $this->assertSame('ticket-42', $fake->last()?->header('X-NetSapiens-Log'));
    }

    public function test_call_drops_null_query_values_and_spells_booleans_as_the_api_does(): void
    {
        $fake = (new FakeTransport())->pushJson([]);

        Client::withApiKey('pbx.example.test', Credentials::key(), $fake)->call('get', 'configurations/count', ['a' => null, 'include_wildcards' => false, 'limit' => 5]);

        $this->assertSame('GET', $fake->last()?->method);
        $this->assertSame(['include_wildcards' => 'false', 'limit' => 5], $fake->last()?->query);
    }

    public function test_call_returns_the_raw_body_on_request(): void
    {
        $fake = (new FakeTransport())->push(new Response(200, 'RIFF....WAVE'));

        $this->assertSame('RIFF....WAVE', Client::withApiKey('pbx.example.test', Credentials::key(), $fake)->call('GET', 'x', raw: true));
    }

    /** @return iterable<string, array{int, class-string<ApiException>}> */
    public static function statuses(): iterable
    {
        yield '400' => [400, ValidationException::class];
        yield '401' => [401, AuthenticationException::class];
        yield '403' => [403, AuthorizationException::class];
        yield '404' => [404, NotFoundException::class];
        yield '409' => [409, ConflictException::class];
        yield '500' => [500, ServerException::class];
        yield '503' => [503, ServerException::class];
        yield '429' => [429, ApiException::class];
    }

    /** @param class-string<ApiException> $class */
    #[DataProvider('statuses')]
    public function test_error_statuses_become_typed_exceptions_carrying_the_api_message(int $status, string $class): void
    {
        $fake = (new FakeTransport())->pushJson(['code' => $status, 'message' => 'Domain example.test not found'], $status);

        try {
            Client::withApiKey('pbx.example.test', Credentials::key(), $fake)->domains()->getDomain('example.test');
            $this->fail('no exception');
        } catch (ApiException $e) {
            $this->assertSame($class, $e::class);
            $this->assertInstanceOf(NetSapiensException::class, $e, 'catchable as the package exception');
            $this->assertSame($status, $e->status);
            $this->assertSame('GET /domains/example.test returned ' . $status . ': Domain example.test not found', $e->getMessage());
            $this->assertSame('/domains/example.test', $e->request->path);
        }
    }

    public function test_a_401_is_retried_once_when_the_authenticator_renews(): void
    {
        $auth = new class () implements Authenticator {
            public int $refreshes = 0;

            public function authorize(Request $request): Request
            {
                return $request->withHeader('Authorization', 'Bearer t' . $this->refreshes);
            }

            public function refresh(): bool
            {
                $this->refreshes++;

                return true;
            }
        };
        $fake = (new FakeTransport())->push(new Response(401), Response::json(200, ['ok' => true]));

        $result = (new Client($fake, $auth))->call('GET', 'version');

        $this->assertSame(['ok' => true], $result);
        $this->assertSame(1, $auth->refreshes);
        $this->assertSame(['Bearer t0', 'Bearer t1'], array_map(static fn (Request $r): ?string => $r->header('Authorization'), $fake->sent()));
    }

    public function test_a_second_401_is_not_retried_again(): void
    {
        $fake = (new FakeTransport())->push(new Response(401), new Response(401), Response::json(200, []));
        $auth = new class () implements Authenticator {
            public function authorize(Request $request): Request
            {
                return $request;
            }

            public function refresh(): bool
            {
                return true;
            }
        };

        try {
            (new Client($fake, $auth))->call('GET', 'version');
            $this->fail('no exception');
        } catch (AuthenticationException) {
            $this->assertCount(2, $fake->sent());
        }
    }

    public function test_an_api_key_is_not_retried_after_a_401(): void
    {
        $fake = (new FakeTransport())->push(new Response(401), Response::json(200, []));

        $this->expectException(AuthenticationException::class);

        try {
            Client::withApiKey('pbx.example.test', Credentials::key(), $fake)->call('GET', 'version');
        } finally {
            $this->assertCount(1, $fake->sent());
        }
    }

    public function test_resource_accessors_return_the_same_instance(): void
    {
        $client = Client::withApiKey('pbx.example.test', Credentials::key(), new FakeTransport());

        $this->assertSame($client->users(), $client->users());
    }
}
