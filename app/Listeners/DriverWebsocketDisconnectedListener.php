<?php

namespace App\Listeners;

use App\Events\DriverSetOfflineEvent;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Laravel\Reverb\Events\ChannelRemoved;
use App\Traits\FirebaseAuthTrait;

class DriverWebsocketDisconnectedListener
{
    use FirebaseAuthTrait;

    // Matches the persistent channels a driver stays subscribed to for their entire session:
    //   private-App.Models.User.{id}       — main private user channel
    //   private-driver.new-order.{id}      — new order alerts (prefixed form)
    //   driver.new-order.{id}              — new order alerts (unprefixed form)
    // Order-specific channels (driver.order.updated.{orderId}) are intentionally excluded
    // because they are transient and don't indicate a full session disconnect.
    private const DRIVER_CHANNEL_PATTERNS = [
        '/^private-App\.Models\.User\.(\d+)$/',
        '/^private-driver\.new-order\.(\d+)$/',
        '/^driver\.new-order\.(\d+)$/',
    ];

    public function handle(ChannelRemoved $event): void
    {
        $channelName = $event->channel->name();

        $userId = null;
        foreach (self::DRIVER_CHANNEL_PATTERNS as $pattern) {
            if (preg_match($pattern, $channelName, $matches)) {
                $userId = (int) $matches[1];
                break;
            }
        }

        if (! $userId) {
            return;
        }

        $driver = User::where('id', $userId)
            ->where('is_online', true)
            ->role('driver')
            ->first();

        if (! $driver) {
            return;
        }

        $driver->is_online = false;
        $driver->save();

        // Broadcast offline status via WebSocket so the app can react
        event(new DriverSetOfflineEvent($driver));

        // Send FCM push notification so the driver is informed even if the app is backgrounded
        $this->sendOfflinePushNotification($driver);

        Log::info('Driver set offline on WebSocket disconnection', ['driver_id' => $driver->id]);
    }

    private function sendOfflinePushNotification(User $driver): void
    {
        if (\App::environment('local')) {
            return;
        }

        $tokens = UserToken::where('user_id', $driver->id)->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        try {
            $messaging = $this->getFirebaseMessaging();

            $message = CloudMessage::new()
                ->withNotification(Notification::fromArray([
                    'title' => __('You are now offline'),
                    'body'  => __('Your connection was lost. You have been set to offline.'),
                ]))
                ->withData([
                    'type'      => 'driver_offline',
                    'is_online' => 'false',
                ]);
            $messaging->sendMulticast($message, $tokens);
        } catch (\Throwable $e) {
            Log::warning('Failed to send driver offline FCM notification', [
                'driver_id' => $driver->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
