<?php

namespace App\Http\Livewire;

use App\Models\PropertyReportReason;
use Exception;
use Illuminate\Support\Facades\DB;

class PropertyReportReasonLivewire extends BaseLivewireComponent
{
    public $model = PropertyReportReason::class;

    public $reason;
    public $is_active = true;

    protected $rules = [
        'reason' => 'required|string|max:255',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        return view('livewire.property_report_reasons');
    }

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $model = new PropertyReportReason();
            $model->reason = $this->reason;
            $model->is_active = $this->is_active;
            $model->save();

            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__('Property Report Reason created successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __('Creation failed!'));
        }
    }

    public function initiateEdit($id)
    {
        $this->selectedModel = $this->model::find($id);
        $this->reason = $this->selectedModel->reason;
        $this->is_active = $this->selectedModel->is_active;
        $this->emit('showEditModal');
    }

    public function update()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $model = $this->selectedModel;
            $model->reason = $this->reason;
            $model->is_active = $this->is_active;
            $model->save();

            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__('Property Report Reason updated successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __('Update failed!'));
        }
    }
}
