<?php

namespace App\Observers;

use App\Models\Order;
use Spatie\ModelStatus\Status;

class BookingOrderStatusObserver
{
    /**
     * Handle the Status "created" event.
     *
     * @param  \Spatie\ModelStatus\Status  $status
     * @return void
     */
    public function created(Status $status)
    {
        // Only process if the status belongs to an Order
        if ($status->model_type == 'App\Models\Order') {

            // Check if status is cancelled or failed
            if (in_array($status->name, ['cancelled', 'failed'])) {

                // Retrieve the order
                $order = $status->model;

                // Check if it's a valid order and has a booking order
                if ($order && $order->booking_order) {
                    // Release the blocked dates
                    $order->booking_order->releaseDates();
                }
            }
        }
    }
}
