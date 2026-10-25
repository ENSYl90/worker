<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\JobHandlerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchScheduledTaxiOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        //
    }

    public function handle(): void
    {
        $this->order->refresh();

        // Only process if the order is still in scheduled status — it may have been
        // cancelled or modified by the user between booking and pickup time.
        if ($this->order->status !== 'scheduled') {
            return;
        }

        $this->order->setStatus('pending');
        (new JobHandlerService())->uploadTaxiOrderJob($this->order);
    }
}
