<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\Finance;

use NorthBees\CodeweaversApi\Support\Value;

/**
 * A numeric option with a default and bounds, e.g. a product's deposit.
 */
final readonly class RangeOption
{
    public function __construct(
        public ?float $default,
        public ?float $minimum,
        public ?float $maximum,
        public bool $isRequired = false,
        public ?float $defaultAsPercentageOfOtr = null,
        public ?float $minimumAsPercentageOfOtr = null,
        public ?float $maximumAsPercentageOfOtr = null,
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
            default: Value::float($data, 'Default'),
            minimum: Value::float($data, 'Minimum'),
            maximum: Value::float($data, 'Maximum'),
            isRequired: Value::bool($data, 'IsRequired'),
            defaultAsPercentageOfOtr: Value::float($data, 'DefaultAsPercentageOfOtr'),
            minimumAsPercentageOfOtr: Value::float($data, 'MinimumAsPercentageOfOtr'),
            maximumAsPercentageOfOtr: Value::float($data, 'MaximumAsPercentageOfOtr'),
        );
    }
}
