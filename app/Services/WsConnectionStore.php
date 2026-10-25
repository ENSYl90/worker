<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

/**
 * Redis-backed registry of active WebSocket connections.
 *
 * Key layout:
 *   ws:connections          – Hash  { socketId => JSON payload }
 *   ws:channels             – Hash  { channel  => JSON array of socketIds }
 *   ws:stats                – Hash  { messages_in, messages_out, auth_failures, total_ever }
 *
 * All keys are prefixed so they never clash with other Redis data.
 * Every write is done through this class to keep the schema in one place.
 */
class WsConnectionStore
{
    private const KEY_CONNECTIONS = 'ws:connections';
    private const KEY_CHANNELS    = 'ws:channels';
    private const KEY_STATS       = 'ws:stats';
    private const TTL_SECONDS     = 300; // safety-net: stale connections expire after 5 min inactivity

    // -----------------------------------------------------------------------
    // Connection lifecycle
    // -----------------------------------------------------------------------

    public function connect(string $socketId, array $meta = []): void
    {
        $payload = array_merge([
            'socket_id'    => $socketId,
            'ip'           => null,
            'user_id'      => null,
            'user_name'    => null,
            'user_role'    => null,
            'channels'     => [],
            'connected_at' => now()->toIso8601String(),
            'last_seen_at' => now()->toIso8601String(),
        ], $meta);

        Redis::hset(self::KEY_CONNECTIONS, $socketId, json_encode($payload));
        Redis::hincrby(self::KEY_STATS, 'total_ever', 1);
        Redis::expire(self::KEY_CONNECTIONS, self::TTL_SECONDS);
    }

    public function disconnect(string $socketId): void
    {
        $raw = Redis::hget(self::KEY_CONNECTIONS, $socketId);
        if ($raw) {
            $conn = json_decode($raw, true);
            foreach ($conn['channels'] ?? [] as $channel) {
                $this->removeFromChannel($socketId, $channel);
            }
        }
        Redis::hdel(self::KEY_CONNECTIONS, $socketId);
    }

    public function touch(string $socketId): void
    {
        $raw = Redis::hget(self::KEY_CONNECTIONS, $socketId);
        if (!$raw) return;
        $conn = json_decode($raw, true);
        $conn['last_seen_at'] = now()->toIso8601String();
        Redis::hset(self::KEY_CONNECTIONS, $socketId, json_encode($conn));
    }

    // -----------------------------------------------------------------------
    // Channel membership
    // -----------------------------------------------------------------------

    public function subscribe(string $socketId, string $channel): void
    {
        // Add channel to connection's channel list
        $raw = Redis::hget(self::KEY_CONNECTIONS, $socketId);
        if ($raw) {
            $conn = json_decode($raw, true);
            if (!in_array($channel, $conn['channels'])) {
                $conn['channels'][] = $channel;
            }
            $conn['last_seen_at'] = now()->toIso8601String();
            Redis::hset(self::KEY_CONNECTIONS, $socketId, json_encode($conn));
        }

        // Add socket to channel's subscriber set
        $subscribers = $this->getChannelSubscribers($channel);
        if (!in_array($socketId, $subscribers)) {
            $subscribers[] = $socketId;
        }
        Redis::hset(self::KEY_CHANNELS, $channel, json_encode($subscribers));
        Redis::expire(self::KEY_CHANNELS, self::TTL_SECONDS);
    }

    public function unsubscribe(string $socketId, string $channel): void
    {
        $this->removeFromChannel($socketId, $channel);

        $raw = Redis::hget(self::KEY_CONNECTIONS, $socketId);
        if ($raw) {
            $conn = json_decode($raw, true);
            $conn['channels'] = array_values(array_filter(
                $conn['channels'],
                fn($c) => $c !== $channel
            ));
            Redis::hset(self::KEY_CONNECTIONS, $socketId, json_encode($conn));
        }
    }

    // -----------------------------------------------------------------------
    // Stats
    // -----------------------------------------------------------------------

    public function incrementStat(string $key, int $by = 1): void
    {
        Redis::hincrby(self::KEY_STATS, $key, $by);
    }

    // -----------------------------------------------------------------------
    // Queries
    // -----------------------------------------------------------------------

    public function allConnections(): array
    {
        $rows = Redis::hgetall(self::KEY_CONNECTIONS);
        return collect($rows)
            ->map(fn($v) => json_decode($v, true))
            ->values()
            ->toArray();
    }

    public function allChannels(): array
    {
        $rows = Redis::hgetall(self::KEY_CHANNELS);
        return collect($rows)
            ->map(fn($subscribers, $channel) => [
                'channel'     => $channel,
                'type'        => $this->channelType($channel),
                'subscribers' => json_decode($subscribers, true),
                'count'       => count(json_decode($subscribers, true)),
            ])
            ->values()
            ->toArray();
    }

    public function getStats(): array
    {
        $raw = Redis::hgetall(self::KEY_STATS);
        $connections = $this->allConnections();

        return [
            'active_connections' => count($connections),
            'total_ever'         => (int) ($raw['total_ever'] ?? 0),
            'messages_in'        => (int) ($raw['messages_in'] ?? 0),
            'messages_out'       => (int) ($raw['messages_out'] ?? 0),
            'auth_failures'      => (int) ($raw['auth_failures'] ?? 0),
            'active_channels'    => Redis::hlen(self::KEY_CHANNELS),
        ];
    }

    public function getConnection(string $socketId): ?array
    {
        $raw = Redis::hget(self::KEY_CONNECTIONS, $socketId);
        return $raw ? json_decode($raw, true) : null;
    }

    // -----------------------------------------------------------------------
    // Maintenance
    // -----------------------------------------------------------------------

    /**
     * Remove connections that have not been seen within $thresholdSeconds.
     * Returns the number of connections pruned.
     */
    public function pruneStale(int $thresholdSeconds = self::TTL_SECONDS): int
    {
        $pruned = 0;
        foreach ($this->allConnections() as $conn) {
            $lastSeen = isset($conn['last_seen_at'])
                ? \Carbon\Carbon::parse($conn['last_seen_at'])
                : now()->subSeconds($thresholdSeconds + 1);

            if ($lastSeen->diffInSeconds(now()) >= $thresholdSeconds) {
                $this->disconnect($conn['socket_id']);
                $pruned++;
            }
        }
        return $pruned;
    }

    public function clearAll(): void
    {
        Redis::del(self::KEY_CONNECTIONS, self::KEY_CHANNELS, self::KEY_STATS);
    }

    // -----------------------------------------------------------------------
    // Internal helpers
    // -----------------------------------------------------------------------

    private function getChannelSubscribers(string $channel): array
    {
        $raw = Redis::hget(self::KEY_CHANNELS, $channel);
        return $raw ? json_decode($raw, true) : [];
    }

    private function removeFromChannel(string $socketId, string $channel): void
    {
        $subscribers = $this->getChannelSubscribers($channel);
        $subscribers = array_values(array_filter($subscribers, fn($s) => $s !== $socketId));
        if (empty($subscribers)) {
            Redis::hdel(self::KEY_CHANNELS, $channel);
        } else {
            Redis::hset(self::KEY_CHANNELS, $channel, json_encode($subscribers));
        }
    }

    private function channelType(string $channel): string
    {
        if (str_starts_with($channel, 'private-')) return 'private';
        if (str_starts_with($channel, 'presence-')) return 'presence';
        return 'public';
    }


}
