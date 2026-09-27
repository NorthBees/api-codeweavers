<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Support;

/**
 * Typed reads from decoded JSON, tolerant of missing keys and numeric strings.
 */
final class Value
{
    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function string(array $data, string $key): ?string
    {
        $value = data_get($data, $key);

        return is_scalar($value) && (string) $value !== '' ? (string) $value : null;
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function float(array $data, string $key): ?float
    {
        $value = data_get($data, $key);

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function int(array $data, string $key): ?int
    {
        $value = data_get($data, $key);

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function bool(array $data, string $key): bool
    {
        return filter_var(data_get($data, $key, false), FILTER_VALIDATE_BOOL);
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<string, mixed>
     */
    public static function object(array $data, string $key): array
    {
        $value = data_get($data, $key);

        return is_array($value) ? $value : [];
    }

    /**
     * A list of objects, skipping anything that is not an array.
     *
     * @param  array<array-key, mixed>  $data
     * @return list<array<string, mixed>>
     */
    public static function list(array $data, string $key): array
    {
        $value = data_get($data, $key);

        return is_array($value) ? array_values(array_filter($value, is_array(...))) : [];
    }

    /**
     * A list of numbers.
     *
     * @param  array<array-key, mixed>  $data
     * @return list<int>
     */
    public static function ints(array $data, string $key): array
    {
        $value = data_get($data, $key);

        return is_array($value) ? array_values(array_map(intval(...), array_filter($value, is_numeric(...)))) : [];
    }
}
