<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Support;

use Closure;
use Generator;
use InvalidArgumentException;

/**
 * Walks a list endpoint through its start/limit parameters. start is a zero-based offset and
 * limit is 1 to 1000, both per the API's Start and Limit schemas.
 */
final class Paginator
{
    public const MAX_LIMIT = 1000;

    /**
     * @template T
     * @param Closure(int $start, int $limit): array<array-key, T> $fetch
     * @return Generator<int, T>
     */
    public static function each(Closure $fetch, int $limit = 100, int $start = 0): Generator
    {
        if ($limit < 1 || $limit > self::MAX_LIMIT) {
            throw new InvalidArgumentException(sprintf('limit must be between 1 and %d, %d given', self::MAX_LIMIT, $limit));
        }

        if ($start < 0) {
            throw new InvalidArgumentException('start must not be negative');
        }

        do {
            $page = $fetch($start, $limit);
            foreach ($page as $item) {
                yield $item;
            }
            $start += $limit;
        } while (count($page) >= $limit);
    }
}
