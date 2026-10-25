<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            // Americas
            ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$', 'country_code' => 'US', 'is_active' => true],
            ['name' => 'Canadian Dollar', 'code' => 'CAD', 'symbol' => 'C$', 'country_code' => 'CA', 'is_active' => false],
            ['name' => 'Mexican Peso', 'code' => 'MXN', 'symbol' => 'Mex$', 'country_code' => 'MX', 'is_active' => false],
            ['name' => 'Brazilian Real', 'code' => 'BRL', 'symbol' => 'R$', 'country_code' => 'BR', 'is_active' => false],
            ['name' => 'Argentine Peso', 'code' => 'ARS', 'symbol' => 'AR$', 'country_code' => 'AR', 'is_active' => false],
            ['name' => 'Chilean Peso', 'code' => 'CLP', 'symbol' => 'CLP$', 'country_code' => 'CL', 'is_active' => false],
            ['name' => 'Colombian Peso', 'code' => 'COP', 'symbol' => 'COL$', 'country_code' => 'CO', 'is_active' => false],

            // Europe
            ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€', 'country_code' => 'EU', 'is_active' => false],
            ['name' => 'British Pound', 'code' => 'GBP', 'symbol' => '£', 'country_code' => 'GB', 'is_active' => false],
            ['name' => 'Swiss Franc', 'code' => 'CHF', 'symbol' => 'CHF', 'country_code' => 'CH', 'is_active' => false],
            ['name' => 'Norwegian Krone', 'code' => 'NOK', 'symbol' => 'kr', 'country_code' => 'NO', 'is_active' => false],
            ['name' => 'Swedish Krona', 'code' => 'SEK', 'symbol' => 'kr', 'country_code' => 'SE', 'is_active' => false],
            ['name' => 'Danish Krone', 'code' => 'DKK', 'symbol' => 'kr', 'country_code' => 'DK', 'is_active' => false],
            ['name' => 'Polish Zloty', 'code' => 'PLN', 'symbol' => 'zł', 'country_code' => 'PL', 'is_active' => false],
            ['name' => 'Czech Koruna', 'code' => 'CZK', 'symbol' => 'Kč', 'country_code' => 'CZ', 'is_active' => false],
            ['name' => 'Hungarian Forint', 'code' => 'HUF', 'symbol' => 'Ft', 'country_code' => 'HU', 'is_active' => false],
            ['name' => 'Romanian Leu', 'code' => 'RON', 'symbol' => 'lei', 'country_code' => 'RO', 'is_active' => false],
            ['name' => 'Russian Ruble', 'code' => 'RUB', 'symbol' => '₽', 'country_code' => 'RU', 'is_active' => false],
            ['name' => 'Turkish Lira', 'code' => 'TRY', 'symbol' => '₺', 'country_code' => 'TR', 'is_active' => false],

            // Asia-Pacific
            ['name' => 'Japanese Yen', 'code' => 'JPY', 'symbol' => '¥', 'country_code' => 'JP', 'is_active' => false],
            ['name' => 'Chinese Yuan', 'code' => 'CNY', 'symbol' => '¥', 'country_code' => 'CN', 'is_active' => false],
            ['name' => 'Hong Kong Dollar', 'code' => 'HKD', 'symbol' => 'HK$', 'country_code' => 'HK', 'is_active' => false],
            ['name' => 'Singapore Dollar', 'code' => 'SGD', 'symbol' => 'S$', 'country_code' => 'SG', 'is_active' => false],
            ['name' => 'South Korean Won', 'code' => 'KRW', 'symbol' => '₩', 'country_code' => 'KR', 'is_active' => false],
            ['name' => 'Indian Rupee', 'code' => 'INR', 'symbol' => '₹', 'country_code' => 'IN', 'is_active' => false],
            ['name' => 'Indonesian Rupiah', 'code' => 'IDR', 'symbol' => 'Rp', 'country_code' => 'ID', 'is_active' => false],
            ['name' => 'Malaysian Ringgit', 'code' => 'MYR', 'symbol' => 'RM', 'country_code' => 'MY', 'is_active' => false],
            ['name' => 'Thai Baht', 'code' => 'THB', 'symbol' => '฿', 'country_code' => 'TH', 'is_active' => false],
            ['name' => 'Philippine Peso', 'code' => 'PHP', 'symbol' => '₱', 'country_code' => 'PH', 'is_active' => false],
            ['name' => 'Vietnamese Dong', 'code' => 'VND', 'symbol' => '₫', 'country_code' => 'VN', 'is_active' => false],
            ['name' => 'Pakistani Rupee', 'code' => 'PKR', 'symbol' => '₨', 'country_code' => 'PK', 'is_active' => false],
            ['name' => 'Bangladeshi Taka', 'code' => 'BDT', 'symbol' => '৳', 'country_code' => 'BD', 'is_active' => false],
            ['name' => 'Australian Dollar', 'code' => 'AUD', 'symbol' => 'A$', 'country_code' => 'AU', 'is_active' => false],
            ['name' => 'New Zealand Dollar', 'code' => 'NZD', 'symbol' => 'NZ$', 'country_code' => 'NZ', 'is_active' => false],

            // Middle East & Africa
            ['name' => 'Saudi Riyal', 'code' => 'SAR', 'symbol' => 'SR', 'country_code' => 'SA', 'is_active' => false],
            ['name' => 'UAE Dirham', 'code' => 'AED', 'symbol' => 'د.إ', 'country_code' => 'AE', 'is_active' => false],
            ['name' => 'Qatari Riyal', 'code' => 'QAR', 'symbol' => 'QR', 'country_code' => 'QA', 'is_active' => false],
            ['name' => 'Kuwaiti Dinar', 'code' => 'KWD', 'symbol' => 'KD', 'country_code' => 'KW', 'is_active' => false],
            ['name' => 'Israeli Shekel', 'code' => 'ILS', 'symbol' => '₪', 'country_code' => 'IL', 'is_active' => false],
            ['name' => 'Egyptian Pound', 'code' => 'EGP', 'symbol' => 'E£', 'country_code' => 'EG', 'is_active' => false],
            ['name' => 'South African Rand', 'code' => 'ZAR', 'symbol' => 'R', 'country_code' => 'ZA', 'is_active' => false],
            ['name' => 'Nigerian Naira', 'code' => 'NGN', 'symbol' => '₦', 'country_code' => 'NG', 'is_active' => false],
            ['name' => 'Kenyan Shilling', 'code' => 'KES', 'symbol' => 'KSh', 'country_code' => 'KE', 'is_active' => false],
            ['name' => 'Ghanaian Cedi', 'code' => 'GHS', 'symbol' => 'GH₵', 'country_code' => 'GH', 'is_active' => false],
            ['name' => 'Moroccan Dirham', 'code' => 'MAD', 'symbol' => 'MAD', 'country_code' => 'MA', 'is_active' => false],

            // Other major currencies
            ['name' => 'Iceland Krona', 'code' => 'ISK', 'symbol' => 'kr', 'country_code' => 'IS', 'is_active' => false],
            ['name' => 'Ukrainian Hryvnia', 'code' => 'UAH', 'symbol' => '₴', 'country_code' => 'UA', 'is_active' => false],
            ['name' => 'Croatian Kuna', 'code' => 'HRK', 'symbol' => 'kn', 'country_code' => 'HR', 'is_active' => false],
            ['name' => 'Peruvian Sol', 'code' => 'PEN', 'symbol' => 'S/', 'country_code' => 'PE', 'is_active' => false],
        ];

        foreach ($currencies as $currency) {
            \App\Models\Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
