<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Requests;

use NorthBees\CodeweaversApi\Requests\Concerns\HasExtraFields;

/**
 * One vehicle in a calculate-for-display request. The ID is echoed back on the result.
 */
final readonly class VehicleRequest
{
    use HasExtraFields;

    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public string $id,
        public PhysicalVehicle $vehicle,
        public FinanceParameters $parameters = new FinanceParameters,
        public array $extra = [],
    ) {}

    public function withParameters(FinanceParameters $parameters): self
    {
        return new self($this->id, $this->vehicle, $parameters, $this->extra);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload([
            'Id' => $this->id,
            'Parameters' => $this->parameters->toArray() ?: (object) [],
            'PhysicalVehicle' => $this->vehicle->toArray(),
        ]);
    }
}
