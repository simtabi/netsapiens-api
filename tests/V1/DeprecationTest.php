<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\V1;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Every v1 class and trait stays deprecated, names the release it was deprecated in, and names the
 * earliest release that may remove it. 68 symbols were deprecated on 2026-10-21; the floor makes a
 * glob that stops matching fail instead of passing over nothing.
 */
final class DeprecationTest extends TestCase
{
    private const MIN_SYMBOLS = 68;

    public function test_every_v1_symbol_is_deprecated_with_its_removal_release(): void
    {
        $src   = dirname(__DIR__, 2) . '/src';
        $files = array_merge(
            [$src . '/NetSapiens.php', $src . '/Resources/Resource.php', $src . '/Resources/Auth/OAuth2.php', $src . '/Resources/Request/Request.php', $src . '/Helpers/Helpers.php'],
            glob($src . '/Traits/*.php') ?: [],
            glob($src . '/Resources/Objects/*.php') ?: [],
            glob($src . '/Resources/Objects/*/*.php') ?: [],
        );

        foreach ($files as $file) {
            $symbol = 'Simtabi\\NetSapiens\\' . str_replace('/', '\\', substr($file, strlen($src) + 1, -4));
            $doc    = (string) (new ReflectionClass($symbol))->getDocComment();

            $this->assertMatchesRegularExpression('/@deprecated 1\.1\.0 /', $doc, $symbol);
            $this->assertStringContainsString('no earlier than 2.0.0', $doc, $symbol);
        }

        $this->assertGreaterThanOrEqual(self::MIN_SYMBOLS, count($files));
    }
}
