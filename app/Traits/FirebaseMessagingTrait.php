<?php

namespace App\Traits;

use App\Models\Order;
use App\Models\User;
use App\Models\UserToken;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;

use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;

trait FirebaseMessagingTrait
{

    use FirebaseAuthTrait, OrderNotificationStatusMessageTrait, FirebaseNotificationValidateTrait;
    public $tempLocale;

    private function sendPlainFirebaseNotification(
        $topic,
        $title,
        $body,
        $image = null,
    ) {

        // igNore in local
        if (\App::environment('local')) {
            return;
        }

        $topic = $this->firebaseTopic($topic);

        //prevent duplicate
        if ($this->checkDuplicateNotification([$topic, $title, $body, $image])) {
            return;
        }

        //getting firebase messaging
        $messaging = $this->getFirebaseMessaging();
        $notification = Notification::fromArray([
            'title' => $title,
            'body' => $body,
            'image' => $image,
        ]);
        //
        $message = CloudMessage::new()
            ->toTopic($topic)
            ->withNotification($notification) // optional
            ->withData($data ?? []); // optional

        $messaging->validate($message);
        $messaging->send($message);


    }

    //
    private function sendFirebaseNotification(
        $topic,
        $title,
        $body,
        ?array $data = null,
        bool $onlyData = true,
        string $channel_id = "basic_channel",
        bool $noSound = false,
        ?string $image = null,
    ) {

        // igNore in local
        if (\App::environment('local')) {
            return;
        }

        $topic = $this->firebaseTopic($topic);

        //prevent duplicate
        if ($this->checkDuplicateNotification([$topic, $title, $body, $data, $onlyData, $channel_id, $noSound, $image])) {
            return;
        }

        //check if notification has been sent before
        if ($this->validateNotification($topic, $title, $body, $data, $onlyData, $channel_id, $noSound, $image)) {
            return;
        }

        //getting firebase messaging
        $messaging = $this->getFirebaseMessaging();
        $message = CloudMessage::new()
            ->toTopic($topic)
            ->withNotification(Notification::fromArray([
                'title' => $title,
                'body' => $body,
                'image' => $image,
            ]));

        //send with data if provided and not null
        if ($data != null) {
            $message = $message->withData($data ?? []);
        }

        //add android config
        $androidConfig = AndroidConfig::fromArray([
            'priority' => 'high',
            'notification' => [
                'title' => $title,
                'body' => $body,
                'image' => $image,
                'sound' => $noSound ? "default" : "alert",
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'channel_id' => $channel_id,
            ],
        ]);
        $message = $message->withAndroidConfig($androidConfig);

        //add apns config regrding sound
        $apnConfig = ApnsConfig::fromArray([
            'headers' => [
                'apns-priority' => '10',
            ],
            'payload' => [
                'aps' => [
                    'alert' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'sound' => $noSound ? "default" : "alert.aiff",
                ],
            ],
            'fcm_options' => [
                'analytics_label' => 'analytics',
                'image' => $image ?? '',
            ],
        ]);

        $message = $message->withApnsConfig($apnConfig);
        $messaging->validate($message);
        $messaging->send($message);


    }

    private function sendOrderFirebaseNotification(
        $topic,
        $title,
        $body,
        array $data,
        $deviceTokens = null,
        $isNew = false,
    ) {

        // igNore in local
        if (\App::environment('local')) {
            return;
        }

        $topic = $this->firebaseTopic($topic);

        //prevent duplicate
        if ($this->checkDuplicateNotification([$topic, $title, $body, $data, $deviceTokens, $isNew])) {
            return;
        }

        //getting firebase messaging
        $messaging = $this->getFirebaseMessaging();
        $notification = Notification::fromArray([
            'title' => $title,
            'body' => $body,
        ]);
        //
        $message = CloudMessage::new()
            ->toTopic($topic)
            ->withNotification($notification) // optional
            ->withData($data ?? []); // optional

        //START: ADDING NOTIFICATION CONFIG BY DEVICE
        //add android config
        $androidConfig = AndroidConfig::fromArray([
            'priority' => 'high',
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => $isNew ? "alert_new" : "alert",
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ],
        ]);
        $message = $message->withAndroidConfig($androidConfig);

        //add apns config regrding sound
        $apnConfig = ApnsConfig::fromArray([
            'headers' => [
                'apns-priority' => '10',
            ],
            'payload' => [
                'aps' => [
                    'alert' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'sound' => $isNew ? "alert_new.aiff" : "alert.aiff",
                ],
            ],
        ]);

        $message = $message->withApnsConfig($apnConfig);
        //END: ADDING NOTIFICATION CONFIG BY DEVICE

        //if array to tokens is provided
        if ($deviceTokens != null && is_array($deviceTokens) && count($deviceTokens) > 0) {
            $messaging->sendMulticast($message, $deviceTokens);
        } else {
            $messaging->validate($message);
            $messaging->send($message);
        }


    }

    private function sendFirebaseNotificationToTokens(array $tokens, $title, $body, array $data = null)
    {

        // igNore in local
        if (\App::environment('local')) {
            return;
        }

        //prevent duplicate
        if ($this->checkDuplicateNotification([$tokens, $title, $body, $data])) {
            return;
        }

        //check if notification has been sent before
        if ($this->validateTokenNotification($tokens, $title, $body)) {
            return;
        }

        if (!empty($tokens)) {
            //getting firebase messaging
            $messaging = $this->getFirebaseMessaging();
            $message = CloudMessage::new();
            //
            $config = WebPushConfig::fromArray([
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'icon' => setting('websiteLogo', asset('images/logo.png')),
                ],
                'fcm_options' => [
                    'link' => $data[0],
                ],
            ]);
            //
            $message = $message->withWebPushConfig($config);
            $messaging->sendMulticast($message, $tokens);
        }
    }










    //
    public function sendOrderStatusChangeNotification(Order $order, $status = null)
    {

        try {
            // logger("sendOrderStatusChangeNotification called");
            // logger("order notification", [$order->code]);
            $this->loadLocale();
            //order data
            $orderData = [
                'is_order' => "1",
                'order_id' => (string) $order->id,
                'status' => $order->status,

            ];
            //for taxi orders
            if (!empty($order->taxi_order) || empty($order->vendor)) {
                // logger("order type as taxi", [$order->code]);
                $this->sendTaxiOrderStatusChangeNotification($order);
                return;
            }
            //
            $managersId = $order->vendor->managers->pluck('id')->all() ?? [];
            $managersTokens = UserToken::whereIn('user_id', $managersId)->pluck('token')->toArray();

            //notification message
            $notificationTitle = setting('websiteName', env("APP_NAME"));
            $customerNotificationMessage = $this->getCustomerOrderNotificationMessage(
                $status ?? $order->status,
                $order,
            );
            //customer
            $this->sendOrderFirebaseNotification(
                $order->user_id,
                $notificationTitle,
                $customerNotificationMessage,
                $orderData,
                //user tokens
                $order->user->notification_tokens ?? null,
            );
            //vendor
            if (!empty($order->vendor_id)) {
                // logger("send vendor notification", []);
                $vendorNotificationMessage = $this->getVendorOrderNotificationMessage(
                    $status ?? $order->status,
                    $order,
                );
                $vendorTopic = "v_" . $order->vendor_id . "";
                // logger("vendorTopic", [$vendorTopic]);
                $this->sendOrderFirebaseNotification(
                    $vendorTopic,
                    $notificationTitle,
                    $vendorNotificationMessage,
                    $orderData,
                    //vendor manager tokens
                    $managersTokens,
                );
                //vendor web
                $this->sendFirebaseNotificationToTokens(
                    $managersTokens,
                    $notificationTitle,
                    $vendorNotificationMessage,
                    [
                        "" . route('orders') . "?filters[search]=" . $order->code . ""
                    ],
                );
            }
            //driver
            if (in_array($order->status, ["delivered", "cancelled", "failed"]) && !empty($order->driver_id)) {
                $this->sendOrderFirebaseNotification(
                    $order->driver_id,
                    $notificationTitle,
                    $customerNotificationMessage,
                    $orderData,
                    //driver tokens
                    $order->driver->notification_tokens ?? null,
                );
            }


            // logger("About to send notification base order status",[
            //     "status" => $order->status
            // ]);
            //send notifications to admin & city-admin
            //admin
            if (setting("notifyAdmin", 0)) {
                //sending notification to admin accounts
                $adminsIds = User::admin()->pluck('id')->all();
                $adminTokens = UserToken::whereIn('user_id', $adminsIds)->pluck('token')->toArray();
                //
                $this->sendFirebaseNotificationToTokens(
                    $adminTokens,
                    __("Order Notification"),
                    __("Order #") . $order->code . " " . __("with") . " " . $order->vendor->name . " " . __("is now:") . " " . $order->status,
                    [
                        "" . route('orders') . "?filters[search]=" . $order->code . ""
                    ],
                );
            }
            //city-admin
            if (setting("notifyCityAdmin", 0) && !empty($order->vendor->creator_id)) {
                //sending notification to city-admin accounts
                $cityAdminTokens = UserToken::where('user_id', $order->vendor->creator_id)->pluck('token')->toArray();
                //
                $this->sendFirebaseNotificationToTokens(
                    $cityAdminTokens,
                    __("Order Notification"),
                    __("Order #") . $order->code . " " . __("with") . " " . $order->vendor->name . " " . __("is now:") . " " . $order->status,
                    [
                        "" . route('orders') . "?filters[search]=" . $order->code . ""
                    ],
                );
            }
            $this->resetLocale();
        } catch (\Exception $e) {
            logger("sendOrderStatusChangeNotification error", [$e->getMessage(), $e]);
        }
    }

    //
    public function sendTaxiOrderStatusChangeNotification(Order $order)
    {

        $this->loadLocale();
        //order data
        $orderData = [
            'is_order' => "0",
            'order_id' => (string) $order->id,
            'status' => $order->status,

        ];

        $pendingMsg = setting('taxi.msg.pending', __("Searching for driver"));
        $preparingMsg = setting('taxi.msg.preparing', __("Driver assigned to your trip and their way"));
        $readyMsg = setting('taxi.msg.ready', __("Driver has arrived"));
        $enrouteMsg = setting('taxi.msg.enroute', __("Trip started"));
        $completedMsg = setting('taxi.msg.completed', __("Trip completed"));
        $cancelledMsg = setting('taxi.msg.cancelled', __("Trip was cancelled"));
        $failedMsg = setting('taxi.msg.failed', __("Trip failed"));
        $notificationTitle = setting('websiteName', env("APP_NAME"));

        //'pending','preparing','ready','enroute','delivered','failed','cancelled'
        if ($order->status == "pending") {
            $this->sendOrderFirebaseNotification($order->user_id, $notificationTitle, $pendingMsg, $orderData);
        } else if ($order->status == "preparing") {
            $this->sendOrderFirebaseNotification($order->user_id, $notificationTitle, $preparingMsg, $orderData);
        } else if ($order->status == "ready") {
            $this->sendOrderFirebaseNotification($order->user_id, $notificationTitle, $readyMsg, $orderData);
        } else if ($order->status == "enroute") {

            //user
            $this->sendOrderFirebaseNotification($order->user_id, $notificationTitle, $enrouteMsg, $orderData);
        } else if ($order->status == "delivered") {


            //user/customer
            $this->sendOrderFirebaseNotification(
                $order->user_id,
                $notificationTitle,
                $completedMsg,
                $orderData,
            );

            //user/customer overdraft
            $hasOverdraft = $order->has_over_draft;
            if ($hasOverdraft) {
                /**
                 * :amt - total
                 * :bal - outstanding
                 * :pai - already paid
                 */
                $amt = currencyFormat($order->outstanding_balance->amount ?? "");
                $bal = currencyFormat($order->outstanding_balance->balance ?? "");
                $pai = currencyFormat($order->outstanding_balance->paid ?? "");
                //
                if ($order->payment_method->slug == "cash") {
                    $customerOverDraftMsg = setting('taxi.msg.cash_overdraft_completed', (__("Pay driver") . ":amt"));
                } else {
                    $message = __("Trip total") . " :amt," . __("but you have paid") . " :pai ";
                    $message .= __("the balance of") . " :bal " . __("will be deduted from your account wallet");
                    $customerOverDraftMsg = setting('taxi.msg.overdraft_completed', $message);
                }
                //replce the values
                $customerOverDraftMsg = str_replace(":amt", $amt, $customerOverDraftMsg);
                $customerOverDraftMsg = str_replace(":bal", $bal, $customerOverDraftMsg);
                $customerOverDraftMsg = str_replace(":pai", $pai, $customerOverDraftMsg);
                //
                $this->sendOrderFirebaseNotification(
                    $order->user_id,
                    $notificationTitle,
                    $customerOverDraftMsg,
                    $orderData,
                );
            }

            //driver
            if (!empty($order->driver_id)) {
                $this->sendOrderFirebaseNotification(
                    $order->driver_id,
                    $notificationTitle,
                    $completedMsg,
                    $orderData
                );
            }
        } else if ($order->status == "failed") {
            $this->sendOrderFirebaseNotification($order->user_id, $notificationTitle, $failedMsg, $orderData);
        } else if ($order->status == "cancelled") {
            $this->sendOrderFirebaseNotification($order->user_id, $notificationTitle, $cancelledMsg, $orderData);
            if ($order->driver_id != null) {
                $this->sendOrderFirebaseNotification($order->driver_id, $notificationTitle, $cancelledMsg, $orderData);
            }
        } else if (!empty($order->status)) {
            $notiMsg = __("Trip #") . $order->code . " " . __("has been") . " " . __($order->status) . "";
            $this->sendOrderFirebaseNotification($order->user_id, $notificationTitle, $notiMsg, $orderData);
        }


        //send notifications to admin & city-admin
        //admin
        if (setting("notifyAdmin", 0)) {
            //sending notification to admin accounts
            $adminsIds = User::admin()->pluck('id')->all();
            $adminTokens = UserToken::whereIn('user_id', $adminsIds)->pluck('token')->toArray();
            //
            $notiMsg = __("Trip #") . $order->code . " " . __("by") . " " . $order->user->name . " " . __("is now:") . " " . $order->status;
            $this->sendFirebaseNotificationToTokens(
                $adminTokens,
                __("Trip Notification"),
                $notiMsg,
                [route('orders')]
            );
        }
        $this->resetLocale();
    }


    public function sendOrderNotificationToDriver(Order $order, $status = null)
    {

        $status ??= $order->status;
        //order data
        $orderData = [
            'is_order' => "1",
            'order_id' => (string) $order->id,
            'status' => $status,

        ];

        //aviod send order details notification data when order is taxi
        if (!empty($order->taxi_order)) {
            $orderData["is_order"] = "0";
        }

        //
        $this->loadLocale();
        if ($status != "cancelled") {
            $this->sendOrderFirebaseNotification(
                $order->driver_id,
                __("Order Update"),
                __("Order #") . $order->code . __(" has been assigned to you"),
                $orderData
            );
        }
        if ($status == "cancelled" && $order->driver_id != null) {
            $cancelledMsg = setting('taxi.msg.cancelled', __("Trip was cancelled"));
            $this->sendOrderFirebaseNotification(
                $order->driver_id,
                __("Order Update"),
                __("Trip #") . $order->code . $cancelledMsg,
                $orderData
            );
        }
        $this->resetLocale();
    }



    //LOCALE CONFIG
    public function loadLocale()
    {
        $this->tempLocale = setting('localeCode', 'en');
        \App::setLocale($this->tempLocale);
    }
    public function resetLocale()
    {
        \App::setLocale($this->tempLocale);
    }

    private function firebaseTopic($topic): string
    {
        $topic = (string) $topic;
        $prefix = (string) setting('firestoreRelatedPrefix', '');

        if (empty($prefix) || str_starts_with($topic, $prefix)) {
            return $topic;
        }

        return $prefix . $topic;
    }

    private function checkDuplicateNotification(array $args)
    {
        try {
            $key = "fcm_sent_" . md5(json_encode($args));
            if (\Cache::has($key)) {
                return true;
            }
            \Cache::put($key, true, now()->addSeconds(10));
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
