<?php

namespace App\Traits;

use App\Models\CarMake;
use App\Models\CarModel;
use App\Models\VehicleType;

trait VehicleTrait
{

    public $vehicleTypes;
    public $car_make_id;
    public $car_model_id;
    public $driver_id;
    public $vehicle_type_id;
    public $reg_no;
    public $color;
    public $photos;

    public function updatedDriverType()
    {
        if (empty($this->vehicleTypes)) {
            $this->vehicleTypes = VehicleType::active()->get();
            $this->vehicle_type_id = $this->vehicleTypes->first()->id ?? null;
        }
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
}
