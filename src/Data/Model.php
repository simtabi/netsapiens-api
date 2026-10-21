<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Data;

/**
 * A response object. Documented fields are typed properties; the whole payload, including fields
 * the documentation does not list, stays available through toArray().
 *
 * Values are coerced to the documented type when they can be ("12" to 12, "yes" to true) and are
 * null when they cannot, so a server that formats a field differently never throws.
 */
abstract class Model
{
    /**
     * @param array<string, mixed> $data
     */
    abstract public static function fromArray(array $data): static;

    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;

    protected static function str(mixed $value): ?string
    {
        return is_scalar($value) ? (string) $value : null;
    }

    protected static function int(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        return is_numeric($value) && (float) $value == (int) $value ? (int) $value : null;
    }

    protected static function float(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    protected static function bool(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return match (is_scalar($value) ? strtolower((string) $value) : null) {
            '1', 'yes', 'true', 'on'  => true,
            '0', 'no', 'false', 'off' => false,
            default                   => null,
        };
    }

    /**
     * @return array<array-key, mixed>|null
     */
    protected static function arr(mixed $value): ?array
    {
        return is_array($value) ? $value : null;
    }
}
