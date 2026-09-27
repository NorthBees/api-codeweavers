<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\JsonFinance;

use Illuminate\Support\Collection;
use NorthBees\CodeweaversApi\Support\Value;

/**
 * The response from POST /public/v3/jsonfinance/bulkcalculate.
 */
final readonly class BulkCalculateResult
{
    /**
     * @param  Collection<int, BulkVehicleResult>  $vehicles
     * @param  array<string, mixed>  $attributes  the raw response
     */
    public function __construct(
        public Collection $vehicles,
        public ?int $duration = null,
        public array $attributes = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            vehicles: collect(Value::list($data, 'VehicleResults'))->map(BulkVehicleResult::fromArray(...))->values(),
            duration: Value::int($data, 'Duration'),
            attributes: $data,
        );
    }

    public function forVehicle(string $id): ?BulkVehicleResult
    {
        return $this->vehicles->first(fn (BulkVehicleResult $vehicle): bool => $vehicle->id === $id);
    }
}
