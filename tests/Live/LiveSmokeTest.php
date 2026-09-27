<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use NorthBees\CodeweaversApi\Codeweavers;
use NorthBees\CodeweaversApi\CodeweaversCredentials;
use NorthBees\CodeweaversApi\Enums\DepositType;
use NorthBees\CodeweaversApi\Enums\Environment;
use NorthBees\CodeweaversApi\Enums\IdentifierType;
use NorthBees\CodeweaversApi\Enums\VehicleStatus;
use NorthBees\CodeweaversApi\Enums\VehicleType;
use NorthBees\CodeweaversApi\Requests\BulkCalculateRequest;
use NorthBees\CodeweaversApi\Requests\BulkParameters;
use NorthBees\CodeweaversApi\Requests\BulkVehicle;
use NorthBees\CodeweaversApi\Requests\CalculateForDisplayRequest;
use NorthBees\CodeweaversApi\Requests\FinanceDefaultsRequest;
use NorthBees\CodeweaversApi\Requests\FinanceParameters;
use NorthBees\CodeweaversApi\Requests\OrganisationIdentifier;
use NorthBees\CodeweaversApi\Requests\PhysicalVehicle;
use NorthBees\CodeweaversApi\Requests\VehicleCode;

/*
 * Live contract checks against the Codeweavers sandbox. Excluded by default; run with:
 * CODEWEAVERS_LIVE=1 CODEWEAVERS_API_KEY=... CODEWEAVERS_DEALER_KEY=... CODEWEAVERS_LIVE_CAPID=... vendor/bin/pest --group=live
 */

beforeEach(function () {
    if (! env('CODEWEAVERS_LIVE')) {
        $this->markTestSkipped('Set CODEWEAVERS_LIVE=1 with CODEWEAVERS_API_KEY and CODEWEAVERS_DEALER_KEY to run live checks.');
    }

    Http::allowStrayRequests();

    $this->codeweavers = app(Codeweavers::class)
        ->withCredentials(new CodeweaversCredentials((string) env('CODEWEAVERS_API_KEY')))
        ->withEnvironment(Environment::tryFrom((string) env('CODEWEAVERS_ENVIRONMENT', 'sandbox')) ?? Environment::Sandbox)
        ->withOrganisation(OrganisationIdentifier::associatedDealerKey((string) env('CODEWEAVERS_DEALER_KEY')));

    $this->vehicle = new PhysicalVehicle(
        type: VehicleType::Car,
        status: VehicleStatus::PreOwned,
        onTheRoadPrice: 15000,
        mileage: 25000,
        externalVehicleId: 'LIVE-TEST',
        codes: [new VehicleCode(IdentifierType::CapCarShortCode, (string) env('CODEWEAVERS_LIVE_CAPID', '90132'))],
    );
});

it('fetches finance defaults', function () {
    expect($this->codeweavers->finance()->defaults(new FinanceDefaultsRequest($this->vehicle))->products)->not->toBeEmpty();
})->group('live');

it('calculates quotes for display', function () {
    $result = $this->codeweavers->finance()->calculateForDisplay(
        CalculateForDisplayRequest::forVehicle('LIVE-TEST', $this->vehicle, new FinanceParameters(term: 48, cashDeposit: 1000, depositType: DepositType::Amount, annualMileage: 10000)),
    );

    expect($result->forVehicle('LIVE-TEST')?->successful())->not->toBeEmpty();
})->group('live');

it('bulk calculates a payment matrix', function () {
    $result = $this->codeweavers->jsonFinance()->bulkCalculate(new BulkCalculateRequest(
        new BulkParameters(terms: [36, 48], deposits: [0, 1000], annualMileages: [10000]),
        [new BulkVehicle('LIVE-TEST', 15000, VehicleType::Car, VehicleStatus::PreOwned, identifier: (string) env('CODEWEAVERS_LIVE_CAPID', '90132'), identifierType: IdentifierType::CapCarShortCode, currentMileage: 25000)],
    ));

    expect($result->forVehicle('LIVE-TEST')?->rows)->not->toBeEmpty();
})->group('live');
