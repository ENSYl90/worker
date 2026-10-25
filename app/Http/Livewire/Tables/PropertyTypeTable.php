<?php

namespace App\Http\Livewire\Tables;

use App\Models\PropertyType;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Illuminate\Database\Eloquent\Builder;

class PropertyTypeTable extends BaseDataTableComponent
{
    public $model = PropertyType::class;

    public function query(): Builder
    {
        return PropertyType::query();
    }

    public function columns(): array
    {
        return [
            $this->indexColumn(),
            $this->xsImageColumn(),
            Column::make(__('Name'), 'name')->searchable()->sortable()->addClass("w-6/12"),
            $this->activeColumn()->sortable(),
            $this->customActionsColumn(
                $showView = false,
                $showEdit = true,
                $showDelete = true,
                $showToggleActive = true
            ),
        ];
    }
}
