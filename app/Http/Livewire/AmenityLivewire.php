<?php

namespace App\Http\Livewire;

use App\Models\Amenity;
use Exception;
use Illuminate\Support\Facades\DB;

class AmenityLivewire extends BaseLivewireComponent
{
    public $model = Amenity::class;

    // Form fields
    public $name;
    public $photo;

    protected $rules = [
        'name' => 'required|string|max:255',
        'photo' => 'nullable|image|max:2048',

    ];


    public function render()
    {
        return view('livewire.amenities');
    }



    public function save()
    {
        // Validate
        $this->validate();

        try {
            $this->isDemo();

            DB::beginTransaction();
            $model = new Amenity();
            if ($this->selectedModel != null) {
                $model = $this->selectedModel;
            }
            $model->name = $this->name;
            $model->save();
            // Handle icon image upload
            if ($this->photo) {
                $model->addMedia($this->photo->getRealPath())
                    ->preservingOriginal()
                    ->toMediaCollection();
            }



            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Amenity saved successfully!"));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Amenity save failed!"));
        }
    }

    public function initiateEdit($id)
    {
        $this->selectedModel = $this->model::find($id);
        $this->name = $this->selectedModel->name;
        // Note: photo will be shown from database, not set here
        $this->emit('showEditModal');
        parent::showEditModal();
    }



}
