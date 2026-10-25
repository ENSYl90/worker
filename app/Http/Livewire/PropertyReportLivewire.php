<?php

namespace App\Http\Livewire;

use App\Models\PropertyReport;
use Exception;
use Illuminate\Support\Facades\DB;

class PropertyReportLivewire extends BaseLivewireComponent
{
    public $model = PropertyReport::class;

    public function render()
    {
        return view('livewire.property_reports');
    }


    public function resolve()
    {
        try {
            DB::beginTransaction();
            $this->selectedModel->status = 'resolved';
            $this->selectedModel->save();
            DB::commit();
            $this->dismissModal();
            $this->showSuccessAlert(__('Report resolved successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __('Operation failed!'));
        }
    }

    public function reject()
    {
        try {
            DB::beginTransaction();
            $this->selectedModel->status = 'rejected';
            $this->selectedModel->save();
            DB::commit();

            $this->dismissModal();
            $this->showSuccessAlert(__('Report rejected successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __('Operation failed!'));
        }
    }
}
