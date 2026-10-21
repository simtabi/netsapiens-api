<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Auth;

/**
 * Keeps tokens for the life of the process. The default.
 */
final class InMemoryTokenStore implements TokenStore
{
    /** @var array<string, Token> */
    private array $tokens = [];

    public function get(string $key): ?Token
    {
        return $this->tokens[$key] ?? null;
    }

    public function put(string $key, Token $token): void
    {
        $this->tokens[$key] = $token;
    }

    public function forget(string $key): void
    {
        unset($this->tokens[$key]);
    }
}
