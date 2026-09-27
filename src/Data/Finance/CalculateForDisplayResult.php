<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\Finance;

use Illuminate\Support\Collection;
use NorthBees\CodeweaversApi\Support\Value;

/**
 * The response from POST /api/finance/calculatefordisplay.
 */
final readonly class CalculateForDisplayResult
{
    /**
     * @param  Collection<int, VehicleQuotations>  $vehicles
     * @param  array<string, mixed>  $attributes  the raw response
     */
    public function __construct(
        public Collection $vehicles,
        public array $attributes = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            vehicles: collect(Value::list($data, 'Vehicles'))->map(VehicleQuotations::fromArray(...))->values(),
            attributes: $data,
        );
    }

    public function forVehicle(string $id): ?VehicleQuotations
    {
        return $this->vehicles->first(fn (VehicleQuotations $vehicle): bool => $vehicle->id === $id);
    }
}
