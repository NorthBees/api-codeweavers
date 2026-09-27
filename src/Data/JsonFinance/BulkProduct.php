<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\JsonFinance;

use NorthBees\CodeweaversApi\Data\ApiError;
use NorthBees\CodeweaversApi\Support\Value;

/**
 * One product's payment for one combination of term, deposit and mileage.
 */
final readonly class BulkProduct
{
    /**
     * @param  array<string, mixed>  $attributes  the raw product result
     */
    public function __construct(
        public ?string $key,
        public ?string $name,
        public ?string $type,
        public ?float $payment,
        public ?float $apr,
        public ?float $totalDeposit,
        public ?float $amountOfCredit,
        public ?float $totalAmountPayable,
        public ?float $totalAmountOfCharges,
        public ?float $finalPayment,
        public ?float $residual,
        public ?float $excessMileageRate,
        public ?float $fixedRateOfInterest,
        public ?int $contractLength,
        public ?string $lender,
        public ?ApiError $error,
        public array $attributes = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            key: Value::string($data, 'Key'),
            name: Value::string($data, 'Name') ?? Value::string($data, 'ProductName'),
            type: Value::string($data, 'Type'),
            payment: Value::float($data, 'Payment'),
            apr: Value::float($data, 'Apr'),
            totalDeposit: Value::float($data, 'TotalDeposit'),
            amountOfCredit: Value::float($data, 'AmountOfCredit'),
            totalAmountPayable: Value::float($data, 'TotalAmountPayable'),
            totalAmountOfCharges: Value::float($data, 'TotalAmountOfCharges'),
            finalPayment: Value::float($data, 'FinalPayment'),
            residual: Value::float($data, 'Residual'),
            excessMileageRate: Value::float($data, 'ExcessMileageRate'),
            fixedRateOfInterest: Value::float($data, 'FixedRateOfInterest'),
            contractLength: Value::int($data, 'ContractLength'),
            lender: Value::string($data, 'Lender'),
            error: ApiError::fromResult($data),
            attributes: $data,
        );
    }

    /**
     * Whether this result carries a usable payment.
     */
    public function isQuoted(): bool
    {
        return $this->error === null && $this->payment !== null && $this->payment > 0;
    }
}
