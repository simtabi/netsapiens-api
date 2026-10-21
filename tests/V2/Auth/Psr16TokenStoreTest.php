<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\V2\Auth;

use PHPUnit\Framework\TestCase;
use Simtabi\NetSapiens\Auth\Psr16TokenStore;
use Simtabi\NetSapiens\Auth\Token;
use Simtabi\NetSapiens\Tests\Support\ArrayCache;

final class Psr16TokenStoreTest extends TestCase
{
    public function test_a_token_round_trips_through_the_cache_under_the_prefix(): void
    {
        $cache = new ArrayCache();
        $store = new Psr16TokenStore($cache);

        $store->put('k', new Token('at', 'rt', time() + 60));

        $this->assertSame(['simtabi.netsapiens.token.k'], array_keys($cache->values));
        $this->assertEquals(new Token('at', 'rt', $cache->values['simtabi.netsapiens.token.k']['expires']), $store->get('k'));
        $this->assertNull($cache->ttls['simtabi.netsapiens.token.k'], 'a refreshable token outlives its access expiry');

        $store->forget('k');
        $this->assertNull($store->get('k'));
    }

    public function test_a_token_that_cannot_be_refreshed_expires_with_its_access_token(): void
    {
        $cache = new ArrayCache();

        (new Psr16TokenStore($cache, 'p.'))->put('k', new Token('at', null, time() + 60));

        $this->assertGreaterThan(0, $cache->ttls['p.k']);
        $this->assertLessThanOrEqual(60, $cache->ttls['p.k']);
    }

    public function test_garbage_in_the_cache_reads_as_no_token(): void
    {
        $cache = new ArrayCache();
        $cache->values['simtabi.netsapiens.token.k'] = ['nope'];

        $this->assertNull((new Psr16TokenStore($cache))->get('k'));
    }
}
