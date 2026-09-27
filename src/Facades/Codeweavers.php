<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Facades;

use Illuminate\Support\Facades\Facade;
use NorthBees\CodeweaversApi\Testing\CodeweaversFake;

/**
 * @method static \NorthBees\CodeweaversApi\Codeweavers withCredentials(\NorthBees\CodeweaversApi\CodeweaversCredentials $credentials)
 * @method static \NorthBees\CodeweaversApi\Codeweavers withEnvironment(\NorthBees\CodeweaversApi\Enums\Environment $environment)
 * @method static \NorthBees\CodeweaversApi\Codeweavers withOrganisation(\NorthBees\CodeweaversApi\Requests\OrganisationIdentifier $organisation)
 * @method static \NorthBees\CodeweaversApi\CodeweaversCredentials credentials()
 * @method static bool hasCredentials()
 * @method static \NorthBees\CodeweaversApi\Enums\Environment environment()
 * @method static \NorthBees\CodeweaversApi\Requests\OrganisationIdentifier|null organisation()
 * @method static string baseUrl()
 * @method static \NorthBees\CodeweaversApi\Resources\FinanceResource finance()
 * @method static \NorthBees\CodeweaversApi\Resources\JsonFinanceResource jsonFinance()
 *
 * @see \NorthBees\CodeweaversApi\Codeweavers
 */
class Codeweavers extends Facade
{
    /**
     * Fake Codeweavers responses. Keys are endpoint names (e.g. "finance.defaults",
     * "jsonFinance.bulkCalculate") or "*"; values are decoded JSON bodies, Http::response()
     * results, or closures receiving the request payload and returning either.
     *
     * @param  array<string, mixed>  $responses
     */
    public static function fake(array $responses = []): CodeweaversFake
    {
        return CodeweaversFake::fake($responses);
    }

    protected static function getFacadeAccessor(): string
    {
        return \NorthBees\CodeweaversApi\Codeweavers::class;
    }
}
