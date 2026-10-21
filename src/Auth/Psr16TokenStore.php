<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Auth;

use Psr\SimpleCache\CacheInterface;

/**
 * Shares tokens across processes through any PSR-16 cache, so each worker does not log in again.
 */
final class Psr16TokenStore implements TokenStore
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly string $prefix = 'simtabi.netsapiens.token.',
    ) {
    }

    public function get(string $key): ?Token
    {
        $value = $this->cache->get($this->prefix . $key);

        return is_array($value) ? Token::fromArray($value) : null;
    }

    public function put(string $key, Token $token): void
    {
        // Keep a token that can be refreshed past its access expiry; the refresh grant needs it.
        $ttl = $token->expiresAt !== null && $token->refreshToken === null ? max(1, $token->expiresAt - time()) : null;

        $this->cache->set($this->prefix . $key, $token->toArray(), $ttl);
    }

    public function forget(string $key): void
    {
        $this->cache->delete($this->prefix . $key);
    }
}
