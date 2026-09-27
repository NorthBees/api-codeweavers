<?php

declare(strict_types=1);

use NorthBees\CodeweaversApi\Enums\VehicleStatus;
use NorthBees\CodeweaversApi\Enums\VehicleType;
use NorthBees\CodeweaversApi\Requests\PhysicalVehicle;
use NorthBees\CodeweaversApi\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit', 'Live');

/**
 * Load a recorded Codeweavers response from `tests/fixtures`.
 *
 * @return array<string, mixed>
 */
function codeweaversFixture(string $file): array
{
    return json_decode((string) file_get_contents(__DIR__.'/fixtures/'.$file), true, flags: JSON_THROW_ON_ERROR);
}

function usedCar(float $price = 15000.0): PhysicalVehicle
{
    return new PhysicalVehicle(
        type: VehicleType::Car,
        status: VehicleStatus::PreOwned,
        onTheRoadPrice: $price,
        mileage: 25000,
        externalVehicleId: 'STOCK-1',
    );
}
