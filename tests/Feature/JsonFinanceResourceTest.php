<?php

declare(strict_types=1);

use NorthBees\CodeweaversApi\Codeweavers;
use NorthBees\CodeweaversApi\Enums\Endpoint;
use NorthBees\CodeweaversApi\Enums\VehicleStatus;
use NorthBees\CodeweaversApi\Enums\VehicleType;
use NorthBees\CodeweaversApi\Exceptions\CodeweaversRequestException;
use NorthBees\CodeweaversApi\Facades\Codeweavers as CodeweaversFacade;
use NorthBees\CodeweaversApi\Requests\BulkCalculateRequest;
use NorthBees\CodeweaversApi\Requests\BulkParameters;
use NorthBees\CodeweaversApi\Requests\BulkVehicle;
use NorthBees\CodeweaversApi\Requests\OrganisationIdentifier;
use NorthBees\CodeweaversApi\Testing\CodeweaversResponse;

function bulkRequest(string ...$ids): BulkCalculateRequest
{
    return new BulkCalculateRequest(
        new BulkParameters(terms: [36, 48], deposits: [0, 1000], annualMileages: [10000]),
        array_map(fn (string $id): BulkVehicle => new BulkVehicle($id, 15000, VehicleType::Car, VehicleStatus::PreOwned, currentMileage: 25000), $ids),
    );
}

it('calculates a payment matrix for many vehicles', function () {
    $fake = CodeweaversFacade::fake([Endpoint::BulkCalculate->value => CodeweaversResponse::bulkCalculate([
        CodeweaversResponse::bulkGrid('1', [36, 48], [0, 1000], [10000]),
        CodeweaversResponse::bulkGrid('2', [36, 48], [0, 1000], [10000], ['HP']),
    ])]);

    $result = app(Codeweavers::class)
        ->withOrganisation(OrganisationIdentifier::associatedDealerKey('DEALER-1'))
        ->jsonFinance()
        ->bulkCalculate(bulkRequest('1', '2'));

    $first = $result->forVehicle('1');

    expect($result->vehicles)->toHaveCount(2)
        ->and($first?->rows)->toHaveCount(4)
        ->and($first?->rows->first()?->term)->toBe(36)
        ->and($first?->rows->first()?->deposit)->toBe(0.0)
        ->and($first?->rows->first()?->annualMileage)->toBe(10000)
        ->and($first?->rows->first()?->products->pluck('key')->all())->toBe(['HP', 'PCP'])
        ->and($first?->rows->first()?->products->first()?->isQuoted())->toBeTrue()
        ->and($first?->rows->first()?->products->last()?->finalPayment)->toBe(6000.0)
        ->and($result->forVehicle('2')?->rows->first()?->products->pluck('key')->all())->toBe(['HP']);

    $fake->assertSent(Endpoint::BulkCalculate, fn (array $payload): bool => $payload['Parameters']['Terms'] === [36, 48]
        && collect($payload['VehicleRequests'])->every(fn (array $vehicle): bool => $vehicle['Dealer'] === 'DEALER-1'));
});

it('does not treat errored or zero payments as quoted', function () {
    CodeweaversFacade::fake([Endpoint::BulkCalculate->value => CodeweaversResponse::bulkCalculate([
        CodeweaversResponse::bulkVehicle('1', [CodeweaversResponse::bulkRow(48, 0, 10000, [
            CodeweaversResponse::bulkProduct('HP', 0.0),
            CodeweaversResponse::bulkProduct('PCP', 199.0, ['HasError' => true, 'Error' => ['UserMessage' => 'Mileage too high']]),
        ])]),
    ])]);

    $products = app(Codeweavers::class)->jsonFinance()->bulkCalculate(bulkRequest('1'))->forVehicle('1')?->rows->first()?->products;

    expect($products?->filter->isQuoted())->toBeEmpty()
        ->and($products?->last()?->error?->message())->toBe('Mileage too high');
});

it('throws when the whole calculation fails', function () {
    CodeweaversFacade::fake([Endpoint::BulkCalculate->value => ['HasError' => true, 'Error' => ['UserMessage' => 'Dealer not found']]]);

    app(Codeweavers::class)->jsonFinance()->bulkCalculate(bulkRequest('1'));
})->throws(CodeweaversRequestException::class, 'Dealer not found');
