<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\V2\Auth;

use PHPUnit\Framework\TestCase;
use Simtabi\NetSapiens\Auth\AccessTokenAuthenticator;
use Simtabi\NetSapiens\Auth\InMemoryTokenStore;
use Simtabi\NetSapiens\Auth\PasswordCredentials;
use Simtabi\NetSapiens\Client;
use Simtabi\NetSapiens\Exceptions\AuthenticationException;
use Simtabi\NetSapiens\Exceptions\ValidationException;
use Simtabi\NetSapiens\Http\Request;
use Simtabi\NetSapiens\Testing\FakeTransport;
use Simtabi\NetSapiens\Tests\Support\Credentials;

final class AccessTokenAuthenticatorTest extends TestCase
{
    private int $now = 1_700_000_000;

    private function credentials(?string $passcode = null): PasswordCredentials
    {
        return new PasswordCredentials('client', Credentials::secret(), '1001@example.test', Credentials::password(), $passcode);
    }

    private function authenticator(FakeTransport $fake, ?PasswordCredentials $credentials = null, ?InMemoryTokenStore $store = null): AccessTokenAuthenticator
    {
        return new AccessTokenAuthenticator($fake, $credentials ?? $this->credentials(), $store ?? new InMemoryTokenStore(), fn (): int => $this->now);
    }

    /** @return array<string, mixed> */
    private static function body(Request $request): array
    {
        return $request->body ?? [];
    }

    public function test_the_password_grant_is_posted_once_and_the_token_reused(): void
    {
        $fake = (new FakeTransport())->pushJson(['access_token' => 'at1', 'refresh_token' => 'rt1', 'expires_in' => 3600]);
        $auth = $this->authenticator($fake);

        $this->assertSame('Bearer at1', $auth->authorize(new Request('GET', 'domains'))->header('Authorization'));
        $this->assertSame('Bearer at1', $auth->authorize(new Request('GET', 'users'))->header('Authorization'));

        $this->assertCount(1, $fake->sent());
        $this->assertSame('tokens', $fake->sent()[0]->path);
        $this->assertSame([
            'grant_type' => 'password', 'client_id' => 'client', 'client_secret' => Credentials::secret(),
            'username' => '1001@example.test', 'password' => Credentials::password(),
        ], self::body($fake->sent()[0]));
    }

    public function test_an_expiring_token_is_renewed_with_the_refresh_grant(): void
    {
        $fake = (new FakeTransport())
            ->pushJson(['access_token' => 'at1', 'refresh_token' => 'rt1', 'expires_in' => 3600])
            ->pushJson(['access_token' => 'at2', 'refresh_token' => 'rt2', 'expires_in' => 3600]);
        $auth = $this->authenticator($fake);

        $auth->token();
        $this->now += 3600 - 29; // inside the 30 second leeway

        $this->assertSame('at2', $auth->token()->accessToken);
        $this->assertSame(['grant_type' => 'refresh_token', 'client_id' => 'client', 'client_secret' => Credentials::secret(), 'refresh_token' => 'rt1'], self::body($fake->sent()[1]));
    }

    public function test_a_rejected_refresh_falls_back_to_logging_in_again(): void
    {
        $fake = (new FakeTransport())
            ->pushJson(['access_token' => 'at1', 'refresh_token' => 'rt1', 'expires_in' => 60])
            ->pushJson(['code' => 400, 'message' => 'invalid_grant'], 400)
            ->pushJson(['access_token' => 'at3', 'expires_in' => 60]);
        $auth = $this->authenticator($fake);

        $auth->token();
        $this->now += 120;

        $this->assertSame('at3', $auth->token()->accessToken);
        $this->assertSame('password', self::body($fake->sent()[2])['grant_type']);
    }

    public function test_a_401_on_a_call_forgets_the_token_and_logs_in_again(): void
    {
        $fake = (new FakeTransport())
            ->pushJson(['access_token' => 'at1', 'expires_in' => 3600])
            ->pushJson(['code' => 401, 'message' => 'revoked'], 401)
            ->pushJson(['access_token' => 'at2', 'expires_in' => 3600])
            ->pushJson(['total' => 1]);

        $count = (new Client($fake, $this->authenticator($fake)))->domains()->countDomains();

        $this->assertSame(1, $count->total);
        $this->assertSame(['tokens', '/domains/count', 'tokens', '/domains/count'], array_map(static fn (Request $r): string => $r->path, $fake->sent()));
        $this->assertSame('Bearer at2', $fake->sent()[3]->header('Authorization'));
    }

    public function test_the_mfa_grant_sends_the_passcode(): void
    {
        $fake = (new FakeTransport())->pushJson(['access_token' => 'at1']);

        $this->authenticator($fake, $this->credentials('123456'))->token();

        $body = self::body($fake->sent()[0]);
        $this->assertSame('mfa', $body['grant_type']);
        $this->assertSame(['google', 'authenticator', '123456'], [$body['mfa_vendor'], $body['mfa_type'], $body['passcode']]);
    }

    public function test_a_token_without_expiry_is_kept_until_the_api_rejects_it(): void
    {
        $fake = (new FakeTransport())->pushJson(['access_token' => 'at1']);
        $auth = $this->authenticator($fake);

        $auth->token();
        $this->now += 86_400 * 365;

        $this->assertSame('at1', $auth->token()->accessToken);
        $this->assertCount(1, $fake->sent());
    }

    public function test_a_failed_login_raises_the_api_error(): void
    {
        $fake = (new FakeTransport())->pushJson(['code' => 400, 'message' => 'invalid username or password'], 400);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('invalid username or password');

        $this->authenticator($fake)->token();
    }

    public function test_a_response_without_an_access_token_is_an_authentication_error(): void
    {
        $fake = (new FakeTransport())->pushJson(['mfa_required' => 'yes']);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('POST tokens returned no access_token');

        $this->authenticator($fake)->token();
    }

    public function test_the_store_key_does_not_contain_the_password(): void
    {
        $key = $this->credentials()->storeKey('tokens');

        $this->assertStringNotContainsString(Credentials::password(), $key);
        $this->assertNotSame($key, $this->credentials()->storeKey('jwt'));
    }
}
