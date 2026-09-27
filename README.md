# NorthBees Codeweavers API

[![Tests](https://github.com/northbees/api-codeweavers/actions/workflows/tests.yml/badge.svg)](https://github.com/northbees/api-codeweavers/actions/workflows/tests.yml)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.md)
[![PHP Version](https://img.shields.io/badge/php-%5E8.4-777bb4.svg?style=flat-square)](composer.json)

A Laravel SDK for the [Codeweavers finance API](https://docs.codeweavers.net/api/quick-start/introduction):

- finance defaults: the products a dealer offers for a vehicle, with their deposit, term and mileage options;
- quotes for display: full finance quotes, including representative example figures;
- bulk calculations: monthly payments for many vehicles across a matrix of terms, deposits and mileages, suited to precomputing finance for stock search.

Requests go through Laravel's HTTP client, so `Http::fake()` works in tests. Responses are parsed into typed, readonly DTOs.

## Requirements

- PHP 8.4+
- Laravel 12 or 13

## Installation

```bash
composer require northbees/codeweavers-api
```

The service provider and the `Codeweavers` facade are auto-discovered.

Publish the configuration file:

```bash
php artisan vendor:publish --tag=codeweavers.config
```

## Configuration

```env
CODEWEAVERS_API_KEY=your-api-key
CODEWEAVERS_DEALER_KEY=your-associated-dealer-key
CODEWEAVERS_ENVIRONMENT=production   # sandbox or production

# Optional
CODEWEAVERS_SANDBOX_URL=https://demoservices.codeweavers.net
CODEWEAVERS_PRODUCTION_URL=https://services.codeweavers.net
CODEWEAVERS_TIMEOUT=15
CODEWEAVERS_CONNECT_TIMEOUT=5
CODEWEAVERS_RETRY_TIMES=2
CODEWEAVERS_RETRY_SLEEP_MS=250
CODEWEAVERS_LOG=false
CODEWEAVERS_LOG_CHANNEL=
```

The API key is sent as the `X-CW-ApiKey` header exactly as issued. The `.env` values are only a fallback: multi-tenant apps should pass credentials per call (see below).

## Usage

Resolve the client from the container or use the facade:

```php
use NorthBees\CodeweaversApi\Codeweavers;

$codeweavers = app(Codeweavers::class);

$codeweavers->finance();      // Finance defaults, quotes for display, terms and conditions
$codeweavers->jsonFinance();  // Bulk calculations
```

### Per-tenant credentials

The client is immutable. `withCredentials()`, `withEnvironment()` and `withOrganisation()` return new instances, so one tenant's settings never leak into another's:

```php
use NorthBees\CodeweaversApi\CodeweaversCredentials;
use NorthBees\CodeweaversApi\Enums\Environment;
use NorthBees\CodeweaversApi\Requests\OrganisationIdentifier;

$codeweavers = app(Codeweavers::class)
    ->withCredentials(new CodeweaversCredentials($tenant->codeweavers_api_key))
    ->withEnvironment(Environment::Production)
    ->withOrganisation(OrganisationIdentifier::associatedDealerKey($tenant->codeweavers_dealer_key));
```

The client is bound as a scoped service, so queue workers and Octane get a fresh instance per job or request.

### Describing a vehicle

```php
use NorthBees\CodeweaversApi\Enums\IdentifierType;
use NorthBees\CodeweaversApi\Enums\VatStatus;
use NorthBees\CodeweaversApi\Enums\VehicleStatus;
use NorthBees\CodeweaversApi\Enums\VehicleType;
use NorthBees\CodeweaversApi\Requests\PhysicalVehicle;
use NorthBees\CodeweaversApi\Requests\Registration;
use NorthBees\CodeweaversApi\Requests\VehicleCode;

$vehicle = new PhysicalVehicle(
    type: VehicleType::Car,
    status: VehicleStatus::PreOwned,
    onTheRoadPrice: 15995,
    mileage: 24000,
    externalVehicleId: 'STOCK-123',
    registration: new Registration('AB21CDE', $firstRegisteredAt),
    codes: [new VehicleCode(IdentifierType::CapCarShortCode, '90132')],
    vatStatus: VatStatus::Margin,
);
```

### Finance defaults

```php
use NorthBees\CodeweaversApi\Requests\FinanceDefaultsRequest;

$defaults = $codeweavers->finance()->defaults(new FinanceDefaultsRequest($vehicle));

foreach ($defaults->available() as $product) {
    $product->key;                    // "PCP"
    $product->term?->values;          // [24, 36, 48]
    $product->deposit?->maximum;      // 10000.0
    $product->annualMileage?->values; // [6000, 8000, 10000]
}

$defaults->defaultProduct(); // the product the dealer highlights first
```

### Quotes for display

```php
use NorthBees\CodeweaversApi\Enums\DepositType;
use NorthBees\CodeweaversApi\Requests\CalculateForDisplayRequest;
use NorthBees\CodeweaversApi\Requests\FinanceParameters;

$result = $codeweavers->finance()->calculateForDisplay(CalculateForDisplayRequest::forVehicle(
    'STOCK-123',
    $vehicle,
    new FinanceParameters(term: 48, cashDeposit: 1000, depositType: DepositType::Amount, annualMileage: 10000),
));

foreach ($result->forVehicle('STOCK-123')?->successful() ?? [] as $quotation) {
    $quotation->product?->name;
    $quotation->quote?->regularPayment;
    $quotation->quote?->apr;
    $quotation->quote?->totalAmountPayable;
    $quotation->quote?->finalPayment();
    $quotation->quote?->isRepresentativeExample;
    $quotation->detail('OptionalFinalPayment')?->displayValue; // pre-formatted display content
}

$codeweavers->finance()->termsAndConditions($quotation->quote->quoteReference);
```

### Bulk calculations

```php
use NorthBees\CodeweaversApi\Requests\BulkCalculateRequest;
use NorthBees\CodeweaversApi\Requests\BulkParameters;
use NorthBees\CodeweaversApi\Requests\BulkVehicle;

$result = $codeweavers->jsonFinance()->bulkCalculate(new BulkCalculateRequest(
    new BulkParameters(terms: [24, 36, 48], deposits: [0, 1000, 2000], annualMileages: [8000, 10000]),
    [
        new BulkVehicle('42', 15995, VehicleType::Car, VehicleStatus::PreOwned, identifier: '90132', identifierType: IdentifierType::CapCarShortCode, currentMileage: 24000),
        // ...
    ],
));

foreach ($result->forVehicle('42')?->rows ?? [] as $row) {
    foreach ($row->products->filter->isQuoted() as $product) {
        [$row->term, $row->deposit, $row->annualMileage, $product->key, $product->payment, $product->apr];
    }
}
```

Vehicles without a `dealer` are quoted as the client's `AssociatedDealerKey` organisation.

### Fields the SDK does not model

Every request object has `with()`, which merges extra documented fields into its payload. Extra fields are merged last, so they win:

```php
new FinanceParameters(term: 48)->with(['PromoCode' => 'SPRING', 'IncludeAllPromotionalFinance' => true]);
```

Every response DTO keeps the raw decoded payload in `$attributes`.

## Errors

Every exception extends `NorthBees\CodeweaversApi\Exceptions\CodeweaversException`. Messages never contain the API key.

| Exception | When |
|---|---|
| `CodeweaversConnectionException` | The API could not be reached, or returned a 5xx after retries |
| `CodeweaversAuthenticationException` | The API key was rejected (401/403) |
| `CodeweaversRequestException` | Any other 4xx, or a top-level `HasError`. Carries the `ApiError` and HTTP status |
| `CodeweaversInvalidResponseException` | The body was not JSON |
| `CodeweaversMissingCredentialsException` | No API key was configured or supplied |

Errors for individual vehicles, quotations and products are not thrown. They are returned on the DTOs (`$vehicle->error`, `$quotation->hasError()`, `$product->isQuoted()`), so one bad vehicle does not fail a whole batch.

Connection failures and 429/502/503/504 responses are retried (`retry.times`, `retry.sleep_ms`). Other errors are not.

## Testing your application

`Codeweavers::fake()` fakes the API through `Http::fake()`. Responses are keyed by endpoint name (`finance.defaults`, `finance.calculateForDisplay`, `finance.termsAndConditions`, `jsonFinance.bulkCalculate`) or `*`. `CodeweaversResponse` builds realistic bodies:

```php
use NorthBees\CodeweaversApi\Enums\Endpoint;
use NorthBees\CodeweaversApi\Facades\Codeweavers;
use NorthBees\CodeweaversApi\Testing\CodeweaversResponse;

$fake = Codeweavers::fake([
    Endpoint::FinanceDefaults->value => CodeweaversResponse::defaults(),
    Endpoint::BulkCalculate->value => fn (array $payload) => CodeweaversResponse::bulkCalculate(
        array_map(fn (array $vehicle) => CodeweaversResponse::bulkGrid($vehicle['Id'], [36, 48], [0, 1000], [10000]), $payload['VehicleRequests']),
    ),
    Endpoint::CalculateForDisplay->value => CodeweaversResponse::error('Vehicle not found', 404),
]);

// ...

$fake->assertSent(Endpoint::BulkCalculate, fn (array $payload) => count($payload['VehicleRequests']) === 25);
$fake->assertNotSent(Endpoint::CalculateForDisplay);
```

Requests that were not faked fail with a `CodeweaversRequestException`.

## Development

```bash
composer test      # Pest
composer lint      # Pint
composer analyse   # Larastan
```

Live contract checks run against the sandbox and are excluded by default:

```bash
CODEWEAVERS_LIVE=1 CODEWEAVERS_API_KEY=... CODEWEAVERS_DEALER_KEY=... CODEWEAVERS_LIVE_CAPID=... vendor/bin/pest --group=live
```

### Not yet covered

Codeweavers-hosted stock (vehicle search and save), proposals, part exchange, navigator redirects, and residual calculations.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).
