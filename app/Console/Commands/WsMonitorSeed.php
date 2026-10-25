<?php

namespace App\Console\Commands;

use App\Services\WsConnectionStore;
use Illuminate\Console\Command;

/**
 * Injects fake connection data so the WS Monitor page can be tested locally
 * without a running Reverb server.
 *
 * Usage:
 *   php artisan ws:monitor:seed           # seed 5 fake connections
 *   php artisan ws:monitor:seed --count=20
 *   php artisan ws:monitor:seed --clear   # wipe all monitor data
 */
class WsMonitorSeed extends Command
{
    protected $signature   = 'ws:monitor:seed {--count=5 : Number of fake connections} {--clear : Clear all monitor data instead of seeding}';
    protected $description = 'Seed fake WebSocket connections into the monitor store (dev/testing only)';

    public function handle(WsConnectionStore $store): int
    {
        if ($this->option('clear')) {
            \Illuminate\Support\Facades\Redis::del('ws:connections', 'ws:channels', 'ws:stats');
            $this->info('Monitor data cleared.');
            return self::SUCCESS;
        }

        $count = (int) $this->option('count');

        $roles    = ['driver', 'customer', 'vendor', 'admin'];
        $channels = [
            'private-App.Models.User',
            'driver.new-order',
            'orders.updated',
            'driver.order.updated',
            'drivers.{id}.location.updated',
        ];

        $names = ['Alice', 'Bob', 'Carol', 'Dave', 'Eve', 'Frank', 'Grace', 'Hank'];

        $this->info("Seeding {$count} fake connections…");
        $bar = $this->output->createProgressBar($count);

        for ($i = 1; $i <= $count; $i++) {
            $socketId = sprintf('%d.%d', rand(100000, 999999), rand(100000, 999999));
            $userId   = rand(1, 200);
            $role     = $roles[array_rand($roles)];
            $name     = $names[array_rand($names)] . " #{$userId}";

            $store->connect($socketId, [
                'ip'        => "192.168.1.{$i}",
                'user_id'   => $userId,
                'user_name' => $name,
                'user_role' => $role,
                'connected_at' => now()->subSeconds(rand(10, 7200))->toIso8601String(),
                'last_seen_at' => now()->subSeconds(rand(0, 60))->toIso8601String(),
            ]);

            // Subscribe to 1–3 channels
            $numChannels = rand(1, 3);
            $picked = (array) array_rand($channels, min($numChannels, count($channels)));
            foreach ($picked as $idx) {
                $channel = str_replace('{id}', $userId, $channels[$idx]) . ".{$userId}";
                $store->subscribe($socketId, $channel);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Seed some stats
        $store->incrementStat('messages_in', rand(50, 500));
        $store->incrementStat('messages_out', rand(50, 500));
        $store->incrementStat('auth_failures', rand(0, 5));

        $stats = $store->getStats();
        $this->table(
            ['Metric', 'Value'],
            collect($stats)->map(fn($v, $k) => [$k, $v])->values()->toArray()
        );

        return self::SUCCESS;
    }
}
