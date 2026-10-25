<?php

namespace App\Http\Livewire\Tables;

use App\Models\BookingFeeType;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Illuminate\Database\Eloquent\Builder;

class BookingFeeTypeTable extends BaseDataTableComponent
{
    public $model = BookingFeeType::class;

    public function query(): Builder
    {
        return BookingFeeType::query();
    }

    public function filters(): array
    {
        return [
            'calculation_type' => Filter::make(__('Calculation Type'))
                ->select([
                    '' => __('All'),
                    'fixed' => __('Fixed Amount'),
                    'percentage' => __('Percentage'),
                    'per_night' => __('Per Night'),
                    'per_guest' => __('Per Guest'),
                ]),
            'is_mandatory' => Filter::make(__('Mandatory'))
                ->select([
                    '' => __('All'),
                    '1' => __('Mandatory'),
                    '0' => __('Optional'),
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

            Column::make(__('Calculation Type'), 'calculation_type')
                ->format(function ($value) {
                    $labels = [
                        'fixed' => __('Fixed Amount'),
                        'percentage' => __('Percentage'),
                        'per_night' => __('Per Night'),
                        'per_guest' => __('Per Guest'),
                    ];
                    return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-primary-200 text-primary-800">' . ($labels[$value] ?? $value) . '</span>';
                })
                ->asHtml()
                ->sortable(),

            Column::make(__('Mandatory'), 'is_mandatory')
                ->format(function ($value) {
                    if ($value) {
                        return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-300 text-amber-800">' . __('Yes') . '</span>';
                    }
                    return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">' . __('No') . '</span>';
                })
                ->asHtml()
                ->sortable(),

            Column::make(__('Taxable'), 'is_taxable')
                ->format(function ($value) {
                    if ($value) {
                        return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">' . __('Yes') . '</span>';
                    }
                    return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">' . __('No') . '</span>';
                })
                ->asHtml()
                ->sortable(),

            Column::make(__('Properties Using'))
                ->format(function ($value, $column, $row) {
                    return $row->propertyFees()->count();
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
