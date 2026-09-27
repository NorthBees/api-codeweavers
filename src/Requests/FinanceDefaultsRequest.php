<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Requests;

use NorthBees\CodeweaversApi\Requests\Concerns\HasExtraFields;

/**
 * The finance products available for a vehicle, with their deposit, term and mileage options.
 */
final readonly class FinanceDefaultsRequest
{
    use HasExtraFields;

    /**
     * @param  list<string>  $productKeys  only return products with these keys
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public PhysicalVehicle $vehicle,
        public ?OrganisationIdentifier $organisation = null,
        public array $productKeys = [],
        public ?bool $cashValuesAreVatExclusive = null,
        public ?string $channel = null,
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
            'PhysicalVehicle' => $this->vehicle->toArray(),
            'OrganisationIdentifier' => $this->organisation?->toArray(),
            'Channel' => $this->channel === null ? null : ['Key' => $this->channel],
            'Parameters' => [
                'ProductIdentifiers' => $this->productKeys,
                'CashValuesAreVatExclusive' => $this->cashValuesAreVatExclusive,
            ],
        ]);
    }
}
