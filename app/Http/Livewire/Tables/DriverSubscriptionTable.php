<?php

namespace App\Http\Livewire\Tables;

use App\Models\DriverSubscription;
use Rappasoft\LaravelLivewireTables\Views\Column;

class DriverSubscriptionTable extends BaseDataTableComponent
{
    public $model = DriverSubscription::class;

    public function query()
    {
        return DriverSubscription::query();
    }

    public function columns(): array
    {
        return [
            Column::make(__('ID'), 'id')->searchable()->sortable(),
            Column::make(__('Name'), 'name')->searchable()->sortable(),
            Column::make(__('Type'), 'type')->format(function ($value) {
                return $value === DriverSubscription::TYPE_ORDERS ? __('Order Based') : __('Time Based');
            })->searchable()->sortable(),
            Column::make(__('Days'), 'days')->searchable()->sortable(),
            Column::make(__('Order Limit'), 'order_limit')->searchable()->sortable(),
            Column::make(__('Amount'), 'amount')->searchable()->sortable(),
            Column::make(__('Actions'), 'id')->format(function ($value, $column, $row) {
                return view('components.buttons.actions', [
                    'model' => $row,
                ]);
            }),
        ];
    }
}
