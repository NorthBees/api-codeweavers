<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Requests;

use NorthBees\CodeweaversApi\Enums\CreditTier;
use NorthBees\CodeweaversApi\Enums\MileageUnit;
use NorthBees\CodeweaversApi\Requests\Concerns\HasExtraFields;

/**
 * The payment matrix for a bulk calculation: every vehicle is quoted for every
 * combination of term, deposit and annual mileage (and credit tier, if given).
 */
final readonly class BulkParameters
{
    use HasExtraFields;

    /**
     * @param  list<int>  $terms  in months
     * @param  list<int|float>  $deposits  cash amounts
     * @param  list<int>  $annualMileages
     * @param  list<CreditTier>  $creditTiers
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public array $terms,
        public array $deposits,
        public array $annualMileages,
        public array $creditTiers = [],
        public bool $enableExtraQuoteDetails = false,
        public ?float $sellOutApr = null,
        public MileageUnit $mileageUnit = MileageUnit::Miles,
        public array $extra = [],
    ) {}

    /**
     * The number of payments each vehicle is quoted for (per product).
     */
    public function combinations(): int
    {
        return count($this->terms) * max(1, count($this->deposits)) * max(1, count($this->annualMileages)) * max(1, count($this->creditTiers));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload([
            'Terms' => $this->terms,
            'Deposits' => $this->deposits,
            'AnnualMileages' => $this->annualMileages,
            'MileageUnit' => $this->mileageUnit->value,
            'CustomerSelectedCreditTiers' => array_map(fn (CreditTier $tier): string => $tier->value, $this->creditTiers),
            'EnableExtraQuoteDetails' => $this->enableExtraQuoteDetails ?: null,
            'SellOut' => $this->sellOutApr === null ? null : ['Type' => 'Apr', 'Value' => $this->sellOutApr],
        ]);
    }
}
