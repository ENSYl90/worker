<?php

namespace App\Http\Livewire\Tables\Reports;

use App\Exports\ProductReportExport;
use App\Models\Property;
use App\Models\Order;
use Maatwebsite\Excel\Facades\Excel;
use Rappasoft\LaravelLivewireTables\Views\Column;

class PropertyReportTable extends BaseReportTable
{

    public $model = Property::class;

    public function query()
    {
        return $this->model::mine()->withCount([
            'bookingOrders' => function ($query) {
                $query->when($this->getFilter('start_date'), fn($query, $sDate) => $query->whereDate('created_at', ">=", $sDate))
                    ->when($this->getFilter('end_date'), fn($query, $eDate) => $query->whereDate('created_at', "<=", $eDate))->groupBy('property_id');
            }
        ]);
    }

    public function columns(): array
    {
        return [
            Column::make(__('ID'), 'id'),
            Column::make(__('Image'))->format(function ($value, $column, $row) {
                return view('components.table.image_sm', $data = [
                    "model" => $row
                ]);
            }),
            Column::make(__('Name'), 'name')->searchable()->sortable(),
            Column::make(__('Host'), "vendor.name")->searchable()->sortable(),
            //current base_price
            Column::make(__('Base Price'), 'base_price')->format(function ($value, $column, $row) {
                return view('components.table.price', $data = [
                    "model" => $row,
                    "value" => $value
                ]);
            })->sortable(),
            Column::make(__('Bookings Count'), 'booking_orders_count')->format(function ($value, $column, $row) {
                return view('components.table.count', $data = [
                    "model" => $row,
                    "value" => $value
                ]);
            })->sortable(),
            Column::make(__('Revenue'), 'id')->format(function ($value, $column, $row) {
                $total = Order::whereHas('booking_order', function ($query) use ($row) {
                    $query->where('property_id', $row->id);
                })->currentStatus(['delivered', 'completed'])->sum('total');
                return view('components.table.price', $data = [
                    "model" => $row,
                    "value" => $total
                ]);
            })->sortable(),
        ];
    }




    public function exportSelected()
    {

        $this->isDemo(true);
        if ($this->selectedRowsQuery->count() > 0) {
            $fileName = "service_report(";
            //
            if ($this->getFilter('start_date')) {
                $fileName .= $this->getFilter('start_date') . " - ";
            }
            //
            else if ($this->getFilter('end_date')) {
                $fileName .= $this->getFilter('end_date');
            }

            $fileName .= ").xlsx";
            return Excel::download(new ProductReportExport($this->selectedRowsQuery), $fileName);
        } else {
            //
        }
    }
}
