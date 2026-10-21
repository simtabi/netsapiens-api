<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\V2;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Simtabi\NetSapiens\ClientConfig;

final class ClientConfigTest extends TestCase
{
    /** @return iterable<string, array{string, string}> */
    public static function servers(): iterable
    {
        yield 'bare host'          => ['pbx.example.test', 'https://pbx.example.test/ns-api/v2'];
        yield 'url'                => ['https://pbx.example.test/', 'https://pbx.example.test/ns-api/v2'];
        yield 'http kept'          => ['http://10.0.0.5:8080', 'http://10.0.0.5:8080/ns-api/v2'];
        yield 'already has path'   => ['https://pbx.example.test/ns-api/v2/', 'https://pbx.example.test/ns-api/v2'];
        yield 'whitespace trimmed' => ['  pbx.example.test ', 'https://pbx.example.test/ns-api/v2'];
    }

    #[DataProvider('servers')]
    public function test_for_server_builds_the_v2_base_uri(string $server, string $expected): void
    {
        $this->assertSame($expected, ClientConfig::forServer($server)->baseUri);
    }

    public function test_an_empty_server_is_refused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ClientConfig::forServer(' ');
    }
}
