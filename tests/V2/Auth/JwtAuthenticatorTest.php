<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\V2\Auth;

use PHPUnit\Framework\TestCase;
use Simtabi\NetSapiens\Auth\InMemoryTokenStore;
use Simtabi\NetSapiens\Auth\JwtAuthenticator;
use Simtabi\NetSapiens\Auth\PasswordCredentials;
use Simtabi\NetSapiens\Testing\FakeTransport;
use Simtabi\NetSapiens\Tests\Support\Credentials;

final class JwtAuthenticatorTest extends TestCase
{
    private int $now = 1_700_000_000;

    /** An unsigned token with the given payload; only the exp claim is read. */
    private static function jwt(array $payload): string
    {
        $part = static fn (array $data): string => rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');

        return $part(['alg' => 'none']) . '.' . $part($payload) . '.sig';
    }

    private function authenticator(FakeTransport $fake): JwtAuthenticator
    {
        return new JwtAuthenticator(
            $fake,
            new PasswordCredentials('client', Credentials::secret(), '1001@example.test', Credentials::password()),
            new InMemoryTokenStore(),
            fn (): int => $this->now,
        );
    }

    public function test_the_expiry_comes_from_the_exp_claim(): void
    {
        $this->assertSame(1_700_003_600, JwtAuthenticator::expiryOf(self::jwt(['exp' => 1_700_003_600, 'sub' => '1001@example.test'])));
        $this->assertNull(JwtAuthenticator::expiryOf(self::jwt(['sub' => 'no exp'])));
        $this->assertNull(JwtAuthenticator::expiryOf('not-a-jwt'));
    }

    public function test_the_refresh_jwt_is_exchanged_before_the_token_expires(): void
    {
        $first  = self::jwt(['exp' => $this->now + 600]);
        $second = self::jwt(['exp' => $this->now + 1200]);
        $fake   = (new FakeTransport())
            ->pushJson(['token' => $first, 'refresh' => 'r1'])
            ->pushJson(['token' => $second, 'refresh' => 'r2']);
        $auth   = $this->authenticator($fake);

        $this->assertSame($first, $auth->token()->accessToken);
        $this->now += 600;

        $this->assertSame($second, $auth->token()->accessToken);
        $this->assertSame('jwt', $fake->sent()[0]->path);
        $this->assertSame('password', $fake->sent()[0]->body['grant_type'] ?? null);
        $this->assertSame(['refresh' => 'r1'], $fake->sent()[1]->body);
    }
}
