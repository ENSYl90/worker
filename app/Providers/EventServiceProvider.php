<?php

namespace App\Providers;

use App\Listeners\OrderStatusEventSubscriber;
use App\Models\AutoAssignment;
use App\Models\Order;
use App\Models\PackageType;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\Product;
use App\Models\Property;
use App\Models\Service;
use App\Models\SubscriptionVendor;
use App\Models\TaxiOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Observers\AutoAssignmentObserver;
use App\Observers\BookingOrderStatusObserver;
use App\Observers\ModelNotificationObserver;
use App\Observers\OrderFeesObserver;
use App\Observers\OrderLoyaltyObserver;
//
use App\Observers\OrderObserver;
use App\Observers\OrderStatusObserver;
use App\Observers\OverdraftOrderObserver;
use App\Observers\OverdraftWalletObserver;
use App\Observers\PackageTypeObserver;
use App\Observers\PayoutObserver;
//
use App\Observers\ProductObserver;
use App\Observers\PropertyObserver;
use App\Observers\ReferralObserver;
use App\Observers\ServiceObserver;
use App\Observers\SubscriptionObserver;
use App\Observers\TaxiDriverObserver;
use App\Observers\TaxiOrderObserver;
use App\Observers\TaxiOrderTripObserver;
use App\Observers\UserObserver;
use App\Observers\VehicleObserver;
use App\Observers\VendorObserver;
use App\Observers\VendorOpenObserver;
use App\Observers\WalletObserver;
use App\Observers\WalletTransactionObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \Laravel\Reverb\Events\ChannelRemoved::class => [
            \App\Listeners\DriverWebsocketDisconnectedListener::class,
            [\App\Listeners\WsMonitorListener::class, 'handleChannelRemoved'],
        ],
        \Laravel\Reverb\Events\MessageReceived::class => [
            [\App\Listeners\WsMonitorListener::class, 'handleMessageReceived'],
        ],
        \Laravel\Reverb\Events\MessageSent::class => [
            [\App\Listeners\WsMonitorListener::class, 'handleMessageSent'],
        ],
        \Laravel\Reverb\Events\ConnectionPruned::class => [
            [\App\Listeners\WsMonitorListener::class, 'handleConnectionPruned'],
        ],
    ];

    protected $subscribe = [
        OrderStatusEventSubscriber::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
        User::observe(UserObserver::class);
        Vendor::observe(VendorObserver::class);
        Vendor::observe(VendorOpenObserver::class);
        Property::observe(PropertyObserver::class);
        Order::observe(OrderObserver::class);
        \Spatie\ModelStatus\Status::observe(OrderStatusObserver::class);
        \Spatie\ModelStatus\Status::observe(BookingOrderStatusObserver::class);
        Order::observe(OrderFeesObserver::class);
        SubscriptionVendor::observe(SubscriptionObserver::class);
        Payout::observe(PayoutObserver::class);

        // Majorly for taxi
        User::observe(TaxiDriverObserver::class);
        Order::observe(TaxiOrderObserver::class);
        TaxiOrder::observe(TaxiOrderTripObserver::class);
        Vehicle::observe(VehicleObserver::class);
        Order::observe(OverdraftOrderObserver::class);

        // Subscription qty checks
        Product::observe(ProductObserver::class);
        Service::observe(ServiceObserver::class);
        PackageType::observe(PackageTypeObserver::class);
        //
        Order::observe(ReferralObserver::class);
        Order::observe(OrderLoyaltyObserver::class);
        Wallet::observe(OverdraftWalletObserver::class);
        Wallet::observe(WalletObserver::class);
        WalletTransaction::observe(WalletTransactionObserver::class);
        // add wallet transaction observer
        AutoAssignment::observe(AutoAssignmentObserver::class);

        // translations observer
        Product::observe(\App\Observers\TranslationObserver::class);
        \App\Models\Category::observe(\App\Observers\TranslationObserver::class);
        \App\Models\Coupon::observe(\App\Observers\TranslationObserver::class);
        \App\Models\Fee::observe(\App\Observers\TranslationObserver::class);
        PackageType::observe(\App\Observers\TranslationObserver::class);
        \App\Models\Menu::observe(\App\Observers\TranslationObserver::class);
        \App\Models\Onboarding::observe(\App\Observers\TranslationObserver::class);
        Service::observe(\App\Observers\TranslationObserver::class);
        \App\Models\Subcategory::observe(\App\Observers\TranslationObserver::class);
        \App\Models\Tag::observe(\App\Observers\TranslationObserver::class);
        \App\Models\VendorType::observe(\App\Observers\TranslationObserver::class);

        // Model notification observers - Global scope approach
        // Register observer once, applies to all configured models
        $notificationObserver = ModelNotificationObserver::class;
        Order::observe($notificationObserver);
        User::observe($notificationObserver);
        Vendor::observe($notificationObserver);
        Payment::observe($notificationObserver);
        SubscriptionVendor::observe($notificationObserver);
        \Spatie\ModelStatus\Status::observe($notificationObserver);
    }
}
