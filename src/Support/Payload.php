<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Support;

/**
 * Builds request bodies: drops nulls and empty lists so optional fields are omitted.
 */
final class Payload
{
    /**
     * @param  array<array-key, mixed>  $payload
     * @return array<array-key, mixed>
     */
    public static function filter(array $payload): array
    {
        $filtered = [];

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $value = self::filter($value);

                if ($value === []) {
                    continue;
                }
            }

            if ($value !== null) {
                $filtered[$key] = $value;
            }
        }

        return array_is_list($payload) ? array_values($filtered) : $filtered;
    }
}
