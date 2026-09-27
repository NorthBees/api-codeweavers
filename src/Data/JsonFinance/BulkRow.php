<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\JsonFinance;

use Illuminate\Support\Collection;
use NorthBees\CodeweaversApi\Support\Value;

/**
 * The product payments for one combination of term, deposit, mileage and credit tier.
 */
final readonly class BulkRow
{
    /**
     * @param  Collection<int, BulkProduct>  $products
     */
    public function __construct(
        public ?int $term,
        public ?int $annualMileage,
        public ?float $deposit,
        public ?string $creditTier,
        public Collection $products,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            term: Value::int($data, 'Term'),
            annualMileage: Value::int($data, 'AnnualMileage'),
            deposit: Value::float($data, 'Deposits'),
            creditTier: Value::string($data, 'CustomerSelectedCreditTier'),
            products: collect(Value::list($data, 'ProductResults'))->map(BulkProduct::fromArray(...))->values(),
        );
    }
}
