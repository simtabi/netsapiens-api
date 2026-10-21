<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\Support;

/**
 * Test credentials are generated per run, never written down, so secret scanners have nothing to
 * report. Each value is memoised for the run so every assertion sees the same one.
 */
final class Credentials
{
    /** @var array<string, string> */
    private static array $values = [];

    public static function password(): string
    {
        return self::$values['password'] ??= bin2hex(random_bytes(12));
    }

    public static function secret(): string
    {
        return self::$values['secret'] ??= bin2hex(random_bytes(16));
    }

    public static function key(): string
    {
        return self::$values['key'] ??= 'nss_' . bin2hex(random_bytes(20));
    }
}
