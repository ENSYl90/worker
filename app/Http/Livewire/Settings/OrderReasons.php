<?php

namespace App\Http\Livewire\Settings;

use App\Models\CancellationReason;

class OrderReasons extends BaseSettingsComponent
{

    public $model = CancellationReason::class;
    //
    public $reason;
    public $type = 'order';
    public $types = [
        [
            "id" => "taxi_order",
            "name" => "Taxi Order",
        ],
        [
            "id" => "order",
            "name" => "Order",
        ],
        [
            "id" => "both",
            "name" => "Both (Taxi Order & Order)",
        ]
    ];




    public function render()
    {
        return view('livewire.settings.order-reasons');
    }



    public function save()
    {

        $this->validate([
            'reason' => 'required',
        ]);

        try {
            $this->isDemo();
            $cancelReason = new CancellationReason();
            $cancelReason->type = $this->type ?? 'order';
            $cancelReason->reason = $this->reason;
            $cancelReason->save();
            //
            $this->reset();
            $this->emit('refreshTable');
            $this->showSuccessAlert(__("Reason saved successfully!"));
        } catch (\Exception $e) {
            $this->showErrorAlert($e->getMessage());
        }
    }

    public function initiateEdit($id)
    {
        $this->selectedModel = CancellationReason::find($id);
        $this->reason = $this->selectedModel->reason;
        $this->type = $this->selectedModel->type;
        $this->showEditModal();
    }


    public function update()
    {

        $this->validate([
            'reason' => 'required',
        ]);

        try {
            $this->isDemo();
            $cancelReason = $this->selectedModel;
            $cancelReason->type = $this->type ?? 'order';
            $cancelReason->reason = $this->reason;
            $cancelReason->save();
            //
            $this->reset();
            $this->emit('refreshTable');
            $this->showSuccessAlert(__("Reason updated successfully!"));
        } catch (\Exception $e) {
            $this->showErrorAlert($e->getMessage());
        }
    }
}
