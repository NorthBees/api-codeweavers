<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\Finance;

/**
 * The response from GET /api/finance/quote/{reference}/termsandconditions. The shape
 * depends on the lender, so the decoded body is exposed as-is.
 */
final readonly class TermsAndConditions
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public string $quoteReference,
        public array $attributes,
    ) {}
}
