<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Resources;

use NorthBees\CodeweaversApi\Data\JsonFinance\BulkCalculateResult;
use NorthBees\CodeweaversApi\Enums\Endpoint;
use NorthBees\CodeweaversApi\Requests\BulkCalculateRequest;

/**
 * The v3 JSON finance service.
 */
final class JsonFinanceResource extends Resource
{
    /**
     * Monthly payments for many vehicles across a matrix of terms, deposits and mileages.
     * Vehicles without a dealer are quoted as the client's AssociatedDealerKey organisation.
     */
    public function bulkCalculate(BulkCalculateRequest $request): BulkCalculateResult
    {
        $organisation = $this->codeweavers->organisation();

        if ($organisation?->isAssociatedDealerKey()) {
            $request = $request->withDefaultDealer($organisation->value);
        }

        return BulkCalculateResult::fromArray($this->send(Endpoint::BulkCalculate, $request->toArray()));
    }
}
