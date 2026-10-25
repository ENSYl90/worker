<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VendorType;

class CheckAccessMiddleware
{
    public function handle(Request $request, Closure $next, string $rule)
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $vendor = $user->vendor;
        $vendorType = $vendor?->vendor_type;

        switch ($rule) {
            case 'showPackage':
                $isParcel = $vendorType->is_parcel ?? false;
                if ($user->hasAnyRole('admin') || ($user->hasAnyRole('manager') && $isParcel)) {
                    return $next($request);
                }
                break;

            case 'showService':
                $isService = $vendorType->is_service ?? false;
                if ($user->hasAnyRole('admin') || ($user->hasAnyRole('manager') && $isService)) {
                    return $next($request);
                }
                break;

            case 'showProduct':
                $isParcel = $vendorType->is_parcel ?? false;
                $isService = $vendorType->is_service ?? false;
                $hasVendor = $vendor !== null;
                if ($user->hasAnyRole('admin') || (!$isParcel && !$isService && $hasVendor)) {
                    return $next($request);
                }
                break;

            case 'showPropertyBooking':
                $isBooking = $vendorType->is_booking ?? false;
                $permissions = [
                    'manage-properties',
                    'manage-property-types',
                    'manage-amenities',
                    'manage-cancellation-policies',
                    'manage-booking-fee-types',
                    "view-property-report",
                    "view-property-booking",
                ];
                $canIPerformAny = canIAny($permissions);
                if ($user->hasAnyRole('admin') || ($canIPerformAny && !$user->hasAnyRole('manager')) || ($user->hasAnyRole('manager') && $isBooking)) {
                    return $next($request);
                }
                break;

            case 'showDeliveryBoys':
                $showDeliveryBoysMenu = $vendor->has_drivers ?? false;
                if ($user->hasAnyRole('manager') && $showDeliveryBoysMenu) {
                    return $next($request);
                }
                break;

            case 'handleDeliveryBoys':
                $showDeliveryBoysMenu = $vendor->has_drivers ?? false;
                if ($user->hasAnyRole(['admin', 'city-admin'])) {
                    return $next($request);
                }
                if ($user->hasAnyRole('manager') && $showDeliveryBoysMenu) {
                    return $next($request);
                }
                break;

            case 'showDeliveryFeeSetting':
                if (setting('vendorSetDeliveryFee') || $user->hasAnyRole('admin')) {
                    return $next($request);
                }
                if ($user->hasAnyRole('manager') && ($vendor->has_drivers ?? false)) {
                    return $next($request);
                }
                break;

            case 'showNewParcelOrder':
                $isParcel = $vendorType->is_parcel ?? false;
                if ($user->hasAnyRole('manager') && $isParcel) {
                    return $next($request);
                }
                break;

            case 'showTaxiOrders':
                $isTaxiEnabled = VendorType::where("slug", "taxi")
                    ->where("is_active", 1)
                    ->exists();
                if ($isTaxiEnabled && $user->can('view-taxi-orders')) {
                    return $next($request);
                }
                break;
        }

        abort(403, 'Forbidden');
    }
}
