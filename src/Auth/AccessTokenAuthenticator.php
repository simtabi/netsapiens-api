<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Auth;

/**
 * OAuth2 access tokens from POST /tokens (password, MFA and refresh_token grants).
 */
final class AccessTokenAuthenticator extends TokenAuthenticator
{
    protected function issue(): Token
    {
        return $this->tokenFrom($this->post('tokens', $this->credentials->grant(), 'access_token'));
    }

    protected function renew(string $refreshToken): ?Token
    {
        return $this->tokenFrom($this->post('tokens', [
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->credentials->clientId,
            'client_secret' => $this->credentials->clientSecret,
            'refresh_token' => $refreshToken,
        ], 'access_token'));
    }

    protected function scope(): string
    {
        return 'tokens';
    }

    /**
     * @param array<string, mixed> $data
     */
    private function tokenFrom(array $data): Token
    {
        return new Token(
            $data['access_token'],
            isset($data['refresh_token']) && is_string($data['refresh_token']) ? $data['refresh_token'] : null,
            $this->expiresIn($data['expires_in'] ?? null),
        );
    }
}
