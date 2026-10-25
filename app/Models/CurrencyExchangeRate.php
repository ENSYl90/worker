<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CurrencyExchangeRate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'currency_id',
        'rate',
        'effective_date',
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'effective_date' => 'date',
    ];

    /**
     * Get the currency that this exchange rate belongs to.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Scope to get rates for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('effective_date', '<=', $date)
            ->orderBy('effective_date', 'desc');
    }

    /**
     * Scope to get the latest rates.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('effective_date', 'desc');
    }

    /**
     * Scope to get rates for a specific currency.
     */
    public function scopeForCurrency($query, $currencyId)
    {
        return $query->where('currency_id', $currencyId);
    }

    /**
     * Get the latest rate for a specific currency.
     */
    public static function getLatestRate($currencyId)
    {
        return static::forCurrency($currencyId)
            ->latest()
            ->first()?->rate ?? 1;
    }

    /**
     * Get rate for a specific currency on a specific date.
     */
    public static function getRateForDate($currencyId, $date)
    {
        return static::forCurrency($currencyId)
            ->forDate($date)
            ->first()?->rate ?? 1;
    }

    /**
     * Convert amount from base currency to target currency.
     */
    public static function convertFromBase($amount, $targetCurrencyId, $date = null)
    {
        $rate = $date
            ? static::getRateForDate($targetCurrencyId, $date)
            : static::getLatestRate($targetCurrencyId);

        return $amount * $rate;
    }

    /**
     * Convert amount from source currency to target currency.
     */
    public static function convert($amount, $sourceCurrencyId, $targetCurrencyId, $date = null)
    {
        $sourceRate = $date
            ? static::getRateForDate($sourceCurrencyId, $date)
            : static::getLatestRate($sourceCurrencyId);

        $targetRate = $date
            ? static::getRateForDate($targetCurrencyId, $date)
            : static::getLatestRate($targetCurrencyId);

        // Convert to base currency first, then to target currency
        return ($amount / $sourceRate) * $targetRate;
    }
}
