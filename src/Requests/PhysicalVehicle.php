<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Requests;

use DateTimeInterface;
use NorthBees\CodeweaversApi\Enums\MileageUnit;
use NorthBees\CodeweaversApi\Enums\VatStatus;
use NorthBees\CodeweaversApi\Enums\VehicleStatus;
use NorthBees\CodeweaversApi\Enums\VehicleType;
use NorthBees\CodeweaversApi\Requests\Concerns\HasExtraFields;

/**
 * A specific, built vehicle (usually a stock vehicle) to calculate finance for.
 */
final readonly class PhysicalVehicle
{
    use HasExtraFields;

    /**
     * @param  list<VehicleCode>  $codes  e.g. its CAP short code or AutoTrader derivative ID
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public VehicleType $type,
        public VehicleStatus $status,
        public float $onTheRoadPrice,
        public ?int $mileage = null,
        public ?string $externalVehicleId = null,
        public ?string $vin = null,
        public ?Registration $registration = null,
        public array $codes = [],
        public ?VatStatus $vatStatus = null,
        public ?DateTimeInterface $dateOfManufacture = null,
        public ?string $externalVehicleLink = null,
        public MileageUnit $mileageUnit = MileageUnit::Miles,
        public array $extra = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload([
            'Type' => $this->type->value,
            'Status' => $this->status->value,
            'OnTheRoadPrice' => round($this->onTheRoadPrice, 2),
            'Mileage' => $this->mileage,
            'MileageUnit' => $this->mileage === null ? null : $this->mileageUnit->value,
            'ExternalVehicleId' => $this->externalVehicleId,
            'Vin' => $this->vin,
            'Registration' => $this->registration?->toArray(),
            'Codes' => array_map(fn (VehicleCode $code): array => $code->toArray(), $this->codes),
            'VatStatus' => $this->vatStatus?->value,
            'DateOfManufacture' => $this->dateOfManufacture?->format('Y-m-d'),
            'ExternalVehicleLink' => $this->externalVehicleLink,
        ]);
    }
}
