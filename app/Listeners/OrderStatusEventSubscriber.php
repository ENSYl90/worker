<?php

namespace App\Listeners;

use App\Jobs\UserActiveOrdersUpdatedJob;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Services\JobHandlerService;
use Spatie\ModelStatus\Status;
use App\Traits\FirebaseMessagingTrait;


class OrderStatusEventSubscriber
{

    use FirebaseMessagingTrait;

    /** @var Status|null */
    public $oldStatus;

    /** @var Status */
    public $newStatus;

    /** @var \Illuminate\Database\Eloquent\Model */
    public $model;

    public function __construct(?Status $oldStatus, Status $newStatus, Order $model)
    {
        $this->oldStatus = $oldStatus;

        $this->newStatus = $newStatus;

        $this->model = $model;
    }

    /**
     * Handle the event.
     *
     * @param  object  $events
     * @return void
     */
    public function subscribe($events)
    {
        //
        $events->listen(
            'Spatie\ModelStatus\Events\StatusUpdated',
            [OrderStatusEventSubscriber::class, 'handleOrderUpdate']
        );
    }

    public function handleOrderUpdate($event)
    {
        //set the correct dateTime from carbon
        $oldStatus = $event->oldStatus;
        $newStatus = $event->newStatus;
        $oldStatusName = ($oldStatus != null ? $oldStatus->name : "");

        // logger("handleOrderUpdate", [
        //     "Order Code" => $event->model->code,
        //     "newStatus" => $newStatus,
        //     "oldStatus" => $oldStatus,
        //     "oldStatusName" => $oldStatusName,
        // ]);
        $orderStatusModel = $newStatus->model;
        $order = $orderStatusModel;
        $order ??= Order::find($orderStatusModel->id);
        $order ??= Order::find($newStatus->model_id);
        if ($order == null) {
            return;
        }

        if ($oldStatusName != $newStatus->name) {
            //
            $order->updated_at = \Carbon\Carbon::now();
            $order->save();
            //
            if (!empty($order->taxi_order)) {
                $type = 2;
            } else {
                $type = 1;
            }

            //
            (new JobHandlerService())->orderFCMNotificationJob($order, $type, $newStatus->name);
            (new OrderObserver())->updated($order);
            //
            if (!empty($order->user_id)) {
                UserActiveOrdersUpdatedJob::dispatch($order->user_id);
            }
        }

        //refund order
        $order->refundUser();
    }
}
