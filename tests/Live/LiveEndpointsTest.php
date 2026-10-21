<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\Live;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Simtabi\NetSapiens\Auth\PasswordCredentials;
use Simtabi\NetSapiens\Client;
use Simtabi\NetSapiens\ClientConfig;
use Simtabi\NetSapiens\Tests\Support\LiveSweep;

/**
 * Calls a real NetSapiens server. Read-only: only GET operations run, and only those that need
 * no argument beyond the domain.
 *
 * Not part of the default suite. Run it with `composer test-live` after setting:
 *
 *     NETSAPIENS_LIVE_SERVER     pbx.example.com
 *     NETSAPIENS_LIVE_API_KEY    nss_…              (or the four OAuth variables below)
 *     NETSAPIENS_LIVE_CLIENT_ID, NETSAPIENS_LIVE_CLIENT_SECRET,
 *     NETSAPIENS_LIVE_USERNAME, NETSAPIENS_LIVE_PASSWORD
 *     NETSAPIENS_LIVE_DOMAIN     optional; the domain-scoped sweep uses "~" (your own) otherwise
 *
 * A 4xx answer is reported, not failed: it depends on the credential's scope and the server's
 * licensed features. A 5xx, a network failure, or no successful call at all fails the run.
 */
#[Group('live')]
final class LiveEndpointsTest extends TestCase
{
    private static function env(string $name): ?string
    {
        $value = getenv($name);

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function client(): Client
    {
        $server = self::env('NETSAPIENS_LIVE_SERVER');
        if ($server === null) {
            $this->markTestSkipped('Set NETSAPIENS_LIVE_SERVER and credentials to run the live tests.');
        }

        $config = ClientConfig::forServer($server, timeout: 20.0, logTag: 'simtabi-netsapiens-api-live-test');

        $key = self::env('NETSAPIENS_LIVE_API_KEY');
        if ($key !== null) {
            return Client::withApiKey($config, $key);
        }

        $id = self::env('NETSAPIENS_LIVE_CLIENT_ID');
        $secret = self::env('NETSAPIENS_LIVE_CLIENT_SECRET');
        $user = self::env('NETSAPIENS_LIVE_USERNAME');
        $password = self::env('NETSAPIENS_LIVE_PASSWORD');
        if ($id === null || $secret === null || $user === null || $password === null) {
            $this->markTestSkipped('Set NETSAPIENS_LIVE_API_KEY, or all four NETSAPIENS_LIVE_CLIENT_ID/_CLIENT_SECRET/_USERNAME/_PASSWORD.');
        }

        return Client::withAccessToken($config, new PasswordCredentials($id, $secret, $user, $password));
    }

    public function test_system_wide_reads_answer_without_server_errors(): void
    {
        $client = $this->client();
        $ops = LiveSweep::readOperations([]);

        $result = LiveSweep::run($client, $ops, []);
        fwrite(STDERR, "\n" . LiveSweep::summary($result));

        $this->assertGreaterThanOrEqual(30, count($ops), 'the sweep found the operations it should');
        $this->assertSame([], $result['failed']);
        $this->assertNotEmpty($result['ok'], 'at least one read succeeded, so the credential works');
    }

    public function test_domain_reads_answer_without_server_errors(): void
    {
        $client = $this->client();
        $ops = LiveSweep::readOperations(['domain']);
        $ops = array_values(array_filter($ops, static fn (array $op): bool => $op['params'] !== []));

        $result = LiveSweep::run($client, $ops, ['domain' => self::env('NETSAPIENS_LIVE_DOMAIN') ?? '~']);
        fwrite(STDERR, "\n" . LiveSweep::summary($result));

        $this->assertGreaterThanOrEqual(30, count($ops), 'the sweep found the operations it should');
        $this->assertSame([], $result['failed']);
        $this->assertNotEmpty($result['ok'], 'at least one domain read succeeded');
    }

    public function test_the_version_endpoint_answers(): void
    {
        $this->assertNotNull($this->client()->version()->version());
    }
}
