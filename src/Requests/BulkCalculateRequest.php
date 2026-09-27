<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Requests;

use NorthBees\CodeweaversApi\Requests\Concerns\HasExtraFields;

/**
 * Monthly payments for many vehicles across a matrix of terms, deposits and mileages,
 * without full quote content. Suited to precomputing finance for stock search.
 */
final readonly class BulkCalculateRequest
{
    use HasExtraFields;

    /**
     * @param  list<BulkVehicle>  $vehicles
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public BulkParameters $parameters,
        public array $vehicles,
        public ?string $referrer = null,
        public array $extra = [],
    ) {}

    /**
     * Quote every vehicle as the given dealer, unless it names its own.
     */
    public function withDefaultDealer(string $dealer): self
    {
        return new self(
            $this->parameters,
            array_map(fn (BulkVehicle $vehicle): BulkVehicle => $vehicle->dealer === null ? $vehicle->withDealer($dealer) : $vehicle, $this->vehicles),
            $this->referrer,
            $this->extra,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload([
            'Parameters' => $this->parameters->toArray(),
            'VehicleRequests' => array_map(fn (BulkVehicle $vehicle): array => $vehicle->toArray(), $this->vehicles),
            'Referrer' => $this->referrer,
        ]);
    }
}
