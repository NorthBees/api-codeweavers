<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\Finance;

use NorthBees\CodeweaversApi\Data\ApiError;
use NorthBees\CodeweaversApi\Support\Value;

/**
 * A finance product the dealer offers for a vehicle, with its parameter options.
 */
final readonly class ProductDefaults
{
    /**
     * @param  list<string>  $creditTiers
     * @param  array<string, mixed>  $attributes  the raw product
     */
    public function __construct(
        public ?string $key,
        public ?string $name,
        public ?string $type,
        public ?string $lender,
        public ?string $familyKey,
        public bool $isDefault,
        public bool $isResidualValueBased,
        public ?ApiError $error,
        public ?RangeOption $deposit,
        public ?ValuesOption $term,
        public ?ValuesOption $annualMileage,
        public ?RangeOption $payment,
        public array $creditTiers,
        public ?string $defaultCreditTier,
        public array $attributes = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            key: Value::string($data, 'Key'),
            name: Value::string($data, 'Name'),
            type: Value::string($data, 'Type'),
            lender: Value::string($data, 'Lender'),
            familyKey: Value::string($data, 'FamilyKey'),
            isDefault: Value::bool($data, 'IsDefault'),
            isResidualValueBased: Value::bool($data, 'IsResidualValueBased'),
            error: ApiError::fromResult($data),
            deposit: RangeOption::fromArray(Value::object($data, 'Deposit')),
            term: ValuesOption::fromArray(Value::object($data, 'Term')),
            annualMileage: ValuesOption::fromArray(Value::object($data, 'AnnualMileage')),
            payment: RangeOption::fromArray(Value::object($data, 'Payment')),
            creditTiers: array_values(array_filter(array_map(
                fn (array $tier): ?string => Value::string($tier, 'Value'),
                Value::list($data, 'CustomerSelectedTiers.Tiers'),
            ))),
            defaultCreditTier: Value::string($data, 'CustomerSelectedTiers.Default'),
            attributes: $data,
        );
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }
}
