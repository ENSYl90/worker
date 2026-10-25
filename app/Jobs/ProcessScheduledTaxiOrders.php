<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\JobHandlerService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessScheduledTaxiOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = Carbon::now();

        // Get taxi orders scheduled for today where pickup time is now or past due
        $orders = Order::currentStatus('scheduled')
            ->whereHas('taxi_order')
            ->whereDate('pickup_date', $now->toDateString())
            ->whereTime('pickup_time', '<=', $now->toTimeString())
            ->limit(20)
            ->get();

        foreach ($orders as $order) {
            $order->setStatus('pending');
            (new JobHandlerService())->uploadTaxiOrderJob($order);
        }
    }
}
