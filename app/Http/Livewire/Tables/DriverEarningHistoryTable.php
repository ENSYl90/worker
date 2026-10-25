<?php

namespace App\Http\Livewire\Tables;

use App\Exports\DriverEarningExport;
use App\Models\EarningReport;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Maatwebsite\Excel\Facades\Excel;

class DriverEarningHistoryTable extends BaseDataTableComponent
{

    public $model = EarningReport::class;
    public array $bulkActions = [];

    public function mount()
    {
        $this->bulkActions = [
            'exportSelected' => __('Export'),
        ];
    }

    public function filters(): array
    {
        return [
            'start_date' => Filter::make(__('Start Date'))
                ->date([
                    'min' => now()->subYear()->format('Y-m-d'), // Optional
                    'max' => now()->format('Y-m-d') // Optional
                ]),
            'end_date' => Filter::make(__('End Date'))
                ->date([
                    'min' => now()->subYear()->format('Y-m-d'), // Optional
                    'max' => now()->format('Y-m-d') // Optional
                ])
        ];
    }


    public function query()
    {
        return EarningReport::with('earnings')->whereHas('earnings', function ($q) {
            if(amI(role: "manager")){
                return $q->whereHas("user",function($q2){
                    return $q2->where('vendor_id',\Auth::user()->vendor_id);
                });
            }else if(!amI(role: "admin")){
                return $q->whereHas("user",function($q2){
                    return $q2->where('creator_id',\Auth::user()->id);
                });
            }
            return $q->whereNotNull('user_id');
        })
            ->when($this->getFilter('start_date'), fn ($query, $sDate) => $query->whereDate('created_at', ">=", $sDate))
            ->when($this->getFilter('end_date'), fn ($query, $eDate) => $query->whereDate('created_at', "<=", $eDate));
    }

    public function columns(): array
    {
        return [
            Column::make(__('ID'), "id")->searchable()->sortable(),
            Column::make(__('Driver'), "earnings.user.name")->searchable()->sortable(),
            Column::make(__('Commission'), "rate")->format(function ($value, $column, $row) {
                return view('components.table.plain', $data = [
                    "model" => $row,
                    "text" => $row->rate != null ? "" . $row->rate . "%": "",
                ]);
            })->searchable()->sortable(),
            Column::make(__('Earning'), "earning")->format(function ($value, $column, $row) {
                return view('components.table.price', $data = [
                    "model" => $row,
                    "value" => $value,
                ]);
            })->searchable()->sortable(),
            Column::make(__('System Commission'), "commission")->format(function ($value, $column, $row) {
                $text = currencyFormat($value);
                return view('components.table.plain', $data = [
                    "model" => $row,
                    "text" => $text,
                ]);
            })->searchable()->sortable(),
            Column::make(__('Balance'), "balance")->format(function ($value, $column, $row) {
                return view('components.table.price', $data = [
                    "model" => $row,
                    "value" => $value,
                ]);
            })->searchable()->sortable(),
            Column::make(__('Order'), 'order.code')->format(function ($value, $column, $row) {
                return view('components.table.order', $data = [
                    "model" => $row->order,
                ]);
            }),
            Column::make(__('Created At'), 'created_at')->format(function ($value, $column, $row) {
                return translated_format_date($value,"d M, Y h:i A");
            })->searchable()->sortable(),
        ];
    }


    public function exportSelected()
    {
        if ($this->selectedRowsQuery->count() > 0) {
            return Excel::download(new DriverEarningExport($this->selectedKeys), 'driver_earning_report.xlsx');
        } else {
            //
        }
    }
}