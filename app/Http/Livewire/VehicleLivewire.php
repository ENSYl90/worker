<?php

namespace App\Http\Livewire;

use App\Models\CarMake;
use App\Models\CarModel;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Support\Facades\DB;
use Exception;

class VehicleLivewire extends BaseLivewireComponent
{

    public $model = Vehicle::class;
    public $car_make_id;
    public $car_model_id;
    public $driver_id;
    public $vehicle_type_id;
    public $reg_no;
    public $color;
    public $is_active;
    public $photos;

    protected function rules()
    {
        $rules = [
            'car_model_id' => 'required|exists:car_models,id',
            'driver_id' => 'required|exists:users,id',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'color' => 'required|string|min:2|max:50',
        ];

        // Dynamic reg_no validation based on edit mode
        if (!$this->showCreate) {
            $vehicleId = $this->selectedModel->id;
            $rules['reg_no'] = 'required|string|min:3|max:20|unique:vehicles,reg_no,' . $vehicleId;
        } else {
            $rules['reg_no'] = 'required|string|min:3|max:20|unique:vehicles,reg_no';
        }

        return $rules;
    }

    // Custom validation messages
    protected function messages()
    {
        return [
            'car_model_id.required' => __('Please select a vehicle model.'),
            'car_model_id.exists' => __('The selected vehicle model is invalid.'),

            'driver_id.required' => __('Please assign a driver to this vehicle.'),
            'driver_id.exists' => __('The selected driver does not exist.'),

            'vehicle_type_id.required' => __('Vehicle type is required.'),
            'vehicle_type_id.exists' => __('The selected vehicle type is invalid.'),

            'reg_no.required' => __('Registration number is required.'),
            'reg_no.unique' => __('This registration number is already taken.'),
            'reg_no.min' => __('Registration number must be at least 3 characters.'),
            'reg_no.max' => __('Registration number cannot exceed 20 characters.'),

            'color.required' => __('Vehicle color is required.'),
            'color.min' => __('Color must be at least 2 characters.'),
            'color.max' => __('Color cannot exceed 50 characters.'),
        ];
    }

    // Custom attribute names for validation errors
    protected function validationAttributes()
    {
        return [
            'car_model_id' => __('vehicle model'),
            'driver_id' => __('driver'),
            'vehicle_type_id' => __('vehicle type'),
            'reg_no' => __('registration number'),
            'color' => __('vehicle color'),
        ];
    }

    public function render()
    {
        return view('livewire.taxi.vehicles');
    }


    public function autocompleteDriverSelected($driver)
    {
        try {
            $this->driver_id = $driver["id"];
        } catch (\Exception $ex) {
            logger("Error", [$ex]);
        }
    }

    public function photoSelected($photos)
    {
        $this->photos = $photos;
    }

    public function save()
    {

        //
        $this->vehicle_type_id = $this->vehicle_type_id ??  $this->vehicleTypes->first()->id;
        //validate
        $this->validate();

        try {
            DB::beginTransaction();
            $model = new Vehicle();
            $model->car_model_id = $this->car_model_id;
            $model->driver_id = $this->driver_id;
            $model->vehicle_type_id = $this->vehicle_type_id ?? $this->vehicleTypes->first()->id;
            $model->reg_no = $this->reg_no;
            $model->color = $this->color;
            $model->is_active = $this->is_active;
            $model->save();

            if ($this->photos) {

                $model->clearMediaCollection();
                foreach ($this->photos as $photo) {
                    $model->addMedia($photo)->toMediaCollection();
                }
                $this->photos = null;
            }
            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Vehicle") . " " . __('created successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            logger("error", [$error]);
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Vehicle") . " " . __('creation failed!'));
        }
    }


    public function initiateEdit($id)
    {
        $this->reset();
        $this->selectedModel = $this->model::find($id);
        $this->car_model_id = $this->selectedModel->car_model_id;
        $this->car_make_id = $this->selectedModel->car_model->car_make_id;
        $this->driver_id = $this->selectedModel->driver_id;
        $this->vehicle_type_id = $this->selectedModel->vehicle_type_id;
        $this->reg_no = $this->selectedModel->reg_no;
        $this->color = $this->selectedModel->color;
        $this->is_active = $this->selectedModel->is_active;

        //

        $this->emit('preselectedDeliveryBoyEmit', \Str::ucfirst($this->selectedModel->driver->name ?? ''));
        $this->emit('preselectedCarMakeEmit', \Str::ucfirst($this->selectedModel->car_model->car_make->name ?? ''));
        $this->emit('preselectedCarModelEmit', \Str::ucfirst($this->selectedModel->car_model->name ?? ''));
        $this->emit('vehiclePreviewsListener', $this->selectedModel->photos ?? []);
        $this->emit('showEditModal');
    }

    public function update()
    {
        //validate
        $this->validate();

        try {
            $this->isDemo();
            DB::beginTransaction();
            $this->selectedModel->car_model_id = $this->car_model_id;
            $this->selectedModel->driver_id = $this->driver_id;
            $this->selectedModel->vehicle_type_id = $this->vehicle_type_id;
            $this->selectedModel->reg_no = $this->reg_no;
            $this->selectedModel->color = $this->color;
            $this->selectedModel->is_active = $this->is_active;
            $this->selectedModel->save();

            if ($this->photos) {

                $this->selectedModel->clearMediaCollection();
                foreach ($this->photos as $photo) {
                    $this->selectedModel->addMedia($photo)->toMediaCollection();
                }
                $this->photos = null;
            }
            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Vehicle") . " " . __('updated successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Vehicle") . " " . __('update failed!'));
        }
    }

    public function verifyVehicle()
    {
        try {
            DB::beginTransaction();
            $this->selectedModel->is_active = true;
            $this->selectedModel->verified = true;
            $this->selectedModel->save();

            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Vehicle") . " " . __('updated successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Vehicle") . " " . __('update failed!'));
        }
    }





    //misc.
    public function getVehicleTypesProperty()
    {
        return VehicleType::active()->select("id", "name")->get();
    }

    public function getCarMakesProperty()
    {
        return CarMake::select("id", "name")->get();
    }

    public function updatedCarMakeId($value)
    {
        $this->car_model_id = null;
    }
    public function getCarModelsProperty()
    {
        return CarModel::select("id", "name")->where('car_make_id', $this->car_make_id)->get();
    }

    public function getVehicleColorsProperty()
    {
        return [
            'White',
            'Black',
            'Silver',
            'Gray',
            'Red',
            'Blue',
            'Green',
            'Yellow',
            'Orange',
            'Brown',
            'Purple',
            'Pink',
            'Gold',
            'Beige',
            'Maroon',
            'Navy Blue',
            'Dark Green',
            'Light Blue',
            'Cream',
            'Bronze'
        ];
    }
}