<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Tests;

use Illuminate\Support\Facades\Http;
use NorthBees\CodeweaversApi\CodeweaversServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    protected function getPackageProviders($app)
    {
        return [
            CodeweaversServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('codeweavers.api_key', 'test-api-key');
        $app['config']->set('codeweavers.environment', 'sandbox');
        $app['config']->set('codeweavers.retry.sleep_ms', 0);
    }
}
