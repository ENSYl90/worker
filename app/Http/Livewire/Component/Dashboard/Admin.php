<?php

namespace App\Http\Livewire\Component\Dashboard;

use App\Models\Vendor;
use App\Models\Order;
use App\Models\Commission;
use App\Models\Product;
use App\Models\User;
use Asantibanez\LivewireCharts\Models\PieChartModel;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Numbers\Number;
use Livewire\Component;

class Admin extends Component
{
    protected $cacheTtl = 1800;

    public function render()
    {
        return view('livewire.component.dashboard.admin');
    }

    public function getLiveDataFetchedAtProperty()
    {
        $timestamp = Cache::get($this->cacheKey('fetched_at'));

        return $timestamp ? Carbon::parse($timestamp)->format('d M Y, h:i A') : __('Not fetched yet');
    }

    public function getTotalOrdersProperty()
    {
        $totalOrders = $this->cacheDashboardData('total_orders', function () {
            return Order::mine()->count();
        });

        return Number::n($totalOrders)->round(3)->getSuffixNotation();
    }

    public function getTotalEarningsProperty()
    {
        $earnedAmount = $this->cacheDashboardData('total_earnings', function () {
            if (\Schema::hasColumn("commissions", 'admin_commission')) {
                return Commission::sum('admin_commission');
            }

            return Order::mine()->currentStatus('delivered')->sum('total');
        });

        return Number::n($earnedAmount)->round(3)->getSuffixNotation();
    }

    public function getTotalVendorsProperty()
    {
        $totalVendors = $this->cacheDashboardData('total_vendors', function () {
            return Vendor::mine()->count();
        });

        return Number::n($totalVendors)->round(3)->getSuffixNotation();
    }

    public function getTotalClientsProperty()
    {
        $totalClients = $this->cacheDashboardData('total_clients', function () {
            return User::client()->count();
        });

        return Number::n($totalClients)->round(3)->getSuffixNotation();
    }

    public function getEarningChartProperty()
    {
        return $this->cacheDashboardData('earning_chart', function () {
            $chart = (new ColumnChartModel())->setTitle(__('Total Earning') . ' (' . Date("Y") . ')')->withoutLegend();

            for ($loop = 0; $loop < 12; $loop++) {
                $date = Carbon::now()->firstOfYear()->addMonths($loop);
                $formattedDate = $date->translatedFormat("M");

                if (\Schema::hasColumn("commissions", 'admin_commission')) {
                    $data = Commission::whereMonth("created_at", $date)->whereYear('created_at', $date)->sum('admin_commission');
                } else {
                    $data = Order::mine()->whereMonth("created_at", $date)->whereYear('created_at', $date)->sum('total');
                }

                $data = currencyValueFormat($data);

                $chart->addColumn(
                    $formattedDate,
                    $data,
                    $this->getModernColors(12)[$loop] ?? $this->genColor(),
                );
            }

            return $chart;
        });
    }

    public function getUsersChartProperty()
    {
        return $this->cacheDashboardData('users_chart', function () {
            $chart = (new ColumnChartModel())->setTitle(__('Users This Week'))->withoutLegend();

            for ($loop = 0; $loop < 7; $loop++) {
                $date = Carbon::now()->startOfWeek()->addDays($loop);
                $formattedDate = $date->translatedFormat("D");
                $data = User::whereDate("created_at", $date)->count();

                $chart->addColumn(
                    $formattedDate,
                    $data,
                    $this->getModernColors(7)[$loop] ?? $this->genColor(),
                );
            }

            return $chart;
        });
    }

    public function getVendorsChartProperty()
    {
        return $this->cacheDashboardData('vendors_chart', function () {
            $chart = (new ColumnChartModel())->setTitle(__('Vendors This Year'))->withoutLegend();

            for ($loop = 0; $loop < 12; $loop++) {
                $date = Carbon::now()->firstOfYear()->addMonths($loop);
                $formattedDate = $date->translatedFormat("M");
                $data = Vendor::whereMonth("created_at", $date)->whereYear('created_at', $date)->count();

                $chart->addColumn(
                    $formattedDate,
                    $data,
                    $this->getModernColors(12)[$loop] ?? $this->genColor(),
                );
            }

            return $chart;
        });
    }

    public function getOrdersChartProperty()
    {
        return $this->cacheDashboardData('orders_chart', function () {
            $chart = (new ColumnChartModel())->setTitle(__('Total Orders') . ' (' . Date("Y") . ')')->withoutLegend();

            for ($loop = 0; $loop < 12; $loop++) {
                $date = Carbon::now()->firstOfYear()->addMonths($loop);
                $formattedDate = $date->translatedFormat("M");
                $data = Order::mine()->whereMonth("created_at", $date)->whereYear('created_at', $date)->count();

                $chart->addColumn(
                    $formattedDate,
                    $data,
                    $this->getModernColors(12)[$loop] ?? $this->genColor(),
                );
            }

            return $chart;
        });
    }

    public function getUserRolesChartProperty()
    {
        return $this->cacheDashboardData('user_roles_chart', function () {
            $chart = (new PieChartModel())->setTitle(__('User Statistics'))->setAnimated(true);
            $roles = \Spatie\Permission\Models\Role::all();
            $colors = $this->getModernColors(count($roles));

            foreach ($roles as $index => $role) {
                $data = User::role($role->name)->count();
                if ($data > 0) {
                    $chart->addSlice(
                        $role->name,
                        $data,
                        $colors[$index] ?? $this->genColor(),
                    );
                }
            }

            return $chart;
        });
    }

    public function getTopSaleDaysChartProperty()
    {
        return $this->cacheDashboardData('top_sale_days_chart', function () {
            $chart = (new ColumnChartModel())->setTitle(__('Total Ordering Days') . ' (' . Date("Y") . ')')->withoutLegend();
            $currentDate = Carbon::now();
            $firstDayOfWeek = $currentDate->startOfWeek();

            for ($dayOfWeek = 1; $dayOfWeek <= 7; $dayOfWeek++) {
                $results = Order::mine()
                    ->select(DB::raw('DAYOFWEEK(created_at) as day_of_week'), DB::raw('COUNT(*) as total'))
                    ->where(DB::raw('DAYOFWEEK(created_at)'), $dayOfWeek)
                    ->groupBy(DB::raw('DAYOFWEEK(created_at)'))
                    ->first();
                $currentDayName = $firstDayOfWeek->format('l');
                $totalOrders = $results ? $results->total : 0;

                $chart->addColumn(
                    $currentDayName,
                    $totalOrders,
                    $this->getModernColors(7)[$dayOfWeek - 1] ?? $this->genColor(),
                );
                $firstDayOfWeek->addDay();
            }

            return $chart;
        });
    }

    public function getTopSaleTimingChartProperty()
    {
        return $this->cacheDashboardData('top_sale_timing_chart', function () {
            $chart = (new ColumnChartModel())->setTitle(__('Avg. Ordering Period') . ' (' . __("30days") . ')')->setHorizontal(true)->withoutLegend();
            $time = Carbon::createFromTime(0, 0, 0);
            $interval = 4;
            $loop = 0;
            while ($time->lt(Carbon::createFromTime(24, 0, 0))) {
                $hourFormattedTime = $time->format('H');
                $results = Order::mine()
                    ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as total'))
                    ->where(DB::raw('HOUR(created_at)'), $hourFormattedTime)
                    ->groupBy(DB::raw('HOUR(created_at)'))
                    ->where('created_at', '>=', Carbon::now()->subDays(30))
                    ->first();
                $totalOrders = $results ? $results->total : 0;
                $labelTime = $time->format('h:i A');
                $time->addHours($interval);
                $labelTime = $labelTime . " - " . $time->format('h:i A');

                $chart->addColumn(
                    $labelTime,
                    $totalOrders,
                    $this->getModernColors(6)[$loop] ?? $this->genColor(),
                );
                $loop++;
            }

            return $chart;
        });
    }

    public function getOrderStatusChartProperty()
    {
        return $this->cacheDashboardData('order_status_chart', function () {
            $chart = (new PieChartModel())->setTitle(__('Order Status Breakdown'))->setAnimated(true);
            $statuses = Order::getPossibleStatues();
            $colors = $this->getModernColors(count($statuses));

            foreach ($statuses as $index => $status) {
                $data = Order::mine()->currentStatus($status)->count();
                if ($data > 0) {
                    $chart->addSlice(
                        \Illuminate\Support\Str::title($status),
                        $data,
                        $colors[$index] ?? $this->genColor(),
                    );
                }
            }

            return $chart;
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

    public $topSellingVendors;
    public $topRatedVendors;
    public $topCustomers;

    public function fetchTopSellingVendors()
    {
        $this->topSellingVendors = $this->cacheDashboardData('top_selling_vendors', function () {
            return Vendor::mine()->withCount('successful_sales')->orderBy('successful_sales_count', 'desc')->limit(6)->get();
        });
    }

    public function fetchTopRatedVendors()
    {
        $this->topRatedVendors = $this->cacheDashboardData('top_rated_vendors', function () {
            return Vendor::mine()
                ->withCount('ratings')->orderBy('ratings_count', 'desc')
                ->limit(6)->get();
        });
    }

    public function fetchTopCustomers()
    {
        $this->topCustomers = $this->cacheDashboardData('top_customers', function () {
            return User::withCount('orders', 'successful_orders')
                ->orderBy('successful_orders_count', 'desc')->limit(6)->get();
        });
    }

    public $topSellingProducts;

    public function fetchTopSellingProducts()
    {
        $this->topSellingProducts = $this->cacheDashboardData('top_selling_products', function () {
            return Product::mine()
                ->withSum('successful_sales', 'quantity')
                ->withCount('successful_sales')->orderBy('successful_sales_count', 'desc')->limit(6)->get();
        });
    }

    public $userRolesSummary;

    public function fetchUserRoleSummary()
    {
        $this->userRolesSummary = $this->cacheDashboardData('user_roles_summary', function () {
            $roles = \Spatie\Permission\Models\Role::all();
            $summary = [];
            foreach ($roles as $role) {
                $summary[$role->name] = User::role($role->name)->count();
            }
            return $summary;
        });
    }

    public function refreshDashboardData()
    {
        $loadedLists = [
            'topSellingVendors' => $this->topSellingVendors !== null,
            'topRatedVendors' => $this->topRatedVendors !== null,
            'topCustomers' => $this->topCustomers !== null,
            'topSellingProducts' => $this->topSellingProducts !== null,
            'userRolesSummary' => $this->userRolesSummary !== null,
        ];

        foreach ($this->dashboardCacheKeys() as $key) {
            Cache::forget($this->cacheKey($key));
        }

        if ($loadedLists['topSellingVendors']) {
            $this->fetchTopSellingVendors();
        }
        if ($loadedLists['topRatedVendors']) {
            $this->fetchTopRatedVendors();
        }
        if ($loadedLists['topCustomers']) {
            $this->fetchTopCustomers();
        }
        if ($loadedLists['topSellingProducts']) {
            $this->fetchTopSellingProducts();
        }
        if ($loadedLists['userRolesSummary']) {
            $this->fetchUserRoleSummary();
        }
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
        return 'dashboard:admin:' . \Auth::id() . ':' . app()->getLocale() . ':' . $key;
    }

    protected function dashboardCacheKeys()
    {
        return [
            'fetched_at',
            'total_orders',
            'total_earnings',
            'total_vendors',
            'total_clients',
            'earning_chart',
            'users_chart',
            'vendors_chart',
            'orders_chart',
            'user_roles_chart',
            'top_sale_days_chart',
            'top_sale_timing_chart',
            'order_status_chart',
            'top_selling_vendors',
            'top_rated_vendors',
            'top_customers',
            'top_selling_products',
            'user_roles_summary',
        ];
    }
}
