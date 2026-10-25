<?php

use App\Http\Controllers\API\AppSettingsController;
use App\Http\Controllers\API\DriverRemittanceController;
use App\Http\Controllers\API\DriverSubscriptionController;
use App\Http\Controllers\API\FavouritePropertyController;
use App\Http\Controllers\API\PropertyReportController;
use App\Http\Controllers\API\UserActiveOrdersController;
use App\Http\Controllers\API\UserFirebaseController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CancellationReasonController;
use App\Http\Controllers\API\AccountManagementController;
use App\Http\Controllers\API\TagController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\BannerController;
use App\Http\Controllers\API\FavouriteController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\VendorController;
use App\Http\Controllers\API\PartnerController;
use App\Http\Controllers\API\VendorProductController;
use App\Http\Controllers\API\VendorMenuController;
use App\Http\Controllers\API\VendorTypeController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\SearchDataController;
use App\Http\Controllers\API\SearchController;
use App\Http\Controllers\API\DeliveryAddressController;
use App\Http\Controllers\API\PaymentMethodController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\TrackOrderController;
use App\Http\Controllers\API\PackageOrderController;
use App\Http\Controllers\API\RegularOrderController;
use App\Http\Controllers\API\PackageTypeController;
use App\Http\Controllers\API\ChatNotificationController;
use App\Http\Controllers\API\DriverTypeController;
use App\Http\Controllers\API\RatingController;
use App\Http\Controllers\API\EarningController;
use App\Http\Controllers\API\ExternalRedirectController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\OrderPaymentCallbackController;
use App\Http\Controllers\API\WebhookPaymentCallbackController;
use App\Http\Controllers\API\OTPController;
use App\Http\Controllers\API\SocialLoginController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\ProductReviewController;
use App\Http\Controllers\API\ProductReviewSummaryController;
use App\Http\Controllers\API\VendorPackageTypePricingController;
use App\Http\Controllers\API\VendorServiceController;
use App\Http\Controllers\API\VehicleTypeController;
use App\Http\Controllers\API\TaxiOrderController;
use App\Http\Controllers\API\TaxiDriverOrderController;
use App\Http\Controllers\API\DriverLocationSyncController;
use App\Http\Controllers\API\PaymentAccountController;
use App\Http\Controllers\API\PayoutController;
use App\Http\Controllers\API\WalletTransferController;
use App\Http\Controllers\API\LoyaltyPointController;
use App\Http\Controllers\API\GeocoderController;
use App\Http\Controllers\API\ProductMiscController;
use App\Http\Controllers\API\FlashSaleController;
use App\Http\Controllers\API\OnboardingController;
use App\Http\Controllers\API\FaqController;
use App\Http\Controllers\API\SignedMediaController;
use App\Http\Controllers\API\MyVendorController;
use App\Http\Controllers\API\VendorReportController;
use App\Http\Controllers\API\DriverReportController;
use App\Http\Controllers\API\DocumentRequestController;
use App\Http\Controllers\API\FavouriteVendorController;
use App\Http\Controllers\API\FavouriteServiceController;
use App\Http\Controllers\API\PackageVendorController;
use App\Http\Controllers\API\PropertyController;
use App\Http\Controllers\API\BookingOrderController;
use App\Http\Controllers\API\VendorPropertyController;
use App\Http\Controllers\API\VendorBookingController;
use App\Http\Controllers\API\PropertyTypeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//CRON Job
Route::get('/cron/job', function (Request $request) {
    //
    $appKey = env("CRON_JOB_KEY", "");
    $urlAppKey = str_ireplace(" ", "+", $request->key);
    //
    if ($appKey != $urlAppKey) {
        return response()->json([
            "message" => "Unauthorized",
        ], 401);
    }

    $artisan = \Artisan::call("schedule:run");
    $output = \Artisan::output();
    return response()->json([
        "message" => "schedule runed",
        "output" => $output
    ]);
})->name('cron.job');


Route::get('/livewire/publish', function (Request $request) {
    $commandArray = ["php", "artisan", "livewire:publish", "--assets"];
    $commandRunner = new \Symfony\Component\Process\Process($commandArray);
    $commandRunner->setWorkingDirectory(base_path());
    $commandRunner->run();

    if (!$commandRunner->isSuccessful()) {
        throw new \Symfony\Component\Process\Exception\ProcessFailedException($commandRunner);
    }

    return response()->json([
        "message" => "publish runed",
    ]);
});


//App settings
Route::get('/app/settings', [AppSettingsController::class, 'index']);
Route::get('/app/onboarding', [OnboardingController::class, 'index']);
Route::get('/app/faqs', [FaqController::class, 'index']);
//new api route name
Route::get('/settings', [AppSettingsController::class, 'index']);
Route::get('/onboarding', [OnboardingController::class, 'index']);
Route::get('/faqs', [FaqController::class, 'index']);
Route::get('/cancellation/reasons', [CancellationReasonController::class, 'index']);

// Auth
Route::group(['middleware' => ['throttle:otp']], function () {
    //otps
    Route::post('otp/send', [OTPController::class, 'sendOTP'])->name('otp.send');
    Route::post('otp/verify', [OTPController::class, 'verifyOTP'])->name('otp.verify');
    Route::post('otp/firebase/verify', [OTPController::class, 'verifyFirebaseToken'])->name('otp.firebase.verify');
});

Route::post('login', [AuthController::class, 'login']);
Route::post('vendor/register', [PartnerController::class, 'vendor']);
Route::post('driver/register', [PartnerController::class, 'driver']);
Route::post('login/qrcode', [AuthController::class, 'qrlogin']);
Route::post('social/login', [SocialLoginController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::get('verify/phone', [AuthController::class, 'verifyPhoneAccount']);
Route::post('password/reset/init', [AuthController::class, 'passwordReset']);
// Route::get('password/update/{code}/{email}', PasswordResetLivewire::class)->name('password.reset.link');

Route::get('tags', [TagController::class, "index"]);
Route::get('categories', [CategoryController::class, "index"]);
Route::get('banners', [BannerController::class, "index"]);
Route::apiResource('products', ProductController::class)->only('index', 'show');
Route::get('product/review/summary', [ProductReviewSummaryController::class, 'index']);
Route::get('product/frequent', [ProductMiscController::class, 'frequent']);
Route::apiResource('services', ServiceController::class);
Route::get('service/durations', [ServiceController::class, 'durations']);
Route::apiResource('vendors', VendorController::class);
Route::get('vendor/reviews', [ReviewController::class, 'index']);
Route::get('product/reviews', [ProductReviewController::class, 'index']);
Route::apiResource('vendor/types', VendorTypeController::class);
Route::get('coupons/{code}', [CouponController::class, 'show']);
Route::get('coupons', [CouponController::class, 'index']);
Route::get('coupons/details/{id}', [CouponController::class, 'details']);
Route::get('search', [SearchController::class, 'index']);
Route::get('search/data', [SearchDataController::class, 'index']);
Route::get('flash/sales', [FlashSaleController::class, 'index']);

//Booking System - Public Property Routes (no authentication required)
Route::get('property-types', [PropertyTypeController::class, 'index']);
Route::get('properties', [PropertyController::class, 'index']);
Route::get('properties/{id}', [PropertyController::class, 'show']);
Route::get('properties/{id}/reviews', [PropertyController::class, 'reviews']);
Route::get('properties/{id}/similar', [PropertyController::class, 'similar']);
Route::post('properties/{id}/check-availability', [PropertyController::class, 'checkAvailability']);
Route::post('properties/{id}/calculate-price', [PropertyController::class, 'calculatePrice']);
Route::get('property/amenities', [PropertyController::class, 'amenities']);
Route::get('property/room/types', [PropertyController::class, 'roomTypes']);
Route::get('property/report/reasons', [PropertyReportController::class, 'reasons']);
Route::post('property/report', [PropertyReportController::class, 'store'])->middleware('auth:sanctum');

//package delivery
Route::get('package/types', [PackageTypeController::class, 'index']);
Route::get('partner/vehicle/types', [PartnerController::class, 'vehicleTypes']);
Route::get('partner/car/makes', [PartnerController::class, 'carMakes']);
Route::get('partner/car/models', [PartnerController::class, 'carModels']);
Route::get('package/vendor/{id}/pricings', [PackageVendorController::class, 'vendorPricing']);
Route::get('package/vendor/{id}/area/of/operations', [PackageVendorController::class, 'areaOfOperations']);
//
Route::post('order/payment/callback', [OrderPaymentCallbackController::class, 'order'])->name('api.payment.callback');
Route::post('wallet/topup/callback', [OrderPaymentCallbackController::class, 'wallet'])->name('api.wallet.topup.callback');
Route::post('subscription/callback', [OrderPaymentCallbackController::class, 'subscription'])->name('api.subscription.callback');
Route::any('external/payment/callback/{hash}', [WebhookPaymentCallbackController::class, 'index'])->name('api.payment.webhook');
Route::apiResource('payment/methods', PaymentMethodController::class)->only('index');
Route::get('setting/notification/sound-config', function () {
    return response()->json(\App\Models\NotificationSoundSetting::current());
});

//geocoding: add throttle of max 10 requests per minute; responses are also cached (see GeocoderController) to reduce provider API usage
Route::group(['middleware' => ['throttle:' . env('GEOCODER_THROTTLE', 10)]], function () {
    Route::get('geocoder/{type}', [GeocoderController::class, 'index']);
    Route::get('geocoder/2/reserve', [GeocoderController::class, 'newReverse']);
    Route::get('geocoder/place/details', [GeocoderController::class, 'reverseDetails']);
});
Route::get('/download/digital/files/{id}', [SignedMediaController::class, 'download'])->name('digital.download');
//Server run external apis
Route::apiResource('external/redirect', ExternalRedirectController::class)->only('index');
Route::get('external/web/redirect', [ExternalRedirectController::class, 'webRedirect']);




Route::group(['middleware' => ['auth:sanctum', "user.active.check"]], function () {

    Route::apiResource('favourites', FavouriteController::class);
    Route::apiResource('favourite/vendors', FavouriteVendorController::class)->names('favourite.vendors');
    Route::apiResource('favourite/services', FavouriteServiceController::class)->names('favourite.services');
    Route::apiResource('favourite/properties', FavouritePropertyController::class)->names('favourite.properties');
    Route::post('device/token/sync', [UserFirebaseController::class, 'syncTokens']);
    Route::get('logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('my/profile', [UserController::class, 'myProfile']);
    Route::put('profile/update', [AuthController::class, 'profileUpdate']);
    Route::put('profile/password/update', [AuthController::class, 'changePassword']);
    Route::delete('account/delete', [AccountManagementController::class, 'delete']);
    Route::apiResource('delivery/addresses', DeliveryAddressController::class);
    Route::apiResource('orders', OrderController::class)->only('index', 'show', 'update');
    Route::post('orders', [OrderController::class, 'store']);
    Route::post('orders/active-count/emit', [UserActiveOrdersController::class, 'emit']);
    Route::post('/track/order', [TrackOrderController::class, "track"]);
    Route::apiResource('rating', RatingController::class)->only('store');
    Route::post('product/reviews', [ProductReviewController::class, 'store']);
    //package delivery
    Route::post('package/order/vendors', [PackageOrderController::class, 'fetchVendors']);
    Route::get('package/order/summary', [PackageOrderController::class, 'summary']);
    Route::get('general/order/delivery/fee/summary', [RegularOrderController::class, 'deliveryFeeSummary']);
    Route::post('general/order/summary', [RegularOrderController::class, 'summary']);
    Route::post('service/order/summary', [RegularOrderController::class, 'serviceSummary']);
    Route::post('package/order/stop/verify/{id}', [PackageOrderController::class, 'verifyOrderStop']);
    //
    Route::post('chat/notification', [ChatNotificationController::class, 'send']);

    //earning
    Route::get('earning/user', [EarningController::class, 'user']);
    Route::get('earning/vendor', [EarningController::class, 'vendor']);
    Route::get('users', [UserController::class, 'index']);
    Route::get('vendor/{id}/details', [VendorController::class, 'fullDeatils']);

    //wallets
    Route::get('wallet/balance', [WalletController::class, 'index']);
    Route::post('wallet/topup', [WalletController::class, 'topup']);
    Route::get('wallet/transactions', [WalletController::class, 'transactions']);
    Route::post('wallet/transfer', [WalletController::class, 'transferBalance']);

    //taxi booking
    Route::get('vehicle/types', [VehicleTypeController::class, 'index']);
    Route::get('vehicle/types/pricing', [VehicleTypeController::class, 'calculateFee']);
    Route::post('taxi/order/summary', [TaxiOrderController::class, 'summary']);
    Route::post('taxi/book/order', [TaxiOrderController::class, 'book']);
    Route::get('taxi/current/order', [TaxiOrderController::class, 'current']);
    Route::get('taxi/rateable/order', [TaxiOrderController::class, 'currentRateable']);
    Route::get('taxi/order/cancel/{id}', [TaxiOrderController::class, 'cancelOrder']);
    Route::get('taxi/driver/info/{id}', [TaxiOrderController::class, 'driverInfo']);
    Route::get('taxi/location/available', [TaxiOrderController::class, 'location_available']);
    Route::get('taxi/location/history', [TaxiOrderController::class, 'location_history']);
    // driver section
    Route::group(['middleware' => ['role:driver']], function () {
        //driver location updating
        Route::post('driver/location/sync', [DriverLocationSyncController::class, 'store']);
        Route::post('taxi/order/asignment/reject', [TaxiDriverOrderController::class, 'driverRejectAssignment']);
        Route::post('taxi/order/asignment/accept', [TaxiDriverOrderController::class, 'driverAcceptAssignment']);
    });

    //Payments
    Route::apiResource('payment/accounts', PaymentAccountController::class);
    Route::apiResource('payouts/request', PayoutController::class)->only('store');
    //Wallet transfer
    Route::get('wallet/my/address', [WalletTransferController::class, 'walletAddress']);
    Route::get('wallet/address/search', [WalletTransferController::class, 'walletAddressSearch']);
    Route::post('wallet/address/transfer', [WalletTransferController::class, 'walletTransfer']);
    //loyalty point
    Route::get('loyalty/point/my', [LoyaltyPointController::class, 'current']);
    Route::get('loyalty/point/my/report', [LoyaltyPointController::class, 'report']);
    Route::post('loyalty/point/my/withdraw', [LoyaltyPointController::class, 'withdraw']);

    //
    Route::group(['middleware' => ['role:manager']], function () {

        Route::post('availability/vendor/{id}', [VendorController::class, 'toggleVendorAvailablity']);

        //separate the my package pricing into get, post, put, delete
        Route::apiResource('/my/package/pricing', VendorPackageTypePricingController::class)->names('my.package.pricing');
        //separate the my services into get, post, put, delete
        Route::apiResource('/my/services', VendorServiceController::class)->names('my.services');
        //separate the my products into get, post, put, delete
        Route::apiResource('/my/products', VendorProductController::class)->names('my.products');
        //separate the my menus into get, post, put, delete
        Route::apiResource('/my/menus', VendorMenuController::class)->names('my.menus');

        //
        Route::get('/my/vendors', [MyVendorController::class, 'index']);
        Route::post('/switch/vendor', [MyVendorController::class, 'switchVendor']);
        Route::get('/my/vendor/sales/report', [VendorReportController::class, 'sales']);
        Route::get('/my/vendor/earnings/report', [VendorReportController::class, 'earnings']);
        Route::get('/my/vendor/report/summary', [VendorReportController::class, 'summary']);

        //document submission
        Route::post('/my/vendor/document/request/submission', [DocumentRequestController::class, 'vendor']);



        // Property Management
        Route::apiResource('my/properties', VendorPropertyController::class);
        Route::post('my/properties/{id}/availability', [VendorPropertyController::class, 'manageAvailability']);
        Route::post('my/properties/{id}/fees', [VendorPropertyController::class, 'storeFee']);
        Route::delete('my/properties/{propertyId}/fees/{feeId}', [VendorPropertyController::class, 'destroyFee']);
        Route::get('my/booking-fee-types', [VendorPropertyController::class, 'feeTypes']);

        // Booking Management
        Route::get('my/bookings', [VendorBookingController::class, 'index']);
        Route::get('my/bookings/statistics', [VendorBookingController::class, 'statistics']);
        Route::get('my/bookings/{id}', [VendorBookingController::class, 'show']);
        Route::post('my/bookings/{id}/approve', [VendorBookingController::class, 'approve']);
        Route::post('my/bookings/{id}/reject', [VendorBookingController::class, 'reject']);
        Route::post('my/bookings/{id}/status', [VendorBookingController::class, 'updateStatus']);

    });

    //Sync driver location for this order.
    Route::post('/orders/{order}/driver/location/sync', [TrackOrderController::class, "syncDriverLocation"]);

    //driver
    Route::group(['middleware' => ['role:driver']], function () {
        Route::post('driver/type/switch', [DriverTypeController::class, 'switchType']);
        Route::post('driver/vehicle/register', [DriverTypeController::class, 'registerVehicle']);
        Route::get('driver/vehicles', [DriverTypeController::class, 'vehicles']);
        Route::post('driver/vehicle/{id}/activate', [DriverTypeController::class, 'activateVehicle']);
        Route::get('driver/subscriptions', [DriverSubscriptionController::class, 'index']);
        Route::post('driver/subscriptions/subscribe', [DriverSubscriptionController::class, 'subscribe']);
        Route::get('driver/subscriptions/state', [DriverSubscriptionController::class, 'state']);
        Route::get('driver/subscriptions/history', [DriverSubscriptionController::class, 'history']);
        // Route::post('driver/order/accept', [DriverTypeController::class, 'activateVehicle']);
        // Route::post('driver/order/reject', [DriverTypeController::class, 'activateVehicle']);
        //document submission
        Route::post('/driver/document/request/submission', [DocumentRequestController::class, 'driver']);
        //summary report
        Route::get('/driver/metrics', [DriverReportController::class, 'metrics']);
        Route::get('/driver/payouts/report', [DriverReportController::class, 'payouts']);
        Route::get('/driver/earnings/report', [DriverReportController::class, 'earnings']);
        //pay outstanding remittance
        Route::post('/driver/remittance/pay', [DriverRemittanceController::class, 'pay']);
    });



    //Booking System - Customer Routes (requires authentication)
    Route::group(['prefix' => 'property/bookings', 'middleware' => ['auth:sanctum']], function () {
        Route::get('/', [BookingOrderController::class, 'index']);
        Route::get('/{id}', [BookingOrderController::class, 'show']);
        Route::post('/', [BookingOrderController::class, 'store']);
        Route::post('/{id}/cancel', [BookingOrderController::class, 'cancel']);
        Route::get('/{id}/cancellation-info', [BookingOrderController::class, 'cancellationInfo']);
        Route::post('/preview', [BookingOrderController::class, 'preview']);
    });
});
