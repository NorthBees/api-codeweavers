<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Resources;

use NorthBees\CodeweaversApi\Data\Finance\CalculateForDisplayResult;
use NorthBees\CodeweaversApi\Data\Finance\FinanceDefaults;
use NorthBees\CodeweaversApi\Data\Finance\TermsAndConditions;
use NorthBees\CodeweaversApi\Enums\Endpoint;
use NorthBees\CodeweaversApi\Requests\CalculateForDisplayRequest;
use NorthBees\CodeweaversApi\Requests\FinanceDefaultsRequest;

/**
 * Finance calculations for individual vehicles.
 */
final class FinanceResource extends Resource
{
    /**
     * The finance products available for a vehicle, with their deposit, term and mileage options.
     */
    public function defaults(FinanceDefaultsRequest $request): FinanceDefaults
    {
        $organisation = $this->codeweavers->organisation();

        if ($request->organisation === null && $organisation !== null) {
            $request = $request->withOrganisation($organisation);
        }

        return FinanceDefaults::fromArray($this->send(Endpoint::FinanceDefaults, $request->toArray()));
    }

    /**
     * Full quotes, with display content, for each available product.
     */
    public function calculateForDisplay(CalculateForDisplayRequest $request): CalculateForDisplayResult
    {
        $organisation = $this->codeweavers->organisation();

        if ($organisation !== null) {
            $request = $request->withDefaultOrganisation($organisation);
        }

        return CalculateForDisplayResult::fromArray($this->send(Endpoint::CalculateForDisplay, $request->toArray()));
    }

    public function termsAndConditions(string $quoteReference): TermsAndConditions
    {
        return new TermsAndConditions($quoteReference, $this->send(Endpoint::TermsAndConditions, null, $quoteReference));
    }
}
