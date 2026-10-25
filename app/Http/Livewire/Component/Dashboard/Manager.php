<?php

namespace App\Http\Livewire\Component\Dashboard;

use App\Models\Commission;
use App\Models\Earning;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Numbers\Number;

class Manager extends Component
{
    protected $cacheTtl = 1800;
    public $myTopSellingProducts;

    public function render()
    {
        return view('livewire.component.dashboard.manager');
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
                return Commission::whereHas('order', function ($q) {
                    $q->mine();
                })->sum('vendor_commission');
            }

            $earning = Earning::firstOrCreate([
                "vendor_id" => \Auth::user()->vendor_id,
            ], [
                "amount" => 0,
            ]);

            return $earning->amount;
        });

        return Number::n($earnedAmount)->round(3)->getSuffixNotation();
    }

    public function getEarningChartProperty()
    {
        return $this->cacheDashboardData('earning_chart', function () {
            $chart = (new ColumnChartModel())->setTitle(__('Total Earning') . ' (' . Date("Y") . ')')->withoutLegend();

            for ($loop = 0; $loop < 12; $loop++) {
                $date = Carbon::now()->firstOfYear()->addMonths($loop);
                $formattedDate = $date->translatedFormat("M");

                if (\Schema::hasColumn("commissions", 'admin_commission')) {
                    $data = Commission::whereMonth("created_at", $date)
                        ->whereYear('created_at', $date)
                        ->whereHas('order', function ($q) {
                            $q->mine();
                        })
                        ->sum('vendor_commission');
                } else {
                    $data = Earning::where("vendor_id", \Auth::user()->vendor_id)
                        ->whereMonth("created_at", $date)
                        ->whereYear('created_at', $date)
                        ->sum('amount');
                }

                $chart->addColumn(
                    $formattedDate,
                    currencyValueFormat($data),
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

    public function getTopSaleDaysChartProperty()
    {
        return $this->cacheDashboardData('top_sale_days_chart', function () {
            $chart = (new ColumnChartModel())->setTitle(__('Total Ordering Days') . ' (' . Date("Y") . ')')->withoutLegend();
            $firstDayOfWeek = Carbon::now()->startOfWeek();

            for ($dayOfWeek = 1; $dayOfWeek <= 7; $dayOfWeek++) {
                $results = Order::mine()
                    ->select(DB::raw('DAYOFWEEK(created_at) as day_of_week'), DB::raw('COUNT(*) as total'))
                    ->where(DB::raw('DAYOFWEEK(created_at)'), $dayOfWeek)
                    ->groupBy(DB::raw('DAYOFWEEK(created_at)'))
                    ->first();

                $chart->addColumn(
                    $firstDayOfWeek->format('l'),
                    $results ? $results->total : 0,
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
                $labelTime = $time->format('h:i A');
                $time->addHours($interval);

                $chart->addColumn(
                    $labelTime . " - " . $time->format('h:i A'),
                    $results ? $results->total : 0,
                    $this->getModernColors(6)[$loop] ?? $this->genColor(),
                );
                $loop++;
            }

            return $chart;
        });
    }

    public function fetchMyTopSellingProducts()
    {
        $this->myTopSellingProducts = $this->cacheDashboardData('my_top_selling_products', function () {
            return Product::mine()
                ->withSum('successful_sales', 'quantity')
                ->withCount('successful_sales')->orderBy('successful_sales_count', 'desc')->limit(6)->get();
        });
    }

    public function refreshDashboardData()
    {
        $loadedProducts = $this->myTopSellingProducts !== null;

        foreach ($this->dashboardCacheKeys() as $key) {
            Cache::forget($this->cacheKey($key));
        }

        if ($loadedProducts) {
            $this->fetchMyTopSellingProducts();
        }
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

    protected function cacheDashboardData($key, $callback)
    {
        return Cache::remember($this->cacheKey($key), $this->cacheTtl, function () use ($callback) {
            Cache::put($this->cacheKey('fetched_at'), now()->toIso8601String(), $this->cacheTtl);

            return $callback();
        });
    }

    protected function cacheKey($key)
    {
        return 'dashboard:manager:' . \Auth::id() . ':' . app()->getLocale() . ':' . $key;
    }

    protected function dashboardCacheKeys()
    {
        return [
            'fetched_at',
            'total_orders',
            'total_earnings',
            'earning_chart',
            'orders_chart',
            'top_sale_days_chart',
            'top_sale_timing_chart',
            'my_top_selling_products',
        ];
    }
}
