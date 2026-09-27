<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\Finance;

use Illuminate\Support\Collection;
use NorthBees\CodeweaversApi\Support\Value;

/**
 * The figures of a finance quotation. Amounts are in pounds.
 */
final readonly class Quote
{
    /**
     * @param  Collection<int, Fee>  $fees
     * @param  Collection<int, Payment>  $payments
     * @param  array<string, mixed>  $attributes  the raw quote
     */
    public function __construct(
        public ?float $regularPayment,
        public ?float $firstPayment,
        public ?int $term,
        public ?int $numberOfRegularPayments,
        public ?float $totalPrice,
        public ?float $cashDeposit,
        public ?float $totalDeposit,
        public ?float $balance,
        public ?float $totalAmountPayable,
        public ?float $chargesForCredit,
        public ?float $apr,
        public ?float $rateOfInterest,
        public ?float $residual,
        public ?int $annualMileage,
        public ?int $contractMileage,
        public ?float $excessMileageRate,
        public ?string $quoteReference,
        public ?string $validFrom,
        public ?string $validTo,
        public bool $isRepresentativeExample,
        public Collection $fees,
        public Collection $payments,
        public array $attributes = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            regularPayment: Value::float($data, 'RegularPayment') ?? Value::float($data, 'TotalRegularPayment'),
            firstPayment: Value::float($data, 'TotalFirstPayment'),
            term: Value::int($data, 'Term'),
            numberOfRegularPayments: Value::int($data, 'TotalNumberOfRegularPayments'),
            totalPrice: Value::float($data, 'TotalPrice'),
            cashDeposit: Value::float($data, 'Deposit.Cash'),
            totalDeposit: Value::float($data, 'TotalDeposit'),
            balance: Value::float($data, 'Balance'),
            totalAmountPayable: Value::float($data, 'TotalAmountPayable'),
            chargesForCredit: Value::float($data, 'ChargesForCredit'),
            apr: Value::float($data, 'Apr'),
            rateOfInterest: Value::float($data, 'RateOfInterest'),
            residual: Value::float($data, 'Residual'),
            annualMileage: Value::int($data, 'AnnualMileage'),
            contractMileage: Value::int($data, 'ContractMileage'),
            excessMileageRate: Value::float($data, 'ExcessMileageRate'),
            quoteReference: Value::string($data, 'QuoteReference'),
            validFrom: Value::string($data, 'ValidFrom'),
            validTo: Value::string($data, 'ValidTo'),
            isRepresentativeExample: Value::bool($data, 'IsRepresentativeExample'),
            fees: collect(Value::list($data, 'Fees'))->map(Fee::fromArray(...))->values(),
            payments: collect(Value::list($data, 'Payments'))->map(Payment::fromArray(...))->values(),
            attributes: $data,
        );
    }

    /**
     * The final (balloon / optional final) payment: the residual for residual based
     * products, otherwise a trailing one-off payment in the schedule.
     */
    public function finalPayment(): ?float
    {
        if ($this->residual !== null && $this->residual > 0) {
            return $this->residual;
        }

        $last = $this->payments->last();

        return $this->payments->count() > 1 && $last?->numberOfPayments === 1 ? $last->amount : null;
    }

    /**
     * The total of fees of a type (e.g. "Purchase" for the option to purchase fee).
     */
    public function fee(string $type): ?float
    {
        $fees = $this->fees->filter(fn (Fee $fee): bool => strcasecmp((string) $fee->type, $type) === 0);

        return $fees->isEmpty() ? null : (float) $fees->sum(fn (Fee $fee): float => $fee->amount ?? 0.0);
    }
}
