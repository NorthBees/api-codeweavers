<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Testing;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

/**
 * Builds realistic Codeweavers response bodies for tests.
 */
final class CodeweaversResponse
{
    /**
     * A finance defaults response.
     *
     * @param  list<array<string, mixed>>  $products  see product()
     * @return array<string, mixed>
     */
    public static function defaults(array $products = []): array
    {
        return ['Products' => $products === [] ? [self::product('HP'), self::product('PCP', ['IsResidualValueBased' => true])] : $products];
    }

    /**
     * One product in a finance defaults response.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function product(string $key = 'HP', array $overrides = []): array
    {
        return array_replace_recursive([
            'HasError' => false,
            'Key' => $key,
            'Name' => $key === 'PCP' ? 'Personal Contract Purchase' : 'Hire Purchase',
            'Type' => $key,
            'Lender' => 'Test Finance Ltd',
            'IsDefault' => $key === 'HP',
            'IsResidualValueBased' => $key === 'PCP',
            'Deposit' => ['Default' => 1000, 'Minimum' => 0, 'Maximum' => 10000, 'IsRequired' => true],
            'Term' => ['Default' => 48, 'Values' => [24, 36, 48, 60], 'IsRequired' => true],
            'AnnualMileage' => ['Default' => 10000, 'Values' => [6000, 8000, 10000, 12000], 'IsRequired' => $key === 'PCP'],
        ], $overrides);
    }

    /**
     * A calculate for display response.
     *
     * @param  list<array<string, mixed>>  $vehicles  see vehicle()
     * @return array<string, mixed>
     */
    public static function calculateForDisplay(array $vehicles): array
    {
        return ['Vehicles' => $vehicles];
    }

    /**
     * One vehicle's quotations in a calculate for display response.
     *
     * @param  list<array<string, mixed>>  $quotations  see quotation()
     * @return array<string, mixed>
     */
    public static function vehicle(string $id, array $quotations = []): array
    {
        return ['Id' => $id, 'HasError' => false, 'FinanceQuotations' => $quotations === [] ? [self::quotation()] : $quotations];
    }

    /**
     * One product's quotation.
     *
     * @param  array<string, mixed>  $quote  overrides for the quote figures
     * @param  array<string, mixed>  $product  overrides for the product
     * @return array<string, mixed>
     */
    public static function quotation(string $key = 'HP', array $quote = [], array $product = []): array
    {
        return [
            'HasError' => false,
            'Finance' => [
                'Key' => $key,
                'Product' => array_replace([
                    'Key' => $key,
                    'Type' => $key,
                    'Name' => $key === 'PCP' ? 'Personal Contract Purchase' : 'Hire Purchase',
                    'Lender' => 'Test Finance Ltd',
                    'IsResidualBased' => $key === 'PCP',
                    'HasApr' => true,
                ], $product),
                'Quote' => array_replace_recursive([
                    'TotalPrice' => 15000.0,
                    'Deposit' => ['Cash' => 1000.0],
                    'TotalDeposit' => 1000.0,
                    'Balance' => 14000.0,
                    'Term' => 48,
                    'RegularPayment' => 349.99,
                    'TotalFirstPayment' => 349.99,
                    'TotalNumberOfRegularPayments' => 48,
                    'TotalAmountPayable' => 17799.52,
                    'ChargesForCredit' => 2799.52,
                    'Apr' => 9.9,
                    'RateOfInterest' => 5.12,
                    'Residual' => $key === 'PCP' ? 6000.0 : 0.0,
                    'AnnualMileage' => 10000,
                    'ExcessMileageRate' => $key === 'PCP' ? 8.5 : null,
                    'QuoteReference' => 'Q-'.$key.'-123',
                    'IsRepresentativeExample' => true,
                    'Fees' => [['Type' => 'Purchase', 'Amount' => 10.0]],
                    'Payments' => [['Amount' => 349.99, 'NumberOfPayments' => 48]],
                ], $quote),
                'Notifications' => [],
            ],
            'Blocks' => [],
        ];
    }

    /**
     * A bulk calculate response.
     *
     * @param  list<array<string, mixed>>  $vehicles  see bulkVehicle() and bulkGrid()
     * @return array<string, mixed>
     */
    public static function bulkCalculate(array $vehicles): array
    {
        return ['Duration' => 120, 'HasError' => false, 'VehicleResults' => $vehicles];
    }

    /**
     * One vehicle's payment matrix in a bulk calculate response.
     *
     * @param  list<array<string, mixed>>  $rows  see bulkRow()
     * @return array<string, mixed>
     */
    public static function bulkVehicle(string $id, array $rows): array
    {
        return ['Id' => $id, 'FinanceProductResults' => $rows];
    }

    /**
     * @param  list<array<string, mixed>>  $products  see bulkProduct()
     * @return array<string, mixed>
     */
    public static function bulkRow(int $term, int|float $deposit, int $annualMileage, array $products): array
    {
        return ['Term' => $term, 'AnnualMileage' => $annualMileage, 'Deposits' => $deposit, 'ProductResults' => $products];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function bulkProduct(string $key, float $payment, array $overrides = []): array
    {
        return array_replace([
            'Key' => $key,
            'Name' => $key === 'PCP' ? 'Personal Contract Purchase' : 'Hire Purchase',
            'Type' => $key,
            'Payment' => $payment,
            'Apr' => 9.9,
            'TotalAmountPayable' => round($payment * 48, 2),
            'FinalPayment' => $key === 'PCP' ? 6000.0 : 0.0,
            'Lender' => 'Test Finance Ltd',
            'HasError' => false,
        ], $overrides);
    }

    /**
     * A vehicle's full payment matrix, with payments from a callback.
     *
     * @param  list<int>  $terms
     * @param  list<int|float>  $deposits
     * @param  list<int>  $annualMileages
     * @param  list<string>  $products
     * @param  (Closure(string, int, int|float, int): ?float)|null  $payment  (product, term, deposit, mileage) => payment, null to omit
     * @return array<string, mixed>
     */
    public static function bulkGrid(string $id, array $terms, array $deposits, array $annualMileages, array $products = ['HP', 'PCP'], ?Closure $payment = null): array
    {
        $payment ??= fn (string $product, int $term, int|float $deposit, int $mileage): float => round(12000 / $term - $deposit / 100 + $mileage / 1000 - ($product === 'PCP' ? 60 : 0), 2);
        $rows = [];

        foreach ($terms as $term) {
            foreach ($deposits as $deposit) {
                foreach ($annualMileages as $mileage) {
                    $rows[] = self::bulkRow($term, $deposit, $mileage, array_values(array_filter(array_map(
                        fn (string $product): ?array => ($amount = $payment($product, $term, $deposit, $mileage)) === null ? null : self::bulkProduct($product, $amount),
                        $products,
                    ))));
                }
            }
        }

        return self::bulkVehicle($id, $rows);
    }

    /**
     * An error response with the root error object.
     */
    public static function error(string $message = 'Invalid request', int $status = 400, ?string $code = null): PromiseInterface
    {
        return Http::response(['Error' => ['UserMessage' => $message, 'TechnicalMessage' => $message, 'Code' => $code]], $status);
    }
}
