<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\Finance;

use NorthBees\CodeweaversApi\Support\Value;

/**
 * A fee on a quote, e.g. an option to purchase or acceptance fee.
 */
final readonly class Fee
{
    public function __construct(
        public ?string $type,
        public ?float $amount,
        public ?string $profile = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: Value::string($data, 'Type'),
            amount: Value::float($data, 'Amount'),
            profile: Value::string($data, 'Profile'),
        );
    }
}
