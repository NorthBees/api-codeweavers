<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\Finance;

use Illuminate\Support\Collection;
use NorthBees\CodeweaversApi\Data\ApiError;
use NorthBees\CodeweaversApi\Support\Value;

/**
 * One product's quotation for a vehicle, with its display content.
 */
final readonly class FinanceQuotation
{
    /**
     * @param  Collection<int, DisplayDetail>  $details  the display block details, in order
     * @param  list<string>  $notifications
     * @param  array<string, mixed>  $attributes  the raw quotation
     */
    public function __construct(
        public ?string $key,
        public ?ApiError $error,
        public ?Product $product,
        public ?Quote $quote,
        public Collection $details,
        public array $notifications,
        public array $attributes = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $finance = Value::object($data, 'Finance');
        $product = Value::object($finance, 'Product');
        $quote = Value::object($finance, 'Quote');

        return new self(
            key: Value::string($finance, 'Key'),
            error: ApiError::fromResult($data),
            product: $product === [] ? null : Product::fromArray($product),
            quote: $quote === [] ? null : Quote::fromArray($quote),
            details: collect(Value::list($data, 'Blocks'))
                ->flatMap(fn (array $block): array => Value::list($block, 'Details'))
                ->map(DisplayDetail::fromArray(...))
                ->values(),
            notifications: array_values(array_filter(array_map(
                fn (array $notification): ?string => Value::string($notification, 'Message'),
                [...Value::list($finance, 'Notifications'), ...Value::list($data, 'Notifications')],
            ))),
            attributes: $data,
        );
    }

    public function hasError(): bool
    {
        return $this->error !== null || $this->quote === null;
    }

    public function detail(string $key): ?DisplayDetail
    {
        return $this->details->first(fn (DisplayDetail $detail): bool => $detail->key === $key);
    }
}
