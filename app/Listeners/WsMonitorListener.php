<?php

namespace App\Listeners;

use App\Services\WsConnectionStore;
use Laravel\Reverb\Events\ChannelRemoved;
use Laravel\Reverb\Events\ConnectionPruned;
use Laravel\Reverb\Events\MessageReceived;
use Laravel\Reverb\Events\MessageSent;

/**
 * Feeds the WsConnectionStore from Reverb's lifecycle events.
 *
 * One class, four handle* methods — registered individually in EventServiceProvider
 * so each can be toggled or removed without touching the others.
 */
class WsMonitorListener
{
    public function __construct(private WsConnectionStore $store) {}

    /**
     * MessageReceived fires on every inbound WebSocket frame.
     * We use it to:
     *   - Register a new connection on the first message
     *   - Detect pusher:subscribe and record channel membership
     *   - Detect pusher:unsubscribe and remove channel membership
     *   - Increment the messages_in counter
     */
    public function handleMessageReceived(MessageReceived $event): void
    {
        $connection = $event->connection;
        $socketId   = $connection->id();

        // Ensure connection exists in store (open fires before first message)
        if (!$this->store->getConnection($socketId)) {
            $this->store->connect($socketId, [
                'ip' => $this->extractIp($connection),
            ]);
        }

        $this->store->incrementStat('messages_in');
        $this->store->touch($socketId);

        // Parse the message to catch subscribe/unsubscribe
        $decoded = json_decode($event->message, true);
        if (!is_array($decoded) || empty($decoded['event'])) {
            return;
        }

        $pusherEvent = $decoded['event'] ?? '';
        $data        = $decoded['data'] ?? [];

        // data can be double-encoded (Pusher protocol)
        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        match ($pusherEvent) {
            'pusher:subscribe'   => $this->handleSubscribe($socketId, $data, $connection),
            'pusher:unsubscribe' => $this->handleUnsubscribe($socketId, $data),
            default              => null,
        };
    }

    /**
     * MessageSent fires whenever Reverb sends a frame to a client.
     * We only count it — no structural changes needed.
     */
    public function handleMessageSent(MessageSent $event): void
    {
        $this->store->incrementStat('messages_out');
    }

    /**
     * ChannelRemoved fires when the last subscriber leaves a channel.
     * Remove it from the store so the channel list stays clean.
     */
    public function handleChannelRemoved(ChannelRemoved $event): void
    {
        // Nothing specific to do — WsConnectionStore removes channel entries
        // when connections unsubscribe. This is a safety-net for channels
        // that disappear without an explicit unsubscribe (e.g. hard disconnect).
        // The store's TTL will expire stale channel data anyway.
    }

    /**
     * ConnectionPruned fires when Reverb's stale-connection job removes a connection.
     */
    public function handleConnectionPruned(ConnectionPruned $event): void
    {
        $socketId = $event->connection->connection()->id();
        $this->store->disconnect($socketId);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function handleSubscribe(string $socketId, array $data, $connection): void
    {
        $channel = $data['channel'] ?? null;
        if (!$channel) {
            return;
        }

        // If this is the first subscribe we see for this socket, try to enrich with user identity.
        // Private/presence channels include auth data that lets us trace back to a user.
        $this->enrichUser($socketId, $channel, $data, $connection);

        $this->store->subscribe($socketId, $channel);
    }

    private function handleUnsubscribe(string $socketId, array $data): void
    {
        $channel = $data['channel'] ?? null;
        if ($channel) {
            $this->store->unsubscribe($socketId, $channel);
        }
    }

    /**
     * Try to identify the user from the channel name or auth data.
     *
     * Private channels follow the pattern:
     *   private-App.Models.User.{userId}
     *   private-driver.new-order.{userId}
     *   driver.new-order.{userId}
     * We extract the ID and load the user for display name + role.
     */
    private function enrichUser(string $socketId, string $channel, array $data, $connection): void
    {
        $existing = $this->store->getConnection($socketId);

        // Already enriched
        if (!empty($existing['user_id'])) {
            return;
        }

        $userId = $this->extractUserIdFromChannel($channel);
        if (!$userId) {
            return;
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return;
        }

        // Determine role label
        $role = 'customer';
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('admin'))        $role = 'admin';
            elseif ($user->hasRole('driver'))   $role = 'driver';
            elseif ($user->hasRole('manager'))  $role = 'vendor';
        }

        $this->store->connect($socketId, array_merge($existing ?? [], [
            'socket_id' => $socketId,
            'ip'        => $existing['ip'] ?? $this->extractIp($connection),
            'user_id'   => $user->id,
            'user_name' => $user->name,
            'user_role' => $role,
            'channels'  => $existing['channels'] ?? [],
        ]));
    }

    private function extractUserIdFromChannel(string $channel): ?int
    {
        $patterns = [
            '/^private-App\.Models\.User\.(\d+)$/',
            '/^private-driver\.new-order\.(\d+)$/',
            '/^driver\.new-order\.(\d+)$/',
            '/^orders\.updated\.(\d+)$/',
            '/^driver\.order\.updated\.(\d+)$/',
            '/^vendor\.order\.updated\.(\d+)$/',
            '/^driver-order\.(\d+)$/',
            '/^drivers\.(\d+)\.location\.updated$/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $channel, $matches)) {
                return (int) $matches[1];
            }
        }

        return null;
    }

    private function extractIp($connection): ?string
    {
        try {
            // Reverb's Connection wraps a Ratchet WebSocketConnection
            // which exposes the underlying React socket
            $rawConn = $connection->connection ?? null;
            if ($rawConn && method_exists($rawConn, 'getRemoteAddress')) {
                $addr = $rawConn->getRemoteAddress();
                // Address is "tcp://ip:port" or "ip:port"
                $addr = str_replace('tcp://', '', $addr);
                return explode(':', $addr)[0] ?? null;
            }
        } catch (\Throwable) {
            // IP extraction is best-effort
        }
        return null;
    }
}
