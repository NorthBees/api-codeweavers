<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\Finance;

use NorthBees\CodeweaversApi\Support\Value;

/**
 * One group in a quote's payment schedule, e.g. 47 payments of £199.
 */
final readonly class Payment
{
    public function __construct(
        public ?float $amount,
        public ?int $numberOfPayments,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            amount: Value::float($data, 'Amount'),
            numberOfPayments: Value::int($data, 'NumberOfPayments'),
        );
    }
}
