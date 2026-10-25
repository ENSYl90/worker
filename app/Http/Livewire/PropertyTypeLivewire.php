<?php

namespace App\Http\Livewire;

use App\Models\PropertyType;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropertyTypeLivewire extends BaseLivewireComponent
{
    public $model = PropertyType::class;

    // Form fields
    public $name;
    public $photo;

    protected $rules = [
        'name' => 'required|string|max:255',
        'photo' => 'nullable|image|max:2048',
    ];

    public function render()
    {
        return view('livewire.property-types');
    }

    public function save()
    {
        // Validate
        $this->validate();

        try {
            $this->isDemo();

            DB::beginTransaction();
            $model = new PropertyType();
            if ($this->selectedModel != null) {
                $model = $this->selectedModel;
            }
            $model->name = $this->name;
            $model->slug = Str::slug($this->name);
            $model->save();

            // Handle icon image upload
            if ($this->photo) {
                $model->clearMediaCollection('default');
                $model->addMedia($this->photo->getRealPath())
                    ->preservingOriginal()
                    ->toMediaCollection('default');
            }

            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Property Type saved successfully!"));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Property Type save failed!"));
        }
    }

    public function initiateEdit($id)
    {
        $this->selectedModel = $this->model::find($id);
        $this->name = $this->selectedModel->name;
        $this->emit('showEditModal');
        parent::showEditModal();
    }
}
