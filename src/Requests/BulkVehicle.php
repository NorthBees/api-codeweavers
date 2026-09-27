<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Requests;

use DateTimeInterface;
use NorthBees\CodeweaversApi\Enums\IdentifierType;
use NorthBees\CodeweaversApi\Enums\MileageUnit;
use NorthBees\CodeweaversApi\Enums\VatStatus;
use NorthBees\CodeweaversApi\Enums\VehicleStatus;
use NorthBees\CodeweaversApi\Enums\VehicleType;
use NorthBees\CodeweaversApi\Requests\Concerns\HasExtraFields;

/**
 * One vehicle in a bulk calculation. The ID is echoed back on its result.
 */
final readonly class BulkVehicle
{
    use HasExtraFields;

    /**
     * @param  string|null  $dealer  the dealer key the vehicle is quoted as (defaults to the client's organisation)
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public string $id,
        public float $cashPrice,
        public VehicleType $type,
        public VehicleStatus $status,
        public ?string $identifier = null,
        public ?IdentifierType $identifierType = null,
        public ?string $registrationNumber = null,
        public ?DateTimeInterface $registrationDate = null,
        public ?int $currentMileage = null,
        public ?bool $isVatQualifying = null,
        public ?VatStatus $vatStatus = null,
        public ?string $vin = null,
        public ?string $stockId = null,
        public ?string $imageUrl = null,
        public ?string $dealerVehicleUrl = null,
        public ?string $dealer = null,
        public ?string $channel = null,
        public MileageUnit $mileageUnit = MileageUnit::Miles,
        public array $extra = [],
    ) {}

    public function withDealer(string $dealer): self
    {
        return new self(...[...get_object_vars($this), 'dealer' => $dealer]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload([
            'Id' => $this->id,
            'Dealer' => $this->dealer,
            'Channel' => $this->channel === null ? null : ['Key' => $this->channel],
            'Vehicle' => [
                'CashPrice' => round($this->cashPrice, 2),
                'Type' => $this->type->value,
                'VehicleStatus' => $this->status->value,
                'Identifier' => $this->identifier,
                'IdentifierType' => $this->identifier === null ? null : $this->identifierType?->value,
                'RegistrationNumber' => $this->registrationNumber,
                'RegistrationDate' => $this->registrationDate?->format('Y-m-d'),
                'CurrentMileage' => $this->currentMileage,
                'CurrentMileageUnit' => $this->currentMileage === null ? null : $this->mileageUnit->value,
                'IsVatQualifying' => $this->isVatQualifying,
                'VatStatus' => $this->vatStatus?->value,
                'Vin' => $this->vin,
                'StockId' => $this->stockId,
                'ImageUrl' => $this->imageUrl,
                'DealerVehicleUrl' => $this->dealerVehicleUrl,
            ],
        ]);
    }
}
