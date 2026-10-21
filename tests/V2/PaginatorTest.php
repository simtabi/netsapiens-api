<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\V2;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Simtabi\NetSapiens\Client;
use Simtabi\NetSapiens\Data\Domain;
use Simtabi\NetSapiens\Http\Request;
use Simtabi\NetSapiens\Support\Paginator;
use Simtabi\NetSapiens\Testing\FakeTransport;
use Simtabi\NetSapiens\Tests\Support\Credentials;

final class PaginatorTest extends TestCase
{
    public function test_pages_are_walked_until_a_short_page(): void
    {
        $calls = [];
        $items = iterator_to_array(Paginator::each(static function (int $start, int $limit) use (&$calls): array {
            $calls[] = [$start, $limit];

            return array_slice(range(1, 5), $start, $limit);
        }, 2), false);

        $this->assertSame([1, 2, 3, 4, 5], $items);
        $this->assertSame([[0, 2], [2, 2], [4, 2]], $calls);
    }

    public function test_an_exact_multiple_costs_one_empty_page(): void
    {
        $calls = 0;
        $items = iterator_to_array(Paginator::each(static function (int $start, int $limit) use (&$calls): array {
            $calls++;

            return array_slice([1, 2, 3, 4], $start, $limit);
        }, 2), false);

        $this->assertSame([1, 2, 3, 4], $items);
        $this->assertSame(3, $calls);
    }

    public function test_it_drives_a_generated_list_method(): void
    {
        $fake = (new FakeTransport())
            ->pushJson([['domain' => 'a.test'], ['domain' => 'b.test']])
            ->pushJson([['domain' => 'c.test']]);
        $ns   = Client::withApiKey('pbx.example.test', Credentials::key(), $fake);

        $names = array_map(
            static fn (Domain $d): ?string => $d->domain,
            iterator_to_array(Paginator::each(static fn (int $start, int $limit): array => $ns->domains()->getDomains($start, $limit), 2), false),
        );

        $this->assertSame(['a.test', 'b.test', 'c.test'], $names);
        $this->assertSame([['start' => 0, 'limit' => 2], ['start' => 2, 'limit' => 2]], array_map(static fn (Request $r): array => $r->query, $fake->sent()));
    }

    public function test_the_limit_must_be_one_the_api_accepts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        iterator_to_array(Paginator::each(static fn (): array => [], 1001));
    }
}
