<?php

namespace App\Http\Livewire\Component\Dashboard;

use App\Models\Order;
use App\Models\User;
use App\Models\Vehicle;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;
use Asantibanez\LivewireCharts\Models\PieChartModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Component;
use Numbers\Number;

class FleetManager extends Component
{
    public $recentOrders;
    public $topDrivers;
    protected $cacheTtl = 1800;

    public function mount()
    {
        $this->recentOrders = collect();
        $this->topDrivers = collect();
    }

    public function render()
    {
        return view('livewire.component.dashboard.fleet-manager');
    }

    public function getFleetProperty()
    {
        return Auth::user()->fleet();
    }

    public function getLiveDataFetchedAtProperty()
    {
        $timestamp = Cache::get($this->cacheKey('fetched_at'));

        return $timestamp ? Carbon::parse($timestamp)->format('d M Y, h:i A') : __('Not fetched yet');
    }

    public function getTotalOrdersProperty()
    {
        $totalOrders = $this->cacheDashboardData('total_orders', function () {
            return $this->fleetOrderQuery()->count();
        });

        return Number::n($totalOrders)->round(3)->getSuffixNotation();
    }

    public function getDeliveredOrdersProperty()
    {
        $deliveredOrders = $this->cacheDashboardData('delivered_orders', function () {
            return $this->fleetOrderQuery()->currentStatus('delivered')->count();
        });

        return Number::n($deliveredOrders)->round(3)->getSuffixNotation();
    }

    public function getActiveDriversProperty()
    {
        if (empty($this->fleet)) {
            return 0;
        }

        $activeDrivers = $this->cacheDashboardData('active_drivers', function () {
            return User::role('driver')
                ->active()
                ->join('fleet_user', 'users.id', '=', 'fleet_user.user_id')
                ->where('fleet_user.fleet_id', $this->fleet->id)
                ->count();
        });

        return Number::n($activeDrivers)->round(3)->getSuffixNotation();
    }

    public function getTotalVehiclesProperty()
    {
        if (empty($this->fleet)) {
            return 0;
        }

        $totalVehicles = $this->cacheDashboardData('total_vehicles', function () {
            return Vehicle::join('fleet_vehicle', 'vehicles.id', '=', 'fleet_vehicle.vehicle_id')
                ->where('fleet_vehicle.fleet_id', $this->fleet->id)
                ->count();
        });

        return Number::n($totalVehicles)->round(3)->getSuffixNotation();
    }

    public function getOrdersChartProperty()
    {
        return $this->cacheDashboardData('orders_chart', function () {
            $chart = (new ColumnChartModel())->setTitle(__('Fleet Orders') . ' (' . Date("Y") . ')')->withoutLegend();
            $colors = $this->getModernColors(12);

            for ($loop = 0; $loop < 12; $loop++) {
                $date = Carbon::now()->firstOfYear()->addMonths($loop);
                $formattedDate = $date->translatedFormat("M");
                $data = $this->fleetOrderQuery()
                    ->whereMonth("orders.created_at", $date)
                    ->whereYear('orders.created_at', $date)
                    ->count();

                $chart->addColumn(
                    $formattedDate,
                    $data,
                    $colors[$loop] ?? $this->genColor(),
                );
            }

            return $chart;
        });
    }

    public function getOrderStatusChartProperty()
    {
        return $this->cacheDashboardData('order_status_chart', function () {
            $chart = (new PieChartModel())->setTitle(__('Fleet Order Status'))->setAnimated(true);
            $statuses = Order::getPossibleStatues();
            $colors = $this->getModernColors(count($statuses));

            foreach ($statuses as $index => $status) {
                $data = $this->fleetOrderQuery()->currentStatus($status)->count();
                if ($data > 0) {
                    $chart->addSlice(
                        Str::title($status),
                        $data,
                        $colors[$index] ?? $this->genColor(),
                    );
                }
            }

            return $chart;
        });
    }

    public function getTopDriversChartProperty()
    {
        return $this->cacheDashboardData('top_drivers_chart', function () {
            $chart = (new ColumnChartModel())->setTitle(__('Top Drivers'))->withoutLegend();
            $drivers = $this->fleetDriverQuery()
                ->selectSub($this->driverOrdersCountQuery(), 'driver_orders_count')
                ->selectSub($this->driverOrdersCountQuery('delivered'), 'delivered_orders_count')
                ->orderBy('delivered_orders_count', 'desc')
                ->limit(6)
                ->get();
            $colors = $this->getModernColors($drivers->count());

            foreach ($drivers as $index => $driver) {
                $chart->addColumn(
                    Str::limit($driver->name, 18),
                    $driver->delivered_orders_count ?? 0,
                    $colors[$index] ?? $this->genColor(),
                );
            }

            return $chart;
        });
    }

    public function fetchRecentOrders()
    {
        $this->recentOrders = $this->cacheDashboardData('recent_orders', function () {
            return $this->fleetOrderQuery()
                ->with('user', 'driver', 'vendor')
                ->latest('orders.created_at')
                ->limit(6)
                ->get();
        });
    }

    public function fetchTopDrivers()
    {
        $this->topDrivers = $this->cacheDashboardData('top_drivers', function () {
            return $this->fleetDriverQuery()
                ->selectSub($this->driverOrdersCountQuery(), 'driver_orders_count')
                ->selectSub($this->driverOrdersCountQuery('delivered'), 'delivered_orders_count')
                ->orderBy('delivered_orders_count', 'desc')
                ->limit(6)
                ->get();
        });
    }

    protected function fleetOrderQuery()
    {
        $fleet = $this->fleet;

        return Order::query()->select('orders.*')->when(empty($fleet), function ($query) {
            return $query->where('id', 0);
        }, function ($query) use ($fleet) {
            return $query->join('fleet_user', 'orders.driver_id', '=', 'fleet_user.user_id')
                ->where('fleet_user.fleet_id', $fleet->id);
        });
    }

    protected function fleetDriverQuery()
    {
        $fleet = $this->fleet;

        return User::role('driver')->select('users.*')->when(empty($fleet), function ($query) {
            return $query->where('users.id', 0);
        }, function ($query) use ($fleet) {
            return $query->join('fleet_user', 'users.id', '=', 'fleet_user.user_id')
                ->where('fleet_user.fleet_id', $fleet->id);
        });
    }

    protected function driverOrdersCountQuery($status = null)
    {
        return Order::query()
            ->selectRaw('count(*)')
            ->whereColumn('orders.driver_id', 'users.id')
            ->when($status, function ($query) use ($status) {
                return $query->currentStatus($status);
            });
    }

    public function genColor()
    {
        return '#' . substr(str_shuffle('ABCDEF0123456789'), 0, 6);
    }

    public function getModernColors($count = 1)
    {
        $palette = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'];
        if ($count <= count($palette)) {
            return array_slice($palette, 0, $count);
        }

        $colors = $palette;
        for ($i = count($palette); $i < $count; $i++) {
            $colors[] = $this->genColor();
        }
        return $colors;
    }

    public function refreshDashboardData()
    {
        foreach ($this->dashboardCacheKeys() as $key) {
            Cache::forget($this->cacheKey($key));
        }

        $this->fetchRecentOrders();
        $this->fetchTopDrivers();
    }

    protected function cacheDashboardData($key, $callback)
    {
        return Cache::remember($this->cacheKey($key), $this->cacheTtl, function () use ($callback) {
            Cache::put($this->cacheKey('fetched_at'), now()->toIso8601String(), $this->cacheTtl);

            return $callback();
        });
    }

    protected function cacheKey($key)
    {
        return 'dashboard:fleet-manager:' . \Auth::id() . ':' . ($this->fleet->id ?? 'none') . ':' . app()->getLocale() . ':' . $key;
    }

    protected function dashboardCacheKeys()
    {
        return [
            'fetched_at',
            'total_orders',
            'delivered_orders',
            'active_drivers',
            'total_vehicles',
            'orders_chart',
            'order_status_chart',
            'top_drivers_chart',
            'recent_orders',
            'top_drivers',
        ];
    }
}
