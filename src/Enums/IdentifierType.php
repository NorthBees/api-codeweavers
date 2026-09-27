<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Enums;

/**
 * Vehicle identifier / code types. Codeweavers documents many more (manufacturer
 * specific codes); these are the ones relevant to UK used and new stock.
 */
enum IdentifierType: string
{
    case CapCarShortCode = 'CapCarShortCode';
    case CapLcvShortCode = 'CapLcvShortCode';
    case CapBikeShortCode = 'CapBikeShortCode';
    case CapLongCode = 'CapLongCode';
    case Glass = 'Glass';
    case AutoTraderDerivativeIdentifier = 'AutoTraderDerivativeIdentifier';
    case CodeweaversStockIdentifier = 'CodeweaversStockIdentifier';
    case ExternalVehicleId = 'ExternalVehicleId';
    case Jato = 'Jato';
    case Vrm = 'Vrm';
    case Vin = 'Vin';

    /**
     * The CAP short code type for a vehicle type (CAP IDs are database specific).
     */
    public static function capShortCodeFor(VehicleType $type): self
    {
        return match ($type) {
            VehicleType::Lcv => self::CapLcvShortCode,
            VehicleType::Bike => self::CapBikeShortCode,
            default => self::CapCarShortCode,
        };
    }
}
