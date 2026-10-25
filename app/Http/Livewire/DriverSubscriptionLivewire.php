<?php

namespace App\Http\Livewire;

use App\Models\DriverSubscription;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DriverSubscriptionLivewire extends BaseLivewireComponent
{
    public $model = DriverSubscription::class;

    public $name;
    public $type = DriverSubscription::TYPE_TIME;
    public $days;
    public $order_limit;
    public $amount;
    public $isActive = true;

    public $types = [];

    public function render()
    {
        $this->loadTypes();
        return view('livewire.driver_subscriptions');
    }

    public function loadTypes()
    {
        $this->types = [
            ['id' => DriverSubscription::TYPE_TIME, 'name' => __('Time Based')],
            ['id' => DriverSubscription::TYPE_ORDERS, 'name' => __('Order Based')],
        ];
    }

    protected function rules()
    {
        return [
            'name' => 'required|string',
            'type' => ['required', Rule::in([DriverSubscription::TYPE_ORDERS, DriverSubscription::TYPE_TIME])],
            'days' => 'nullable|required_if:type,' . DriverSubscription::TYPE_TIME . '|integer|min:1',
            'order_limit' => 'nullable|required_if:type,' . DriverSubscription::TYPE_ORDERS . '|integer|min:1',
            'amount' => 'required|numeric|min:0',
        ];
    }

    public function initiateEdit($id)
    {
        $this->selectedModel = $this->model::find($id);
        $this->name = $this->selectedModel->name;
        $this->type = $this->selectedModel->type;
        $this->days = $this->selectedModel->days;
        $this->order_limit = $this->selectedModel->order_limit;
        $this->amount = $this->selectedModel->amount;
        $this->isActive = $this->selectedModel->is_active;
        $this->emit('showEditModal');
    }

    public function save()
    {
        $this->validate();

        try {
            $this->isDemo();
            DB::beginTransaction();

            $model = new DriverSubscription();
            $this->saveModel($model);

            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->type = DriverSubscription::TYPE_TIME;
            $this->isActive = true;
            $this->showSuccessAlert(__("Driver Subscription") . " " . __('created successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Driver Subscription") . " " . __('creation failed!'));
        }
    }

    public function update()
    {
        $this->validate();

        try {
            $this->isDemo();
            DB::beginTransaction();

            $this->saveModel($this->selectedModel);

            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->type = DriverSubscription::TYPE_TIME;
            $this->isActive = true;
            $this->showSuccessAlert(__("Driver Subscription") . " " . __('updated successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Driver Subscription") . " " . __('update failed!'));
        }
    }

    private function saveModel(DriverSubscription $model)
    {
        $model->name = $this->name;
        $model->type = $this->type;
        $model->days = $this->type === DriverSubscription::TYPE_TIME ? $this->days : null;
        $model->order_limit = $this->type === DriverSubscription::TYPE_ORDERS ? $this->order_limit : null;
        $model->amount = $this->amount;
        $model->is_active = $this->isActive;
        $model->save();
    }
}
