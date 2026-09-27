<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Enums;

/**
 * The Codeweavers endpoints this SDK calls. The value is the key used by Codeweavers::fake().
 */
enum Endpoint: string
{
    case FinanceDefaults = 'finance.defaults';
    case CalculateForDisplay = 'finance.calculateForDisplay';
    case TermsAndConditions = 'finance.termsAndConditions';
    case BulkCalculate = 'jsonFinance.bulkCalculate';

    public function method(): string
    {
        return $this === self::TermsAndConditions ? 'GET' : 'POST';
    }

    public function path(string ...$parameters): string
    {
        return match ($this) {
            self::FinanceDefaults => '/api/finance/defaults',
            self::CalculateForDisplay => '/api/finance/calculatefordisplay',
            self::TermsAndConditions => '/api/finance/quote/'.rawurlencode($parameters[0] ?? '').'/termsandconditions',
            self::BulkCalculate => '/public/v3/jsonfinance/bulkcalculate',
        };
    }

    public static function fromPath(string $path): ?self
    {
        $path = '/'.strtolower(trim($path, '/'));

        return match (true) {
            $path === '/api/finance/defaults' => self::FinanceDefaults,
            $path === '/api/finance/calculatefordisplay' => self::CalculateForDisplay,
            (bool) preg_match('#^/api/finance/quote/[^/]+/termsandconditions$#', $path) => self::TermsAndConditions,
            $path === '/public/v3/jsonfinance/bulkcalculate' => self::BulkCalculate,
            default => null,
        };
    }
}
