<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use NorthBees\CodeweaversApi\Codeweavers;
use NorthBees\CodeweaversApi\CodeweaversCredentials;
use NorthBees\CodeweaversApi\Enums\DepositType;
use NorthBees\CodeweaversApi\Enums\Endpoint;
use NorthBees\CodeweaversApi\Facades\Codeweavers as CodeweaversFacade;
use NorthBees\CodeweaversApi\Requests\CalculateForDisplayRequest;
use NorthBees\CodeweaversApi\Requests\FinanceDefaultsRequest;
use NorthBees\CodeweaversApi\Requests\FinanceParameters;
use NorthBees\CodeweaversApi\Requests\OrganisationIdentifier;
use NorthBees\CodeweaversApi\Testing\CodeweaversResponse;

it('fetches finance defaults as the client organisation', function () {
    $fake = CodeweaversFacade::fake([Endpoint::FinanceDefaults->value => CodeweaversResponse::defaults()]);

    $defaults = app(Codeweavers::class)
        ->withOrganisation(OrganisationIdentifier::associatedDealerKey('DEALER-1'))
        ->finance()
        ->defaults(new FinanceDefaultsRequest(usedCar()));

    expect($defaults->products)->toHaveCount(2)
        ->and($defaults->defaultProduct()?->key)->toBe('HP')
        ->and($defaults->products[1]->isResidualValueBased)->toBeTrue()
        ->and($defaults->products[1]->term?->values)->toBe([24, 36, 48, 60])
        ->and($defaults->products[1]->deposit?->maximum)->toBe(10000.0)
        ->and($defaults->products[1]->annualMileage?->default)->toBe(10000);

    $fake->assertSent(Endpoint::FinanceDefaults, fn (array $payload): bool => $payload['OrganisationIdentifier'] === ['Type' => 'AssociatedDealerKey', 'Value' => 'DEALER-1']
        && $payload['PhysicalVehicle']['OnTheRoadPrice'] === 15000.0);
});

it('excludes products with errors from the available products', function () {
    CodeweaversFacade::fake([Endpoint::FinanceDefaults->value => CodeweaversResponse::defaults([
        CodeweaversResponse::product('HP', ['HasError' => true, 'Error' => ['UserMessage' => 'Vehicle too old']]),
        CodeweaversResponse::product('PCP'),
    ])]);

    $defaults = app(Codeweavers::class)->finance()->defaults(new FinanceDefaultsRequest(usedCar()));

    expect($defaults->available()->pluck('key')->all())->toBe(['PCP'])
        ->and($defaults->products[0]->error?->message())->toBe('Vehicle too old')
        ->and($defaults->defaultProduct()?->key)->toBe('PCP');
});

it('calculates quotes for display', function () {
    $fake = CodeweaversFacade::fake([
        Endpoint::CalculateForDisplay->value => CodeweaversResponse::calculateForDisplay([
            CodeweaversResponse::vehicle('STOCK-1', [
                CodeweaversResponse::quotation('HP'),
                CodeweaversResponse::quotation('PCP', ['RegularPayment' => 249.5, 'Payments' => [['Amount' => 249.5, 'NumberOfPayments' => 47], ['Amount' => 6010.0, 'NumberOfPayments' => 1]]]),
            ]),
        ]),
    ]);

    $result = app(Codeweavers::class)
        ->withCredentials(new CodeweaversCredentials('tenant-key'))
        ->withOrganisation(OrganisationIdentifier::associatedDealerKey('DEALER-1'))
        ->finance()
        ->calculateForDisplay(CalculateForDisplayRequest::forVehicle('STOCK-1', usedCar(), new FinanceParameters(term: 48, cashDeposit: 1000, depositType: DepositType::Amount, annualMileage: 10000)));

    $vehicle = $result->forVehicle('STOCK-1');
    $hp = $vehicle?->successful()->first();
    $pcp = $vehicle?->successful()->last();

    expect($vehicle?->quotations)->toHaveCount(2)
        ->and($hp?->key)->toBe('HP')
        ->and($hp?->quote?->regularPayment)->toBe(349.99)
        ->and($hp?->quote?->apr)->toBe(9.9)
        ->and($hp?->quote?->totalAmountPayable)->toBe(17799.52)
        ->and($hp?->quote?->cashDeposit)->toBe(1000.0)
        ->and($hp?->quote?->fee('Purchase'))->toBe(10.0)
        ->and($hp?->quote?->isRepresentativeExample)->toBeTrue()
        ->and($hp?->quote?->finalPayment())->toBeNull()
        ->and($pcp?->product?->isResidualBased)->toBeTrue()
        ->and($pcp?->quote?->finalPayment())->toBe(6000.0);

    $fake->assertSent(Endpoint::CalculateForDisplay, fn (array $payload): bool => $payload['VehicleRequests'][0]['Parameters'] === [
        'Term' => 48,
        'CashDeposit' => 1000.0,
        'DepositType' => 'Amount',
        'AnnualMileage' => 10000,
        'AnnualMileageUnit' => 'Miles',
        'OrganisationIdentifier' => ['Type' => 'AssociatedDealerKey', 'Value' => 'DEALER-1'],
    ]);

    Http::assertSent(fn (Request $request): bool => $request->hasHeader('X-CW-ApiKey', 'tenant-key')
        && $request->url() === 'https://demoservices.codeweavers.net/api/finance/calculatefordisplay');
});

it('reads display details and notifications', function () {
    $quotation = CodeweaversResponse::quotation('PCP');
    $quotation['Blocks'] = [['Details' => [['Key' => 'OptionalFinalPayment', 'Label' => 'Optional final payment', 'DisplayValue' => '£6,000.00', 'Value' => '6000.00']]]];
    $quotation['Finance']['Notifications'] = [['Code' => 1, 'Message' => 'Term adjusted to 47 months']];

    CodeweaversFacade::fake([Endpoint::CalculateForDisplay->value => CodeweaversResponse::calculateForDisplay([CodeweaversResponse::vehicle('1', [$quotation])])]);

    $pcp = app(Codeweavers::class)->finance()->calculateForDisplay(CalculateForDisplayRequest::forVehicle('1', usedCar()))->forVehicle('1')?->quotations->first();

    expect($pcp?->detail('OptionalFinalPayment')?->displayValue)->toBe('£6,000.00')
        ->and($pcp?->notifications)->toBe(['Term adjusted to 47 months']);
});

it('reports per-vehicle and per-quotation errors without throwing', function () {
    CodeweaversFacade::fake([Endpoint::CalculateForDisplay->value => CodeweaversResponse::calculateForDisplay([
        ['Id' => '1', 'HasError' => true, 'Error' => ['UserMessage' => 'Vehicle not found'], 'FinanceQuotations' => []],
        CodeweaversResponse::vehicle('2', [['HasError' => true, 'Error' => ['UserMessage' => 'No products'], 'Finance' => ['Key' => 'HP']]]),
    ])]);

    $result = app(Codeweavers::class)->finance()->calculateForDisplay(CalculateForDisplayRequest::forVehicle('1', usedCar()));

    expect($result->forVehicle('1')?->hasError())->toBeTrue()
        ->and($result->forVehicle('1')?->error?->message())->toBe('Vehicle not found')
        ->and($result->forVehicle('2')?->successful())->toBeEmpty()
        ->and($result->forVehicle('2')?->quotations->first()?->error?->message())->toBe('No products');
});

it('fetches terms and conditions for a quote', function () {
    $fake = CodeweaversFacade::fake([Endpoint::TermsAndConditions->value => ['Content' => '<p>Terms</p>']]);

    $terms = app(Codeweavers::class)->finance()->termsAndConditions('Q-123');

    expect($terms->quoteReference)->toBe('Q-123')
        ->and($terms->attributes)->toBe(['Content' => '<p>Terms</p>']);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET' && str_ends_with($request->url(), '/api/finance/quote/Q-123/termsandconditions'));
    $fake->assertSentTimes(Endpoint::TermsAndConditions, 1);
});
