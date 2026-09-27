<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use NorthBees\CodeweaversApi\Http\HttpTransport;

/**
 * This is the service provider for the Codeweavers API package.
 */
class CodeweaversServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/codeweavers.php' => config_path('codeweavers.php'),
            ], 'codeweavers.config');
        }
    }

    /**
     * Register any package services.
     *
     * Scoped rather than singleton so queue workers and Octane never share a
     * client across requests or tenants.
     */
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/codeweavers.php', 'codeweavers');

        $this->app->scoped(Codeweavers::class, function (Application $app): Codeweavers {
            /** @var array<string, mixed> $config */
            $config = $app['config']->get('codeweavers', []);

            return new Codeweavers(new HttpTransport($config), $config);
        });

        $this->app->alias(Codeweavers::class, 'codeweavers');
    }
}
