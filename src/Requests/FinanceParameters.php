<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Requests;

use NorthBees\CodeweaversApi\Enums\CreditTier;
use NorthBees\CodeweaversApi\Enums\DepositType;
use NorthBees\CodeweaversApi\Enums\MileageUnit;
use NorthBees\CodeweaversApi\Requests\Concerns\HasExtraFields;

/**
 * The finance parameters for a calculation. Omitted values use the dealer's defaults.
 */
final readonly class FinanceParameters
{
    use HasExtraFields;

    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?int $term = null,
        public ?float $cashDeposit = null,
        public ?DepositType $depositType = null,
        public ?int $annualMileage = null,
        public ?string $productKey = null,
        public ?CreditTier $creditTier = null,
        public ?bool $cashValuesAreVatExclusive = null,
        public ?string $channel = null,
        public ?OrganisationIdentifier $organisation = null,
        public MileageUnit $annualMileageUnit = MileageUnit::Miles,
        public array $extra = [],
    ) {}

    public function withOrganisation(OrganisationIdentifier $organisation): self
    {
        return new self(...[...get_object_vars($this), 'organisation' => $organisation]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload([
            'Term' => $this->term,
            'CashDeposit' => $this->cashDeposit === null ? null : round($this->cashDeposit, 2),
            'DepositType' => $this->depositType?->value,
            'AnnualMileage' => $this->annualMileage,
            'AnnualMileageUnit' => $this->annualMileage === null ? null : $this->annualMileageUnit->value,
            'ProductKey' => $this->productKey,
            'CustomerSelectedCreditTier' => $this->creditTier?->value,
            'CashValuesAreVatExclusive' => $this->cashValuesAreVatExclusive,
            'Channel' => $this->channel === null ? null : ['Key' => $this->channel],
            'OrganisationIdentifier' => $this->organisation?->toArray(),
        ]);
    }
}
