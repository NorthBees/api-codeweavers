<?php

declare(strict_types=1);

use NorthBees\CodeweaversApi\Codeweavers;
use NorthBees\CodeweaversApi\Enums\Endpoint;
use NorthBees\CodeweaversApi\Exceptions\CodeweaversRequestException;
use NorthBees\CodeweaversApi\Facades\Codeweavers as CodeweaversFacade;
use NorthBees\CodeweaversApi\Requests\FinanceDefaultsRequest;
use NorthBees\CodeweaversApi\Testing\CodeweaversResponse;
use PHPUnit\Framework\AssertionFailedError;

it('responds with closures that receive the payload', function () {
    $fake = CodeweaversFacade::fake([
        '*' => fn (array $payload): array => CodeweaversResponse::defaults([CodeweaversResponse::product('HP', ['Name' => 'Price '.$payload['PhysicalVehicle']['OnTheRoadPrice']])]),
    ]);

    $defaults = app(Codeweavers::class)->finance()->defaults(new FinanceDefaultsRequest(usedCar(9999)));

    expect($defaults->products->first()?->name)->toBe('Price 9999');
    expect($fake->sent())->toHaveCount(1)
        ->and($fake->sent()[0]['key'])->toBe('finance.defaults');
});

it('fails loudly for requests that were not faked', function () {
    $fake = CodeweaversFacade::fake();

    expect(fn () => app(Codeweavers::class)->finance()->defaults(new FinanceDefaultsRequest(usedCar())))
        ->toThrow(CodeweaversRequestException::class, 'was not faked');

    $fake->assertSent(Endpoint::FinanceDefaults);
});

it('serves pushed responses and error responses', function () {
    $fake = CodeweaversFacade::fake()->push(Endpoint::FinanceDefaults, CodeweaversResponse::error('Bad dealer', 404));

    expect(fn () => app(Codeweavers::class)->finance()->defaults(new FinanceDefaultsRequest(usedCar())))
        ->toThrow(CodeweaversRequestException::class, 'Bad dealer');

    $fake->assertNotSent(Endpoint::BulkCalculate);
});

it('asserts that nothing was sent', function () {
    CodeweaversFacade::fake()->assertNothingSent();

    expect(fn () => CodeweaversFacade::fake()->assertSent(Endpoint::FinanceDefaults))->toThrow(AssertionFailedError::class);
});
