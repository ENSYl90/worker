<?php

namespace App\Http\Livewire;

use App\Models\BookingFeeType;
use Exception;
use Illuminate\Support\Facades\DB;

class BookingFeeTypeLivewire extends BaseLivewireComponent
{
    public $model = BookingFeeType::class;

    // Form fields
    public $name;
    public $description;
    public $calculation_type = 'fixed';
    public $is_mandatory = false;
    public $is_taxable = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'calculation_type' => 'required|string|in:fixed,percentage,per_night,per_guest',
        'is_mandatory' => 'boolean',
        'is_taxable' => 'boolean',
    ];

    public function render()
    {
        return view('livewire.booking-fee-types');
    }

    public function getCalculationTypesProperty()
    {
        return [
            ['id' => 'fixed', 'name' => __('Fixed Amount')],
            ['id' => 'percentage', 'name' => __('Percentage of Total')],
            ['id' => 'per_night', 'name' => __('Per Night')],
            ['id' => 'per_guest', 'name' => __('Per Guest')],
        ];
    }

    public function save()
    {
        // Validate
        $this->validate();

        try {
            DB::beginTransaction();
            $model = new BookingFeeType();
            if ($this->selectedModel) {
                $model = $this->selectedModel;
            }
            $model->name = $this->name;
            $model->description = $this->description;
            $model->calculation_type = $this->calculation_type;
            $model->is_mandatory = $this->is_mandatory;
            $model->is_taxable = $this->is_taxable;
            $model->save();

            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Booking Fee Type saved successfully!"));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Booking Fee Type save failed!"));
        }
    }

    public function initiateEdit($id)
    {
        $this->selectedModel = $this->model::find($id);
        $this->name = $this->selectedModel->name;
        $this->description = $this->selectedModel->description;
        $this->calculation_type = $this->selectedModel->calculation_type;
        $this->is_mandatory = $this->selectedModel->is_mandatory;
        $this->is_taxable = $this->selectedModel->is_taxable;
        $this->emit('showEditModal');
    }

  
}
