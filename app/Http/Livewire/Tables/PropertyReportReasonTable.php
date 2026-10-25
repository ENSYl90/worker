<?php

namespace App\Http\Livewire\Tables;

use App\Models\PropertyReportReason;
use Rappasoft\LaravelLivewireTables\Views\Column;

class PropertyReportReasonTable extends BaseDataTableComponent
{
    public $model = PropertyReportReason::class;

    public function query()
    {
        return PropertyReportReason::query()
            ->withCount(['propertyReport']);
    }

    public function columns(): array
    {
        return [
            $this->indexColumn(),
            Column::make(__('Reason'), 'reason')->searchable()->sortable(),
            Column::make(__('Reports'), 'propertyReportCount')->sortable(),
            $this->activeColumn(),
            Column::make(__('Created At'), 'created_at')->sortable(),
            $this->actionsColumn(),
        ];
    }
}
