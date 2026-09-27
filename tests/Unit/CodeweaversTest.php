<?php

declare(strict_types=1);

use NorthBees\CodeweaversApi\Codeweavers;
use NorthBees\CodeweaversApi\CodeweaversCredentials;
use NorthBees\CodeweaversApi\Enums\Environment;
use NorthBees\CodeweaversApi\Exceptions\CodeweaversMissingCredentialsException;
use NorthBees\CodeweaversApi\Requests\OrganisationIdentifier;

it('is bound as a scoped service', function () {
    expect(app(Codeweavers::class))->toBe(app(Codeweavers::class))
        ->and(app('codeweavers'))->toBe(app(Codeweavers::class));
});

it('falls back to config for credentials, environment and organisation', function () {
    config()->set('codeweavers.dealer_key', 'CONFIG-DEALER');
    app()->forgetScopedInstances();

    $client = app(Codeweavers::class);

    expect($client->credentials()->apiKey)->toBe('test-api-key')
        ->and($client->environment())->toBe(Environment::Sandbox)
        ->and($client->baseUrl())->toBe('https://demoservices.codeweavers.net')
        ->and($client->organisation()?->value)->toBe('CONFIG-DEALER');
});

it('returns new instances from the with methods', function () {
    $client = app(Codeweavers::class);
    $tenant = $client
        ->withCredentials(new CodeweaversCredentials('tenant-key'))
        ->withEnvironment(Environment::Production)
        ->withOrganisation(OrganisationIdentifier::associatedDealerKey('D1'));

    expect($tenant)->not->toBe($client)
        ->and($tenant->credentials()->apiKey)->toBe('tenant-key')
        ->and($tenant->baseUrl())->toBe('https://services.codeweavers.net')
        ->and($tenant->organisation()?->value)->toBe('D1')
        ->and($client->credentials()->apiKey)->toBe('test-api-key');
});

it('throws when no API key is configured', function () {
    config()->set('codeweavers.api_key', null);
    app()->forgetScopedInstances();

    expect(app(Codeweavers::class)->hasCredentials())->toBeFalse();

    app(Codeweavers::class)->credentials();
})->throws(CodeweaversMissingCredentialsException::class);

it('redacts the API key from dumps', function () {
    expect(print_r(new CodeweaversCredentials('secret-key'), true))->not->toContain('secret-key');
});
