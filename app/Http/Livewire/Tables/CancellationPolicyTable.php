<?php

namespace App\Http\Livewire\Tables;

use App\Models\CancellationPolicy;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Illuminate\Database\Eloquent\Builder;

class CancellationPolicyTable extends BaseDataTableComponent
{
    public $model = CancellationPolicy::class;

    public function query(): Builder
    {
        return CancellationPolicy::query();
    }

    public function filters(): array
    {
        return [
            'is_active' => Filter::make(__('Status'))
                ->select([
                    '' => __('All'),
                    '1' => __('Active'),
                    '0' => __('Inactive'),
                ]),
        ];
    }

    public function columns(): array
    {
        return [
            Column::make(__('ID'), 'id')
                ->searchable()
                ->sortable(),

            Column::make(__('Name'), 'name')
                ->searchable()
                ->sortable(),

            Column::make(__('Description'), 'description')
                ->format(function ($value) {
                    return $value ? \Str::limit($value, 50) : '-';
                }),

            Column::make(__('Rules Count'))
                ->format(function ($value, $column, $row) {
                    $rules = [];
                    if ($row->refund_percentage_rules) {
                        if (!is_array($row->refund_percentage_rules)) {
                            $rules = json_decode($row->refund_percentage_rules, true);
                        } else {
                            $rules = $row->refund_percentage_rules ?? [];
                        }
                    }
                    return count($rules);
                }),
            Column::make(__('Properties Count'))
                ->format(function ($value, $column, $row) {
                    return $row->properties()->count();
                }),
            $this->activeColumn()->sortable(),

            Column::make(__('Actions'))
                ->format(function ($value, $column, $row) {
                    return view('components.buttons.crud_actions', [
                        'model' => $row,
                    ]);
                }),
        ];
    }
}
