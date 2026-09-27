<?php

declare(strict_types=1);

use NorthBees\CodeweaversApi\Enums\Endpoint;

it('resolves endpoints from request paths', function (string $path, ?Endpoint $expected) {
    expect(Endpoint::fromPath($path))->toBe($expected);
})->with([
    ['/api/finance/defaults', Endpoint::FinanceDefaults],
    ['/api/finance/calculatefordisplay', Endpoint::CalculateForDisplay],
    ['/api/finance/quote/Q-123/termsandconditions', Endpoint::TermsAndConditions],
    ['/public/v3/JsonFinance/BulkCalculate', Endpoint::BulkCalculate],
    ['/api/vehicles/search', null],
]);

it('builds paths with encoded parameters', function () {
    expect(Endpoint::TermsAndConditions->path('Q 1/2'))->toBe('/api/finance/quote/Q%201%2F2/termsandconditions')
        ->and(Endpoint::TermsAndConditions->method())->toBe('GET')
        ->and(Endpoint::BulkCalculate->method())->toBe('POST');
});
