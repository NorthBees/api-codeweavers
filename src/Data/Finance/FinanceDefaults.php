<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\Finance;

use Illuminate\Support\Collection;
use NorthBees\CodeweaversApi\Support\Value;

/**
 * The response from POST /api/finance/defaults.
 */
final readonly class FinanceDefaults
{
    /**
     * @param  Collection<int, ProductDefaults>  $products
     * @param  array<string, mixed>  $attributes  the raw response
     */
    public function __construct(
        public Collection $products,
        public ?ProductDefaults $combined = null,
        public array $attributes = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $combined = Value::object($data, 'CombinedProducts');

        return new self(
            products: collect(Value::list($data, 'Products'))->map(ProductDefaults::fromArray(...))->values(),
            combined: $combined === [] ? null : ProductDefaults::fromArray($combined),
            attributes: $data,
        );
    }

    /**
     * Products that calculated without error.
     *
     * @return Collection<int, ProductDefaults>
     */
    public function available(): Collection
    {
        return $this->products->reject(fn (ProductDefaults $product): bool => $product->hasError())->values();
    }

    /**
     * The product the dealer highlights first, falling back to the first available.
     */
    public function defaultProduct(): ?ProductDefaults
    {
        return $this->available()->first(fn (ProductDefaults $product): bool => $product->isDefault) ?? $this->available()->first();
    }
}
