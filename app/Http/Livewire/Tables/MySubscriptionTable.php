<?php

namespace App\Http\Livewire\Tables;


use App\Models\SubscriptionVendor;
use Rappasoft\LaravelLivewireTables\Views\Column;

class MySubscriptionTable extends BaseDataTableComponent
{

    public $model = SubscriptionVendor::class;

    public function query()
    {
        return SubscriptionVendor::with('subscription')->where('vendor_id',\Auth::user()->vendor_id);
    }

    public function columns(): array
    {
        return [
            Column::make(__('ID'),"id")->searchable()->sortable(),
            Column::make(__('Name'),'subscription.name')->searchable()->sortable(),
            Column::make(__('Status'),'status')->searchable()->sortable(),
            Column::make(__('Expires At'),'expires_at'),
            Column::make(__('Expired'),'expired')->format(function ($value, $column, $row) {
                return view('components.table.bool', $data = [
                    "model" => $row,
                    'isTrue' => $value != null,
                ]);
            }),
        ];
    }


}
