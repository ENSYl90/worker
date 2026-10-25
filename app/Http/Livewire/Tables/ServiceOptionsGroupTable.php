<?php

namespace App\Http\Livewire\Tables;

use App\Models\ServiceOptionGroup;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\Views\Column;

class ServiceOptionsGroupTable extends OrderingBaseDataTableComponent
{
    public $model = ServiceOptionGroup::class;

    public function query()
    {

        if (Auth::user()->hasRole('admin')) {
            return ServiceOptionGroup::query();
        } else {
            return ServiceOptionGroup::where('vendor_id', Auth::user()->vendor_id);
        }
    }

    public function columns(): array
    {

        $columns = [
            Column::make(__('ID'), 'id')->searchable()->sortable(),
            Column::make(__('Name'), 'name')->searchable()->sortable(),
            Column::make(__('Multiple'))->format(function ($value, $column, $row) {
                return view($actionView ?? 'components.table.multiple', $data = [
                    'model' => $row,
                ]);
            }),
            Column::make(__('Max Options'), 'max_options'),
            Column::make(__('Created At'), 'formatted_date'),
            $this->actionsColumn(),
        ];

        return $columns;
    }
}
