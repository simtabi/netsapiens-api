<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Auth;

interface TokenStore
{
    public function get(string $key): ?Token;

    public function put(string $key, Token $token): void;

    public function forget(string $key): void;
}
