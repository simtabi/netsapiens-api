<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\Support;

use ReflectionClass;
use ReflectionMethod;
use Simtabi\NetSapiens\Client;
use Simtabi\NetSapiens\Exceptions\ApiException;
use Simtabi\NetSapiens\Exceptions\ServerException;
use Throwable;

/**
 * Picks the read-only operations a live run can call safely and calls them, sorting each into
 * ok, refused (4xx) or failed (5xx or no response).
 */
final class LiveSweep
{
    /** No limit parameter and potentially very large; left out of an automatic sweep. */
    public const SKIPPED_PATHS = ['/accesslog', '/auditlog'];

    /**
     * GET operations whose only required arguments are path parameters named in $allowed.
     *
     * @param list<string> $allowed
     * @return list<array{class: class-string, method: string, path: string, params: array<string, string>, query: array<string, string>}>
     */
    public static function readOperations(array $allowed): array
    {
        $ops = [];
        foreach (require dirname(__DIR__) . '/Fixtures/operations.php' as $op) {
            if ($op['verb'] !== 'GET' || in_array($op['path'], self::SKIPPED_PATHS, true) || array_diff(array_keys($op['params']), $allowed) !== []) {
                continue;
            }
            $required = array_filter((new ReflectionMethod($op['class'], $op['method']))->getParameters(), static fn ($p): bool => !$p->isOptional());
            if (count($required) !== count($op['params'])) {
                continue; // needs a required query parameter that cannot be chosen safely
            }
            $ops[] = $op;
        }

        return $ops;
    }

    /**
     * @param list<array{class: class-string, method: string, path: string, params: array<string, string>, query: array<string, string>}> $ops
     * @param array<string, string> $values path values by API name
     * @return array{ok: list<string>, refused: list<string>, failed: list<string>}
     */
    public static function run(Client $client, array $ops, array $values): array
    {
        $result = ['ok' => [], 'refused' => [], 'failed' => []];

        foreach ($ops as $op) {
            $args = [];
            foreach ($op['params'] as $apiName => $argName) {
                $args[$argName] = $values[$apiName];
            }
            if (isset($op['query']['limit'])) {
                $args[$op['query']['limit']] = 1;
            }

            $label = "GET {$op['path']}";
            try {
                $client->{lcfirst((new ReflectionClass($op['class']))->getShortName())}()->{$op['method']}(...$args);
                $result['ok'][] = $label;
            } catch (ServerException $e) {
                $result['failed'][] = "{$label}: {$e->getMessage()}";
            } catch (ApiException $e) {
                $result['refused'][] = "{$label}: {$e->status}";
            } catch (Throwable $e) {
                $result['failed'][] = "{$label}: " . $e::class . ' ' . $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * @param array{ok: list<string>, refused: list<string>, failed: list<string>} $result
     */
    public static function summary(array $result): string
    {
        $lines = [sprintf('%d ok, %d refused (4xx), %d failed', count($result['ok']), count($result['refused']), count($result['failed']))];
        foreach ($result['refused'] as $line) {
            $lines[] = "  refused: {$line}";
        }
        foreach ($result['failed'] as $line) {
            $lines[] = "  FAILED: {$line}";
        }

        return implode("\n", $lines) . "\n";
    }
}
