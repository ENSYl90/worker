<?php

namespace App\Http\Livewire\Tables\Taxi;

use App\Http\Livewire\Tables\BaseDataTableComponent;
use App\Models\CarModel;
use Rappasoft\LaravelLivewireTables\Views\Column;

class CarModelTable extends BaseDataTableComponent
{

    public $model = CarModel::class;

    public function query()
    {
        return CarModel::with('car_make');
    }

    public function columns():array
    {
        return [
            Column::make(__('ID'),"id")->searchable()->sortable(),
            Column::make(__('Make'),'car_make.name')->searchable(),
            Column::make(__('Name'),'name')->searchable(),
            $this->customActionsColumn(
                $showView = false,
                $showEdit = true,
                $showDelete = false,
                $showToggleActive = false
            )
        ];
    }
}
