<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\Finance;

use Illuminate\Support\Collection;
use NorthBees\CodeweaversApi\Data\ApiError;
use NorthBees\CodeweaversApi\Support\Value;

/**
 * The quotations for one vehicle request.
 */
final readonly class VehicleQuotations
{
    /**
     * @param  Collection<int, FinanceQuotation>  $quotations
     */
    public function __construct(
        public ?string $id,
        public ?ApiError $error,
        public Collection $quotations,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Value::string($data, 'Id'),
            error: ApiError::fromResult($data),
            quotations: collect(Value::list($data, 'FinanceQuotations'))->map(FinanceQuotation::fromArray(...))->values(),
        );
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }

    /**
     * Quotations that calculated without error.
     *
     * @return Collection<int, FinanceQuotation>
     */
    public function successful(): Collection
    {
        return $this->quotations->reject(fn (FinanceQuotation $quotation): bool => $quotation->hasError())->values();
    }
}
