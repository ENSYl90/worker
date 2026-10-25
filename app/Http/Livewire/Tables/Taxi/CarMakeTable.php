<?php

namespace App\Http\Livewire\Tables\Taxi;

use App\Http\Livewire\Tables\BaseDataTableComponent;
use App\Models\CarMake;
use Rappasoft\LaravelLivewireTables\Views\Column;

class CarMakeTable extends BaseDataTableComponent
{

    public $model = CarMake::class;
    public function query()
    {
        return CarMake::query();
    }

    public function columns(): array
    {
        return [
            Column::make(__('ID'), "id")->searchable()->sortable(),
            Column::make(__('Name'), 'name')->searchable(),
            $this->customActionsColumn(
                $showView = false,
                $showEdit = true,
                $showDelete = false,
                $showToggleActive = false
            )
        ];
    }
}
