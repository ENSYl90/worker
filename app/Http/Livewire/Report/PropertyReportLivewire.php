<?php

namespace App\Http\Livewire\Report;

use App\Http\Livewire\BaseLivewireComponent;
use App\Models\Vendor;
use App\Models\Property;
use App\Models\PropertyType;
use Asantibanez\LivewireCharts\Charts\LivewirePieChart;
use Asantibanez\LivewireCharts\Facades\LivewireCharts;
use Illuminate\Support\Facades\DB;

class PropertyReportLivewire extends BaseLivewireComponent
{

    //
    public $model = Property::class;

    public function render()
    {

        return view('livewire.reports.property_report', [
            "propertiesChart" => $this->propertiesChart(),
            "topPropertyTypeChart" => $this->topPropertyTypeChart(),
            "topHostsChart" => $this->topHostsChart(),
        ]);
    }


    public function propertiesChart()
    {
        //
        $chart = (new LivewirePieChart());
        $dataSet = $this->model::mine()->orderByPowerJoinsCount('bookingOrders.id', 'desc')->limit(10)->get();
        $chart = $dataSet->reduce(
            function ($pieChartModel, $data) {
                return $pieChartModel->addSlice($data["name"], $data->bookingOrders()->count(), $this->genColor());
            },
            LivewireCharts::pieChartModel()
                ->setTitle(__("Top Properties"))
                ->setAnimated(true)
                ->legendPositionBottom()
                ->legendHorizontallyAlignedCenter()
                ->setDataLabelsEnabled(false)
        );
        return $chart;
    }

    public function topPropertyTypeChart()
    {
        //
        $chart = (new LivewirePieChart());
        //
        $dataSet = PropertyType::query()
            ->join('properties', 'property_types.id', '=', 'properties.property_type_id')
            ->leftJoin('booking_orders', 'properties.id', '=', 'booking_orders.property_id')
            ->select('property_types.*', DB::raw('COUNT(booking_orders.id) as sales_count'))
            ->groupBy('property_types.id')
            ->orderBy('sales_count', 'desc')
            ->limit(10)
            ->get();
        //
        $chart = $dataSet->reduce(
            function ($pieChartModel, $data) {
                return $pieChartModel->addSlice($data["name"], $data->sales_count, $this->genColor());
            },
            LivewireCharts::pieChartModel()
                ->setTitle(__("Top Property Types"))
                ->setAnimated(true)
                ->legendPositionBottom()
                ->legendHorizontallyAlignedCenter()
                ->setDataLabelsEnabled(false)
        );
        return $chart;
    }

    public function topHostsChart()
    {
        //return null if user is not admin
        if (!auth()->user()->hasRole('admin')) {
            return null;
        }
        //Vendor 
        $chart = (new LivewirePieChart());
        //
        $dataSet = Vendor::query()
            ->join('properties', 'vendors.id', '=', 'properties.vendor_id')
            ->leftJoin('booking_orders', 'properties.id', '=', 'booking_orders.property_id')
            ->select('vendors.*', DB::raw('COUNT(booking_orders.id) as sales_count'))
            ->groupBy('vendors.id')
            ->orderBy('sales_count', 'desc')
            ->limit(10)
            ->get();
        //
        $chart = $dataSet->reduce(
            function ($pieChartModel, $data) {
                return $pieChartModel->addSlice($data["name"], $data->sales_count, $this->genColor());
            },
            LivewireCharts::pieChartModel()
                ->setTitle(__("Top Hosts"))
                ->setAnimated(true)
                ->legendPositionBottom()
                ->legendHorizontallyAlignedCenter()
                ->setDataLabelsEnabled(false)
        );
        return $chart;
    }
}