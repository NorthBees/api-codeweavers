<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\Finance;

use NorthBees\CodeweaversApi\Support\Value;

/**
 * An option with a fixed set of allowed values, e.g. a product's terms or annual mileages.
 */
final readonly class ValuesOption
{
    /**
     * @param  list<int>  $values
     */
    public function __construct(
        public ?int $default,
        public array $values,
        public bool $isRequired = false,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): ?self
    {
        if ($data === []) {
            return null;
        }

        return new self(
            default: Value::int($data, 'Default'),
            values: Value::ints($data, 'Values'),
            isRequired: Value::bool($data, 'IsRequired'),
        );
    }
}
