<?php

namespace App\Observers;

use App\Models\DriverSubscriptionHistory;
use App\Models\Order;
use App\Services\JobHandlerService;
use Illuminate\Support\Facades\DB;
use Spatie\ModelStatus\Status;

class OrderStatusObserver
{



    public function creating(Status $statusModel)
    {
        // logger("called here ==> creating");
        // AppLangService::tempLocale();
        //
        // AppLangService::restoreLocale();
    }

    public function created(Status $statusModel)
    {
        // logger("called here ==> created");
        if (isUsingWebsocket()) {
            $modelId = $statusModel->model->id;
            $order = Order::find($modelId);
            (new JobHandlerService())->pushOrderToFCMJob($order);
        }

        $this->handleDriverOrderSubscription($statusModel);
    }

    private function handleDriverOrderSubscription(Status $statusModel)
    {
        if ($statusModel->model_type !== Order::class || !in_array($statusModel->name, ['delivered', 'completed', 'successful'])) {
            return;
        }

        $order = Order::find($statusModel->model_id);

        if (empty($order) || empty($order->driver_id)) {
            return;
        }

        try {
            DB::beginTransaction();

            $subscription = DriverSubscriptionHistory::active()
                ->where('driver_id', $order->driver_id)
                ->where('type', 'orders')
                ->lockForUpdate()
                ->latest()
                ->first();

            if (empty($subscription)) {
                DB::commit();
                return;
            }

            $subscription->remaining_orders = max(($subscription->remaining_orders ?? 0) - 1, 0);
            $subscription->completed_orders = ($subscription->completed_orders ?? 0) + 1;

            if ($subscription->remaining_orders <= 0) {
                $subscription->status = 'expired';
            }

            $subscription->save();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            logger("Driver order subscription update failed", [
                'order_id' => $order->id,
                'driver_id' => $order->driver_id,
                'error' => $ex->getMessage(),
            ]);
        }
    }
}
