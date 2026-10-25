<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\EarningReportResource;
use App\Http\Resources\SaleReportResource;
use App\Models\EarningReport;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderService;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Models\VendorManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

class VendorReportController extends Controller
{

    public function sales(Request $request)
    {
        if (Auth::user()->vendor == null) {
            return response()->json([
                "message" => __("Current user seems to have not vendor assigned"),
            ], 400);
        }
        $sDate = $request->start_date;
        $eDate = $request->end_date;
        $vendorId = Auth::user()->vendor_id;
        $vendorType = Auth::user()->vendor->vendor_type->slug;
        //
        if ($vendorType == "service" || $vendorType == "booking") {
            $query = OrderService::select('*', DB::raw('DATE(created_at) as date'), DB::raw('SUM(price) as total_amount'), DB::raw('SUM(hours) as total_unit'))
                ->whereHas("order", function ($q) use ($vendorId) {
                    $q->where('vendor_id', $vendorId)->currentStatus('delivered');
                })
                ->groupBy('service_id', DB::raw('DATE(created_at)'));
        } else  if ($vendorType == "parcel" || $vendorType == "package") {
            $query = Order::select('*', DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total_amount'), DB::raw('COUNT(*) as total_unit'))
                ->currentStatus('delivered')
                ->where('vendor_id', $vendorId)
                ->groupBy('package_type_id', DB::raw('DATE(created_at)'));
        } else {
            $query = OrderProduct::select('*', DB::raw('DATE(created_at) as date'), DB::raw('SUM(price) as total_amount'), DB::raw('SUM(quantity) as total_unit'))
                ->whereHas("order", function ($q) use ($vendorId) {
                    $q->where('vendor_id', $vendorId)->currentStatus('delivered');
                })
                ->groupBy('product_id', DB::raw('DATE(created_at)'));
        }

        //add the filters
        $salesReport = $query->when($sDate, function ($query) use ($sDate) {
            return $query->whereDate('created_at', ">=", $sDate);
        })->when($eDate, function ($query) use ($eDate) {
            return $query->whereDate('created_at', "<=", $eDate);
        })->paginate();

        //
        $salesReport = SaleReportResource::collection($salesReport);
        return response()->json($salesReport);
    }

    public function earnings(Request $request)
    {
        $sDate = $request->start_date;
        $eDate = $request->end_date;
        $vendorId = Auth::user()->vendor_id;
        $earningReports = EarningReport::where(function ($query) use ($vendorId) {
            $query->whereHas("earnings", function ($q) use ($vendorId) {
                    $q->where('vendor_id', $vendorId);
                });
        })->when($sDate, function ($query) use ($sDate) {
            return $query->whereDate('created_at', ">=", $sDate);
        })->when($eDate, function ($query) use ($eDate) {
            return $query->whereDate('created_at', "<=", $eDate);
        })
            ->select(
                '*',
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(earning) as total_earning'),
                DB::raw('SUM(commission) as total_commission'),
                DB::raw('SUM(balance) as total_balance')
            )
            // ->groupBy('created_at')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->paginate();

        //
        $earningReports = EarningReportResource::collection($earningReports);
        return response()->json($earningReports);
    }

    public function summary(Request $request)
    {
        if (Auth::user()->vendor == null) {
            return response()->json([
                "message" => __("Current user seems to have not vendor assigned"),
            ], 400);
        }

        $vendorId = Auth::user()->vendor_id;
        $locale = app()->getLocale();
        $cacheKey = "vendor.report.summary.{$vendorId}.{$locale}." . today()->toDateString();

        $summary = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($vendorId, $locale) {
            $baseQuery = Order::where('vendor_id', $vendorId)->currentStatus('delivered');

            $todaysRevenue = (clone $baseQuery)->whereDate('created_at', today())->sum('total');
            $todaysTotalOrder = (clone $baseQuery)->whereDate('created_at', today())->count();
            $totalRevenue = (clone $baseQuery)->sum('total');
            $totalOrders = (clone $baseQuery)->count();
            $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

            //current week starts Saturday for arabic locale, Monday otherwise
            $weekStartsAt = $locale == 'ar' ? CarbonInterface::SATURDAY : CarbonInterface::MONDAY;
            $weekEndsAt = ($weekStartsAt + 6) % 7;
            $startOfWeek = now()->startOfWeek($weekStartsAt);
            $endOfWeek = now()->endOfWeek($weekEndsAt);

            $ordersByDay = (clone $baseQuery)
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total_amount'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('total_amount', 'date');

            $salesOrderChart = [];
            $day = $startOfWeek->copy();
            while ($day->lte($endOfWeek)) {
                $salesOrderChart[] = [
                    'label' => $day->translatedFormat('D'),
                    'value' => (float) ($ordersByDay[$day->toDateString()] ?? 0),
                ];
                $day->addDay();
            }

            return [
                'todays_revenue' => (float) $todaysRevenue,
                'todays_total_order' => $todaysTotalOrder,
                'total_revenue' => (float) $totalRevenue,
                'total_orders' => $totalOrders,
                'avg_order_value' => round($avgOrderValue, 2),
                'sales_order_chart' => $salesOrderChart,
            ];
        });

        return response()->json($summary);
    }
}