<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\Finance;

use NorthBees\CodeweaversApi\Support\Value;

/**
 * A labelled, pre-formatted value from a quotation's display blocks.
 */
final readonly class DisplayDetail
{
    public function __construct(
        public ?string $key,
        public ?string $label,
        public ?string $displayValue,
        public ?string $value,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            key: Value::string($data, 'Key'),
            label: Value::string($data, 'Label'),
            displayValue: Value::string($data, 'DisplayValue'),
            value: Value::string($data, 'Value'),
        );
    }
}
