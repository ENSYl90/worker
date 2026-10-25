<?php

namespace App\Http\Livewire\Tables;

use App\Models\Amenity;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Illuminate\Database\Eloquent\Builder;

class AmenityTable extends BaseDataTableComponent
{
    public $model = Amenity::class;

    public function query(): Builder
    {
        return Amenity::query();
    }



    public function columns(): array
    {
        return [
            $this->indexColumn(),
            $this->xsImageColumn(),

            Column::make(__('Name'), 'name')
                ->searchable()
                ->sortable(),

            Column::make(__('Properties Count'))
                ->format(function ($value, $column, $row) {
                    return $row->properties()->count();
                }),

            Column::make(__('Actions'))
                ->format(function ($value, $column, $row) {
                    return view('components.buttons.actions', [
                        'model' => $row,
                    ]);
                }),
        ];
    }
}
