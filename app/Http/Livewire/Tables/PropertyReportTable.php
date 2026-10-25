<?php

namespace App\Http\Livewire\Tables;

use App\Models\PropertyReport;
use Rappasoft\LaravelLivewireTables\Views\Column;

class PropertyReportTable extends BaseDataTableComponent
{
    public $model = PropertyReport::class;

    public function query()
    {
        return PropertyReport::query()
            ->with(['user', 'property', 'reason']);
    }

    public function columns(): array
    {
        return [
            $this->indexColumn(),
            Column::make(__('User'), 'user.name')->searchable()->sortable(),
            Column::make(__('Property'), 'property.name')->searchable()->sortable(),
            Column::make(__('Reason'), 'reason.reason')->searchable()->sortable(),
            Column::make(__('Details'), 'details')->format(function ($value, $column, $row) {
                if (strlen($value) > 50) {
                    $value = substr($value, 0, 50);
                }
                return "<p class='text-sm text-gray-600'>" . $value . "</p>";
            })
                ->asHtml(),
            Column::make(__('Status'), 'status')
                ->format(function ($value, $column, $row) {
                    return view('components.table.status', ['model' => $row, "value" => $value]);
                })->sortable(),
            Column::make(__('Date'), 'created_at')->sortable(),
            $this->customActionsColumn(
                $showView = true,
                $showEdit = false,
                $showDelete = true,
                $showToggleActive = false,
            ),
        ];
    }
}
