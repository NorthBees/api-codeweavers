<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use NorthBees\CodeweaversApi\Codeweavers;
use NorthBees\CodeweaversApi\Exceptions\CodeweaversAuthenticationException;
use NorthBees\CodeweaversApi\Exceptions\CodeweaversConnectionException;
use NorthBees\CodeweaversApi\Exceptions\CodeweaversInvalidResponseException;
use NorthBees\CodeweaversApi\Exceptions\CodeweaversRequestException;
use NorthBees\CodeweaversApi\Requests\FinanceDefaultsRequest;

function fetchDefaults(): void
{
    app(Codeweavers::class)->finance()->defaults(new FinanceDefaultsRequest(usedCar()));
}

it('maps 401 and 403 to an authentication exception', function (int $status) {
    Http::fake(['demoservices.codeweavers.net/*' => Http::response(['Error' => ['UserMessage' => 'Invalid key']], $status)]);

    try {
        fetchDefaults();
        $this->fail('Expected an exception.');
    } catch (CodeweaversAuthenticationException $exception) {
        expect($exception->status)->toBe($status)
            ->and($exception->error?->userMessage)->toBe('Invalid key')
            ->and($exception->getMessage())->not->toContain('test-api-key');
    }
})->with([401, 403]);

it('maps other client errors to a request exception with the API error', function () {
    Http::fake(['demoservices.codeweavers.net/*' => Http::response(['Error' => ['UserMessage' => 'OnTheRoadPrice is required', 'Code' => 'Validation']], 400)]);

    try {
        fetchDefaults();
        $this->fail('Expected an exception.');
    } catch (CodeweaversRequestException $exception) {
        expect($exception->status)->toBe(400)
            ->and($exception->error?->code)->toBe('Validation')
            ->and($exception->getMessage())->toContain('OnTheRoadPrice is required');
    }
});

it('retries gateway errors and rate limits, then succeeds', function () {
    Http::fakeSequence('demoservices.codeweavers.net/*')
        ->push('', 503)
        ->push('', 429)
        ->push(['Products' => []]);

    fetchDefaults();

    Http::assertSentCount(3);
});

it('throws a connection exception when retries are exhausted', function () {
    Http::fake(['demoservices.codeweavers.net/*' => Http::response('', 502)]);

    fetchDefaults();
})->throws(CodeweaversConnectionException::class);

it('does not retry client errors', function () {
    Http::fake(['demoservices.codeweavers.net/*' => Http::response(['Error' => []], 422)]);

    rescue(fetchDefaults(...), report: false);

    Http::assertSentCount(1);
});

it('throws when the response is not JSON', function () {
    Http::fake(['demoservices.codeweavers.net/*' => Http::response('<html>Maintenance</html>', 200)]);

    fetchDefaults();
})->throws(CodeweaversInvalidResponseException::class);

it('sends the API key header and accepts JSON', function () {
    Http::fake(['demoservices.codeweavers.net/*' => Http::response(['Products' => []])]);

    fetchDefaults();

    Http::assertSent(fn (Request $request): bool => $request->hasHeader('X-CW-ApiKey', 'test-api-key')
        && $request->hasHeader('Accept', 'application/json')
        && $request->isJson());
});
