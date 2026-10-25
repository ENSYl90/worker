<?php

namespace App\Http\Livewire;

use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EditOrderLivewire extends BaseLivewireComponent
{
    public $model = Order::class;
    public $deliveryBoyId;
    public $status;
    public $paymentStatus;
    public $note;

    protected $listeners = [
        'deliveryBoyIdUpdated' => 'autocompleteDriverSelected',
    ];

    public function mount($code)
    {
        $this->selectedModel = Order::whereCode($code)->first();

        if (!$this->selectedModel) {
            return redirect()->route('orders')->with('error', __('Order not found'));
        }

        // Prevent non-admin from editing unpaid orders
        $isAdmin = Auth::user()->hasRole("admin");
        if (!$isAdmin) {
            $isPharmacyOrder = $this->selectedModel->vendor->vendor_type->slug == "pharmacy";
            if (!$isPharmacyOrder) {
                $paymentMethodSlug = $this->selectedModel->payment_method->slug;
                $isUnpaidOrder = $this->selectedModel->payment_status != "successful" && !in_array($paymentMethodSlug, ["cash", "wallet"]);
                $canViewUnpaid = Auth::user()->can('handle-unpaid-order');
                if ($isUnpaidOrder && !$canViewUnpaid) {
                    return redirect()->route('orders')->with('error', __("Unpaid Order can't be edited. Please wait until payment is confirmed"));
                }
            }
        }

        // Load current values
        $this->deliveryBoyId = $this->selectedModel->driver_id;
        $this->status = $this->selectedModel->status;
        $this->paymentStatus = $this->selectedModel->payment_status;
        $this->note = $this->selectedModel->note;

        if ($this->deliveryBoyId != null) {
            $this->emit('deliveryBoyId_Loaded', $this->deliveryBoyId);
        }
    }

    public function render()
    {
        return view('livewire.edit-order');
    }

    public function autocompleteDriverSelected($value)
    {
        try {
            $this->deliveryBoyId = $value['value'];
        } catch (Exception $ex) {
            logger("Error", [$ex]);
        }
    }

    public function rejectOrder()
    {
        try {
            DB::beginTransaction();
            $this->status = "cancelled";
            $this->selectedModel->status = "cancelled";
            $this->selectedModel->reason = $this->note ?? __("Order rejected");
            $this->selectedModel->save();

            $mStatus = $this->selectedModel->convertStatusName($this->status);
            $this->selectedModel->setStatus($mStatus);

            DB::commit();

            $this->showSuccessAlert(__("Order") . " " . __('rejected successfully!'));

            return redirect()->route('orders')
                ->with('success', __("Order") . " " . __('rejected successfully!'));
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Order") . " " . __('rejection failed!'));
        }
    }

    public function acceptOrder()
    {
        try {
            DB::beginTransaction();
            $this->status = "preparing";
            $this->selectedModel->status = "preparing";
            $this->selectedModel->save();

            $mStatus = $this->selectedModel->convertStatusName($this->status);
            $this->selectedModel->setStatus($mStatus);

            DB::commit();

            $this->showSuccessAlert(__("Order") . " " . __('accepted successfully!'));

            return redirect()->route('orders')
                ->with('success', __("Order") . " " . __('accepted successfully!'));
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Order") . " " . __('acceptance failed!'));
        }
    }

    public function update()
    {
        try {
            DB::beginTransaction();

            $this->selectedModel->driver_id = $this->deliveryBoyId ?? null;
            $this->selectedModel->payment_status = $this->paymentStatus;
            $this->selectedModel->reason = $this->note;
            $this->selectedModel->save();

            $mStatus = $this->selectedModel->convertStatusName($this->status);
            $this->selectedModel->setStatus($mStatus);

            // If taxi order manual driver assign
            if ($this->selectedModel->driver_id != null && $this->selectedModel->taxi_order != null && $this->selectedModel->status == "pending") {
                $mStatus = "preparing";
                $this->selectedModel->setStatus($mStatus);
            }

            DB::commit();

            $this->showSuccessAlert(__("Order") . " " . __('updated successfully!'));

            // Redirect back to orders list
            return redirect()->route('orders')
                ->with('success', __("Order") . " " . __('updated successfully!'));
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Order") . " " . __('updated failed!'));
        }
    }



    public function getOrderStatusProperty()
    {
        $order = $this->selectedModel;
        if ($order == null) {
            return [];
        }
        //for pickup order
        $isPickup = $order->delivery_address_id == null && $order->taxi_order == null && ($order->stops->empty() ?? true);
        if ($isPickup) {
            $options = ['scheduled', 'pending', 'preparing', 'ready',   'completed', 'failed', 'cancelled'];
        } else {
            $options =  ['scheduled', 'pending', 'preparing', 'ready', 'enroute', 'delivered', 'failed', 'cancelled'];
        }

        //remove schedule if order pickup_date == null
        if ($order->pickup_date == null) {
            unset($options[0]);
            $options = array_values($options);
        }


        $statuses = [];
        foreach ($options as $value) {
            $statuses[] = [
                "id" => $value,
                "name" => __($value),
            ];
        }
        return $statuses;
    }

    public function getOrderPaymentStatusProperty()
    {
        $options = ['pending', 'request', 'review', 'failed', 'cancelled', 'successful'];
        $statuses = [];
        foreach ($options as $value) {
            $statuses[] = [
                "id" => $value,
                "name" => __($value),
            ];
        }
        return $statuses;
    }
}
