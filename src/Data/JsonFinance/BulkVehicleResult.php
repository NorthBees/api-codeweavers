<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\JsonFinance;

use Illuminate\Support\Collection;
use NorthBees\CodeweaversApi\Data\ApiError;
use NorthBees\CodeweaversApi\Support\Value;

/**
 * The payment matrix for one vehicle in a bulk calculation.
 */
final readonly class BulkVehicleResult
{
    /**
     * @param  Collection<int, BulkRow>  $rows
     */
    public function __construct(
        public ?string $id,
        public ?ApiError $error,
        public Collection $rows,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Value::string($data, 'Id'),
            error: ApiError::fromResult($data),
            rows: collect(Value::list($data, 'FinanceProductResults'))->map(BulkRow::fromArray(...))->values(),
        );
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }
}
