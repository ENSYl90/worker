<?php

namespace App\Services;

use App\Models\VendorType;

class WebsiteModuleService
{
    /**
     * Fetch the active vendor-type modules used on the public website
     * welcome pages (food, grocery, taxi, parcel, pharmacy, service),
     * each cached individually for a short period.
     *
     * @return array<string, VendorType|null>
     */
    public static function getModules(): array
    {
        return [
            'food' => remember_result('food_module_fetch', function () {
                return VendorType::where('slug', 'food')->active()->first();
            }, 60),
            'grocery' => remember_result('grocery_module_fetch', function () {
                return VendorType::where('slug', 'grocery')->active()->first();
            }, 60),
            'taxi' => remember_result('taxi_module_fetch', function () {
                return VendorType::where('slug', 'taxi')->active()->first();
            }, 60),
            'parcel' => remember_result('parcel_module_fetch', function () {
                return VendorType::where('slug', ['parcel', 'package'])->active()->first();
            }, 60),
            'service' => remember_result('service_module_fetch', function () {
                return VendorType::where('slug', ['service', 'booking'])->active()->first();
            }, 60),
            'pharmacy' => remember_result('pharmacy_module_fetch', function () {
                return VendorType::where('slug', ['pharmacy', 'drug', 'medicine'])->active()->first();
            }, 60),
        ];
    }
}
