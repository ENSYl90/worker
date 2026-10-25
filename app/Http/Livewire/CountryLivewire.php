<?php

namespace App\Http\Livewire;

use App\Models\Country;
use Exception;
use Illuminate\Support\Facades\DB;

class CountryLivewire extends BaseLivewireComponent
{

    //
    public $model = Country::class;

    //
    public $name;

    protected $rules = [
        "name" => "required|string",
    ];


    public function render()
    {
        return view('livewire.countries');
    }


    public function showDetailsModal($id)
    {
        parent::showDetailsModal($id);
        $boundaries = $this->selectedModel?->boundaries ?? [];
        // If it's a string, try decoding it
        if (is_string($boundaries)) {
            $decoded = json_decode($boundaries, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $boundaries = $decoded;
            } else {
                $boundaries = [];
            }
        }
        $this->emit('initMapBoundaryDetail', $boundaries);
    }

    public function save()
    {
        //validate
        $this->validate();

        try {

            DB::beginTransaction();
            $model = new Country();
            $model->name = $this->name;
            $model->save();

            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Country") . " " . __('created successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Country") . " " . __('creation failed!'));
        }
    }

    public function initiateEdit($id)
    {
        $this->selectedModel = $this->model::find($id);
        $this->name = $this->selectedModel->name;
        $this->emit('showEditModal');
    }

    public function update()
    {
        //validate
        $this->validate();

        try {

            DB::beginTransaction();
            $model = $this->selectedModel;
            $model->name = $this->name;
            $model->save();

            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Country") . " " . __('updated successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Country") . " " . __('updated failed!'));
        }
    }
}