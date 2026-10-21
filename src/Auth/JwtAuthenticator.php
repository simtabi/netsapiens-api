<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Auth;

/**
 * JSON Web Tokens from POST /jwt. The refresh JWT is single-use: the API revokes it and returns a
 * new pair.
 */
final class JwtAuthenticator extends TokenAuthenticator
{
    protected function issue(): Token
    {
        return $this->tokenFrom($this->post('jwt', $this->credentials->grant(), 'token'));
    }

    protected function renew(string $refreshToken): ?Token
    {
        return $this->tokenFrom($this->post('jwt', ['refresh' => $refreshToken], 'token'));
    }

    protected function scope(): string
    {
        return 'jwt';
    }

    /**
     * The expiry is read from the token's own exp claim. The signature is not checked: the API does
     * that, and the client only needs to know when to renew.
     */
    public static function expiryOf(string $jwt): ?int
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }

        $payload = json_decode((string) base64_decode(strtr($parts[1], '-_', '+/'), true), true);

        return is_array($payload) && isset($payload['exp']) && is_numeric($payload['exp']) ? (int) $payload['exp'] : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function tokenFrom(array $data): Token
    {
        $token = $data['token'];

        return new Token(
            $token,
            isset($data['refresh']) && is_string($data['refresh']) ? $data['refresh'] : null,
            self::expiryOf($token),
        );
    }
}
