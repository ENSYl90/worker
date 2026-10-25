<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\CurrencyExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CurrencyExchangeRateService
{
    /**
     * Get structured exchange rates for Flutter app
     *
     * @param string|null $date Optional date (Y-m-d format), defaults to today
     * @param bool $useCache Whether to use cache
     * @return array
     */
    public function getStructuredRates(?string $date = null, bool $useCache = true)
    {
        $exchangeRateEnabled = (bool) setting('enableExchangeRate', true);
        if(!$exchangeRateEnabled){
            return (object)[];
        }
        $date = $date ? Carbon::parse($date)->format('Y-m-d') : now()->format('Y-m-d');
        $cacheKey = "exchange_rates_{$date}";

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $baseCurrency = baseCurrency();
        if (!$baseCurrency) {
            throw new \Exception(__('Base currency not found'));
        }

        // Get all active currencies
        $currencies = Currency::orderBy('code')->get();

        // Get exchange rates for the specified date or latest rates
        $rates = $this->getRatesForDate($date);

        $structuredData = [
            'base_currency' => [
                'id' => $baseCurrency->id,
                'name' => $baseCurrency->name,
                'code' => $baseCurrency->code,
                'symbol' => $baseCurrency->symbol,
                'country_code' => $baseCurrency->country_code,
            ],
            'effective_date' => $date,
            'last_updated' => now()->toISOString(),
            'currencies' => [],
            'rates' => [],
            'conversion_matrix' => [],
        ];

        // Build currencies list
        foreach ($currencies as $currency) {
            $structuredData['currencies'][] = [
                'id' => $currency->id,
                'name' => $currency->name,
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'country_code' => $currency->country_code,
                'is_base' => $currency->id === $baseCurrency->id,
            ];
        }

        // Build rates from base currency
        foreach ($currencies as $currency) {
            $rate = $rates->get($currency->id);
            $rateValue = $currency->id === $baseCurrency->id ? 1.0 : ($rate ? $rate->rate : null);

            $structuredData['rates'][$currency->code] = [
                'currency_id' => $currency->id,
                'rate' => $rateValue,
                'formatted_rate' => $rateValue ? number_format($rateValue, 8) : null,
                'is_available' => $rateValue !== null,
                'last_updated' => $rate ? $rate->updated_at->toISOString() : null,
            ];
        }

        // Build conversion matrix (from any currency to any currency)
        $structuredData['conversion_matrix'] = $this->buildConversionMatrix($currencies, $rates, $baseCurrency->id);

        if ($useCache) {
            Cache::put($cacheKey, $structuredData, now()->addHours(1)); // Cache for 1 hour
        }

        return $structuredData;
    }

    /**
     * Get rates for a specific date, fallback to latest rates
     *
     * @param string $date
     * @return Collection
     */
    private function getRatesForDate(string $date): Collection
    {
        // Try to get rates for the specific date
        $ratesForDate = CurrencyExchangeRate::where('effective_date', $date)
            ->get()
            ->keyBy('currency_id');

        // If no rates found for the date, get the latest rates for each currency
        if ($ratesForDate->isEmpty()) {
            $ratesForDate = CurrencyExchangeRate::whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('currency_exchange_rates')
                    ->whereNull('deleted_at')
                    ->groupBy('currency_id');
            })
                ->get()
                ->keyBy('currency_id');
        }

        return $ratesForDate;
    }

    /**
     * Build conversion matrix for direct currency-to-currency conversions
     *
     * @param Collection $currencies
     * @param Collection $rates
     * @param int $baseCurrencyId
     * @return array
     */
    private function buildConversionMatrix(Collection $currencies, Collection $rates, int $baseCurrencyId): array
    {
        $matrix = [];

        foreach ($currencies as $fromCurrency) {
            $fromRate = $fromCurrency->id === $baseCurrencyId ? 1.0 : $rates->get($fromCurrency->id)?->rate;

            foreach ($currencies as $toCurrency) {
                $toRate = $toCurrency->id === $baseCurrencyId ? 1.0 : $rates->get($toCurrency->id)?->rate;

                // Calculate conversion rate (from -> base -> to)
                $conversionRate = null;
                if ($fromRate && $toRate) {
                    $conversionRate = $toRate / $fromRate;
                }

                $matrix[$fromCurrency->code][$toCurrency->code] = [
                    'from_currency_id' => $fromCurrency->id,
                    'to_currency_id' => $toCurrency->id,
                    'rate' => $conversionRate,
                    'formatted_rate' => $conversionRate ? number_format($conversionRate, 8) : null,
                    'is_available' => $conversionRate !== null,
                ];
            }
        }

        return $matrix;
    }

    /**
     * Convert amount from one currency to another
     *
     * @param float $amount
     * @param string $fromCurrencyCode
     * @param string $toCurrencyCode
     * @param string|null $date
     * @return array
     */
    public function convertAmount(float $amount, string $fromCurrencyCode, string $toCurrencyCode, ?string $date = null): array
    {
        $rates = $this->getStructuredRates($date);

        if (!isset($rates['conversion_matrix'][$fromCurrencyCode][$toCurrencyCode])) {
            throw new \Exception(__('Currency conversion not available'));
        }

        $conversion = $rates['conversion_matrix'][$fromCurrencyCode][$toCurrencyCode];

        if (!$conversion['is_available']) {
            throw new \Exception(__('Exchange rate not available for this conversion'));
        }

        $convertedAmount = $amount * $conversion['rate'];

        return [
            'original_amount' => $amount,
            'converted_amount' => $convertedAmount,
            'formatted_converted_amount' => number_format($convertedAmount, 2),
            'from_currency' => $fromCurrencyCode,
            'to_currency' => $toCurrencyCode,
            'exchange_rate' => $conversion['rate'],
            'formatted_exchange_rate' => $conversion['formatted_rate'],
            'effective_date' => $rates['effective_date'],
            'calculation' => "{$amount} {$fromCurrencyCode} × {$conversion['formatted_rate']} = {$convertedAmount} {$toCurrencyCode}",
        ];
    }

    /**
     * Get simplified rates structure for mobile apps (smaller payload)
     *
     * @param string|null $date
     * @return array
     */
    public function getSimplifiedRates(?string $date = null): array
    {
        $fullRates = $this->getStructuredRates($date);

        return [
            'base_currency' => $fullRates['base_currency']['code'],
            'effective_date' => $fullRates['effective_date'],
            'last_updated' => $fullRates['last_updated'],
            'rates' => collect($fullRates['rates'])
                ->filter(fn($rate) => $rate['is_available'])
                ->mapWithKeys(fn($rate, $code) => [$code => $rate['rate']])
                ->toArray(),
        ];
    }

    /**
     * Get conversion rates for specific currencies only
     *
     * @param array $currencyCodes
     * @param string|null $date
     * @return array
     */
    public function getRatesForCurrencies(array $currencyCodes, ?string $date = null): array
    {
        $fullRates = $this->getStructuredRates($date);

        $filteredRates = [
            'base_currency' => $fullRates['base_currency'],
            'effective_date' => $fullRates['effective_date'],
            'last_updated' => $fullRates['last_updated'],
            'rates' => [],
            'conversion_matrix' => [],
        ];

        // Filter rates for specific currencies
        foreach ($currencyCodes as $code) {
            if (isset($fullRates['rates'][$code])) {
                $filteredRates['rates'][$code] = $fullRates['rates'][$code];
            }
        }

        // Filter conversion matrix for specific currencies
        foreach ($currencyCodes as $fromCode) {
            foreach ($currencyCodes as $toCode) {
                if (isset($fullRates['conversion_matrix'][$fromCode][$toCode])) {
                    $filteredRates['conversion_matrix'][$fromCode][$toCode] =
                        $fullRates['conversion_matrix'][$fromCode][$toCode];
                }
            }
        }

        return $filteredRates;
    }

    /**
     * Clear exchange rates cache
     *
     * @param string|null $date Specific date to clear, or null to clear all
     * @return void
     */
    public function clearCache(?string $date = null): void
    {
        if ($date) {
            $date = Carbon::parse($date)->format('Y-m-d');
            Cache::forget("exchange_rates_{$date}");
        } else {
            // Clear all exchange rate caches (you might want to use cache tags for this)
            $keys = Cache::get('exchange_rate_cache_keys', []);
            foreach ($keys as $key) {
                Cache::forget($key);
            }
            Cache::forget('exchange_rate_cache_keys');
        }
    }

    /**
     * Get available currencies with their latest rates
     *
     * @return array
     */
    public function getAvailableCurrencies(): array
    {
        $rates = $this->getStructuredRates();

        return collect($rates['currencies'])
            ->map(function ($currency) use ($rates) {
                $rate = $rates['rates'][$currency['code']] ?? null;
                return [
                    'id' => $currency['id'],
                    'name' => $currency['name'],
                    'code' => $currency['code'],
                    'symbol' => $currency['symbol'],
                    'country_code' => $currency['country_code'],
                    'is_base' => $currency['is_base'],
                    'has_rate' => $rate ? $rate['is_available'] : false,
                    'rate' => $rate ? $rate['rate'] : null,
                ];
            })
            ->values()
            ->toArray();
    }
}