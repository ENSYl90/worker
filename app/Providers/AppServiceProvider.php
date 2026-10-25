<?php

namespace App\Providers;

use anlutro\LaravelSettings\Facades\Setting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Services\Core\ExtraPhoneNumberValidationService;
use App\Services\CustomDatabaseSettingStore;
use Propaganistas\LaravelPhone\Rules\Phone as CustomPhoneRule;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Validator::includeUnvalidatedArrayKeys();
        Schema::defaultStringLength(191);

        // ✅ Fix 1: Cache timezone — only query DB once, then cache it
        $this->configureTimezone();

        // ✅ Fix 2: Only run mail override in non-production
        if (!$this->app->environment('production')) {
            $this->configureDevMail();
        }

        // ✅ Fix 3: Register blade directives
        $this->registerBladeDirectives();

        // Validator extension
        Validator::extendDependent('phone', function ($attribute, $value, $parameters, $validator) {
            $passed = (new CustomPhoneRule())->setValidator($validator)->passes($attribute, $value);
            if (!$passed) {
                $value = str_replace(" ", "", $value);
                return ExtraPhoneNumberValidationService::validateCustomRegex($value);
            }
            return true;
        });

        Setting::extend('customDatabaseSettingStore', function ($app) {
            return $app->make(CustomDatabaseSettingStore::class);
        });
    }

    /**
     * ✅ Cache timezone in file cache — DB only hit once per cache clear
     */
    protected function configureTimezone(): void
    {
        try {
            $timezone = cache()->remember('app.timezone', now()->addDay(), function () {
                // Only check DB connection once, cache the result
                DB::connection()->getPdo();
                if (Schema::hasTable('settings')) {
                    return setting('timeZone', 'UTC');
                }
                return 'UTC';
            });
            date_default_timezone_set($timezone);
        } catch (Exception $ex) {
            date_default_timezone_set('UTC');
        }
    }

    /**
     * ✅ Dev mail config isolated
     */
    protected function configureDevMail(): void
    {
        try {
            $supportEmails = config('backend.support.email');
            $isHostSet = config('mail.host') != null;
            if ($isHostSet && !empty($supportEmails)) {
                Mail::alwaysTo($supportEmails);
            }
        } catch (Exception $ex) {
            logger("Mail Always to Error", [$ex]);
        }
    }

    /**
     * ✅ All blade directives in one place
     * Auth::user() is already cached by Laravel after first call — no extra DB hits
     */
    protected function registerBladeDirectives(): void
    {
        Blade::if('showPackage', function () {
            $user = Auth::user();
            if (!$user)
                return false;
            $isParcel = $user->vendor->vendor_type->is_parcel ?? false;
            return $user->hasAnyRole('admin') || ($user->hasAnyRole('manager') && $isParcel);
        });

        Blade::if('showService', function () {
            $user = Auth::user();
            if (!$user)
                return false;
            $isService = $user->vendor->vendor_type->is_service ?? false;
            return $user->hasAnyRole('admin') || ($user->hasAnyRole('manager') && $isService);
        });

        Blade::if('showPropertyBooking', function () {
            $user = Auth::user();
            if (!$user)
                return false;
            $isBooking = $user->vendor->vendor_type->is_booking ?? false;
            $canIPerformAny = canIAny(['manage-properties', 'manage-property-types', 'manage-amenities', 'manage-cancellation-policies', 'manage-booking-fee-types']);
            return $user->hasAnyRole('admin')
                || ($canIPerformAny && !$user->hasAnyRole('manager'))
                || ($user->hasAnyRole('manager') && $isBooking);
        });

        Blade::if('showProduct', function () {
            $user = Auth::user();
            if (!$user)
                return false;
            $vendorType = $user->vendor->vendor_type ?? null;
            $hasVendor = $user->vendor != null;
            $isParcel = $vendorType->is_parcel ?? false;
            $isService = $vendorType->is_service ?? false;
            $isBooking = $vendorType->is_booking ?? false;
            return $user->hasAnyRole('admin') || (!$isParcel && !$isService && !$isBooking && $hasVendor);
        });

        Blade::if('showDeliveryBoys', function () {
            $user = Auth::user();
            if (!$user)
                return false;
            return $user->hasAnyRole('manager') && ($user->vendor->has_drivers ?? false);
        });

        Blade::if('handleDeliveryBoys', function () {
            $user = Auth::user();
            if (!$user)
                return false;
            if ($user->hasAnyRole('admin|city-admin'))
                return true;
            return $user->hasAnyRole('manager') && ($user->vendor->has_drivers ?? false);
        });

        Blade::if('showDeliveryFeeSetting', function () {
            $user = Auth::user();
            if (!$user)
                return false;
            if (setting('vendorSetDeliveryFee') || $user->hasAnyRole('admin'))
                return true;
            return $user->hasAnyRole('manager') && ($user->vendor->has_drivers ?? false);
        });

        Blade::if('showNewParcelOrder', function () {
            $user = Auth::user();
            if (!$user || !$user->hasAnyRole('manager'))
                return false;
            return $user->vendor->vendor_type->is_parcel ?? false;
        });

        Blade::if('showRegularOrders', function () {
            $user = Auth::user();
            if (!$user || !$user->can('view-orders'))
                return false;
            if ($user->hasAnyRole('manager')) {
                if (!$user->vendor)
                    return false;
                $slug = $user->vendor->vendor_type->slug ?? "";
                return !in_array($slug, ["booking", "taxi"]);
            }
            return true;
        });

        Blade::if('showPropertyOrders', function () {
            $user = Auth::user();
            if (!$user || !$user->canAny(['view-orders', 'view-my-bookings', 'view-all-bookings']))
                return false;
            if ($user->hasAnyRole('manager')) {
                if (!$user->vendor)
                    return false;
                $slug = $user->vendor->vendor_type->slug ?? "";
                return in_array($slug, ["booking"]);
            }

            if ($user->hasAnyRole('admin') || $user->can('view-property-booking')) {
                return true;
            }

            return false;
        });

        Blade::if('showTaxiOrders', function () {
            $user = Auth::user();
            if (!$user || !$user->can('view-taxi-orders'))
                return false;

            // ✅ Cache this query — VendorType rarely changes
            $isTaxiEnabled = cache()->remember('vendor_type.taxi.active', now()->addHour(), function () {
                return \App\Models\VendorType::where("slug", "taxi")
                    ->where("is_active", 1)
                    ->exists(); // .exists() is faster than .first() != null
            });

            return $isTaxiEnabled;
        });
    }
}