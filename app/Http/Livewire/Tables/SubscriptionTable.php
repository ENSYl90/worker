<?php

namespace App\Http\Livewire\Tables;

use App\Models\Subscription;
use Rappasoft\LaravelLivewireTables\Views\Column;

class SubscriptionTable extends BaseDataTableComponent
{

    public $model = Subscription::class;

    public function query()
    {
        return Subscription::query();
    }

    public function columns():array
    {
        return [
            Column::make(__('ID'),"id")->searchable()->sortable(),
            Column::make(__('Name'),'name')->searchable()->sortable(),
            Column::make(__('Days'),'days')->searchable()->sortable(),
            Column::make(__('Qty'),'qty')->searchable()->sortable(),
            Column::make(__('Amount'),'amount')->searchable()->sortable(),
            Column::make(__('Actions'),'id')->format(function ($value, $column, $row) {
                return view('components.buttons.actions', $data = [
                    "model" => $row,
                ]);
            }),
        ];
    }


}
