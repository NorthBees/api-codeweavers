<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use NorthBees\CodeweaversApi\Enums\CreditTier;
use NorthBees\CodeweaversApi\Enums\DepositType;
use NorthBees\CodeweaversApi\Enums\IdentifierType;
use NorthBees\CodeweaversApi\Enums\VatStatus;
use NorthBees\CodeweaversApi\Enums\VehicleStatus;
use NorthBees\CodeweaversApi\Enums\VehicleType;
use NorthBees\CodeweaversApi\Requests\BulkCalculateRequest;
use NorthBees\CodeweaversApi\Requests\BulkParameters;
use NorthBees\CodeweaversApi\Requests\BulkVehicle;
use NorthBees\CodeweaversApi\Requests\CalculateForDisplayRequest;
use NorthBees\CodeweaversApi\Requests\FinanceParameters;
use NorthBees\CodeweaversApi\Requests\OrganisationIdentifier;
use NorthBees\CodeweaversApi\Requests\PhysicalVehicle;
use NorthBees\CodeweaversApi\Requests\Registration;
use NorthBees\CodeweaversApi\Requests\VehicleCode;
use NorthBees\CodeweaversApi\Requests\VehicleRequest;

it('builds a physical vehicle with numeric prices and omits empty fields', function () {
    $vehicle = new PhysicalVehicle(
        type: VehicleType::Car,
        status: VehicleStatus::PreOwned,
        onTheRoadPrice: 12345.678,
        mileage: 30000,
        registration: new Registration('AB12CDE', CarbonImmutable::parse('2021-03-01')),
        codes: [new VehicleCode(IdentifierType::CapCarShortCode, '90132')],
        vatStatus: VatStatus::Margin,
    );

    expect($vehicle->toArray())->toBe([
        'Type' => 'Car',
        'Status' => 'PreOwned',
        'OnTheRoadPrice' => 12345.68,
        'Mileage' => 30000,
        'MileageUnit' => 'Miles',
        'Registration' => ['RegistrationNumber' => 'AB12CDE', 'DateRegisteredWithDvla' => '2021-03-01', 'CountryCode' => 'GB'],
        'Codes' => [['Type' => 'CapCarShortCode', 'Value' => '90132']],
        'VatStatus' => 'Margin',
    ]);
});

it('merges extra fields last', function () {
    $parameters = (new FinanceParameters(term: 48, cashDeposit: 1000, depositType: DepositType::Amount))
        ->with(['Term' => 36, 'PromoCode' => 'SPRING']);

    expect($parameters->toArray())->toMatchArray([
        'Term' => 36,
        'CashDeposit' => 1000.0,
        'DepositType' => 'Amount',
        'PromoCode' => 'SPRING',
    ]);
});

it('fills a default organisation only where a vehicle request has none', function () {
    $own = new OrganisationIdentifier('OWN');
    $request = new CalculateForDisplayRequest([
        new VehicleRequest('1', usedCar()),
        new VehicleRequest('2', usedCar(), new FinanceParameters(organisation: $own)),
    ]);

    $payload = $request->withDefaultOrganisation(OrganisationIdentifier::associatedDealerKey('DEALER'))->toArray();

    expect($payload['VehicleRequests'][0]['Parameters']['OrganisationIdentifier'])->toBe(['Type' => 'AssociatedDealerKey', 'Value' => 'DEALER'])
        ->and($payload['VehicleRequests'][1]['Parameters']['OrganisationIdentifier']['Value'])->toBe('OWN');
});

it('sends empty parameters as an object', function () {
    $json = json_encode(CalculateForDisplayRequest::forVehicle('1', usedCar())->toArray());

    expect($json)->toContain('"Parameters":{}');
});

it('builds a bulk calculation matrix', function () {
    $request = new BulkCalculateRequest(
        new BulkParameters(terms: [36, 48], deposits: [0, 1000], annualMileages: [8000, 10000], creditTiers: [CreditTier::Excellent]),
        [new BulkVehicle(
            id: '42',
            cashPrice: 15000,
            type: VehicleType::Car,
            status: VehicleStatus::PreOwned,
            identifier: '90132',
            identifierType: IdentifierType::CapCarShortCode,
            registrationDate: CarbonImmutable::parse('2021-03-01'),
            currentMileage: 25000,
        )],
    );

    $payload = $request->withDefaultDealer('DEALER')->toArray();

    expect($payload['Parameters'])->toBe([
        'Terms' => [36, 48],
        'Deposits' => [0, 1000],
        'AnnualMileages' => [8000, 10000],
        'MileageUnit' => 'Miles',
        'CustomerSelectedCreditTiers' => ['Excellent'],
    ])->and($payload['VehicleRequests'][0])->toBe([
        'Id' => '42',
        'Dealer' => 'DEALER',
        'Vehicle' => [
            'CashPrice' => 15000.0,
            'Type' => 'Car',
            'VehicleStatus' => 'PreOwned',
            'Identifier' => '90132',
            'IdentifierType' => 'CapCarShortCode',
            'RegistrationDate' => '2021-03-01',
            'CurrentMileage' => 25000,
            'CurrentMileageUnit' => 'Miles',
        ],
    ])->and($request->parameters->combinations())->toBe(8);
});

it('maps vehicle types to CAP short code types', function (VehicleType $type, IdentifierType $expected) {
    expect(IdentifierType::capShortCodeFor($type))->toBe($expected);
})->with([
    [VehicleType::Car, IdentifierType::CapCarShortCode],
    [VehicleType::Lcv, IdentifierType::CapLcvShortCode],
    [VehicleType::Bike, IdentifierType::CapBikeShortCode],
]);
