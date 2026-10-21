<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\V2;

use PHPUnit\Framework\TestCase;
use Simtabi\NetSapiens\Client;
use Simtabi\NetSapiens\Http\Request;
use Simtabi\NetSapiens\Http\Response;
use Simtabi\NetSapiens\Testing\FakeTransport;
use Simtabi\NetSapiens\Tests\Support\Credentials;
use Simtabi\NetSapiens\Tests\Support\LiveSweep;

/**
 * The live suite's sweep, run offline: it must only ever read, pass limit=1 where it can, and
 * sort answers so that a 4xx is reported and a 5xx or missing response fails.
 */
final class LiveSweepTest extends TestCase
{
    public function test_the_sweep_selects_only_argument_free_reads(): void
    {
        $system = LiveSweep::readOperations([]);
        $domain = LiveSweep::readOperations(['domain']);

        $this->assertCount(39, $system, 'measured 2026-10-21: 41 argument-free GETs, less /accesslog and /auditlog');
        $this->assertSame(['GET'], array_values(array_unique(array_column($domain, 'verb'))));
        $this->assertNotContains('/accesslog', array_column($system, 'path'));
        $this->assertGreaterThanOrEqual(50 + 39, count($domain), 'domain-only GETs plus the argument-free ones');
    }

    public function test_answers_are_sorted_and_only_gets_are_sent(): void
    {
        $fake = (new FakeTransport())->push(...array_fill(0, 200, static function (Request $request): Response {
            return match (true) {
                str_starts_with($request->path, '/apikeys')     => Response::json(403, ['code' => 403, 'message' => 'scope']),
                $request->path === '/restore'                   => Response::json(503, ['code' => 503, 'message' => 'down']),
                str_ends_with($request->path, '/count')         => Response::json(200, ['total' => 1]),
                default                                         => Response::json(200, [['domain' => 'example.test']]),
            };
        }));
        $client = Client::withApiKey('pbx.example.test', Credentials::key(), $fake);
        $ops    = LiveSweep::readOperations([]);

        $result = LiveSweep::run($client, $ops, []);

        $this->assertSame(['GET /apikeys: 403', 'GET /apikeys/~: 403'], $result['refused']);
        $this->assertCount(1, $result['failed']);
        $this->assertStringStartsWith('GET /restore: ', $result['failed'][0]);
        $this->assertCount(count($ops) - 3, $result['ok']);
        $this->assertSame(['GET'], array_values(array_unique(array_map(static fn (Request $r): string => $r->method, $fake->sent()))));
        $this->assertSame(1, $this->queryOf($fake, '/domains')['limit'] ?? null, 'a list call asks for one row');
        $this->assertStringContainsString('2 refused (4xx), 1 failed', LiveSweep::summary($result));
    }

    /** @return array<string, scalar> */
    private function queryOf(FakeTransport $fake, string $path): array
    {
        foreach ($fake->sent() as $request) {
            if ($request->path === $path) {
                return $request->query;
            }
        }

        return [];
    }
}
