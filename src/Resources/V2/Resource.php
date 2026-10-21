<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Resources\V2;

use Simtabi\NetSapiens\Client;
use Simtabi\NetSapiens\Data\Model;

/**
 * Base for the generated v2 resource classes: fills the path template, drops unset query values,
 * and maps a response onto a model when the operation's schema has one.
 */
abstract class Resource
{
    final public function __construct(protected readonly Client $client)
    {
    }

    /**
     * @param array<string, string>                $path
     * @param array<string, scalar|null>           $query
     * @param array<array-key, mixed>|null         $body
     * @param list<array<string, mixed>>|null      $multipart
     */
    protected function call(
        string $method,
        string $template,
        array $path = [],
        array $query = [],
        ?array $body = null,
        ?array $multipart = null,
        bool $raw = false,
    ): mixed {
        return $this->client->call($method, self::expand($template, $path), $query, $body, $multipart, $raw);
    }

    /**
     * Fill a path template. Each value is URL-encoded, so a "/" stays inside its segment, and a
     * value that is empty, "." or ".." is refused, because a server that normalises the path would
     * otherwise send the call to a different endpoint.
     *
     * @param array<string, string> $path
     */
    public static function expand(string $template, array $path): string
    {
        return (string) preg_replace_callback(
            '/\{([^}]+)\}/',
            static function (array $m) use ($path): string {
                $value = $path[$m[1]] ?? throw new \LogicException("Missing path parameter {$m[1]}");

                if (in_array($value, ['', '.', '..'], true)) {
                    throw new \InvalidArgumentException(sprintf('Path parameter %s cannot be "%s"', $m[1], $value));
                }

                return rawurlencode($value);
            },
            $template,
        );
    }

    /**
     * @template T of Model
     * @param class-string<T> $model
     * @return T
     */
    protected function one(string $model, mixed $data): Model
    {
        return $model::fromArray(is_array($data) ? $data : []);
    }

    /**
     * @template T of Model
     * @param class-string<T> $model
     * @return list<T>
     */
    protected function many(string $model, mixed $data): array
    {
        if (!is_array($data)) {
            return [];
        }

        // A single object where the schema promised a list is still one item.
        if ($data !== [] && !array_is_list($data)) {
            $data = [$data];
        }

        return array_values(array_map(static fn (mixed $item): Model => $model::fromArray(is_array($item) ? $item : []), $data));
    }
}
