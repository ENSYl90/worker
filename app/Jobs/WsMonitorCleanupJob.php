<?php

namespace App\Jobs;

use App\Services\WsConnectionStore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class WsMonitorCleanupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public int $delayMinutes) {}

    public function handle(): void
    {
        // Stop if the admin has turned off auto-cleanup
        if (!Redis::get('ws:monitor:cleanup_running')) {
            return;
        }

        app(WsConnectionStore::class)->pruneStale();
        app(WsConnectionStore::class)->clearAll();

        // Re-dispatch itself with the same delay to keep the loop going
        static::dispatch($this->delayMinutes)
            ->onQueue('default')
            ->delay(now()->addMinutes($this->delayMinutes));
    }
}
