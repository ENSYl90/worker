<?php

use App\Models\Currency;

function currencyFormat($value, $currency = null)
{

    //format currency
    $value = number_format(
        (float) $value,
        setting('ui.currency.decimals', 2),
        setting('ui.currency.decimal_format', "."),
        setting('ui.currency.format', ",")
    );

    //
    if (empty($currency)) {
        $currency = setting('currency', '$');
    }

    //side
    $currencySide = setting('ui.currency.location', 'left');
    if (strtolower($currencySide) != "left") {
        return $value . " " . $currency;
    } else {
        return $currency . " " . $value;
    }
}

function currencyValueFormat($value)
{

    //format currency
    $value = number_format(
        (float) $value,
        setting('ui.currency.decimals', 2),
        setting('ui.currency.decimal_format', "."),
        setting('ui.currency.format', ",")
    );
    return (float)$value;
}


function baseCurrency(): ?Currency
{
    return Currency::where('country_code', setting("currencyCountryCode", "GH"))->first();
}

function getCurrencyInputStep(): string
{
    $decimals = setting('ui.currency.decimals', 2);

    return '0.' . str_repeat('0', $decimals - 1) . '1';
}
