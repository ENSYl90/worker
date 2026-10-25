<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\EarningReportResource;
use App\Http\Resources\PayoutReportResource;
use App\Models\Earning;
use App\Models\EarningReport;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Remittance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DriverReportController extends Controller
{

    public function payouts(Request $request)
    {
        $sDate = $request->start_date;
        $eDate = $request->end_date;
        $userId = Auth::id();
        //add the filters
        // $payoutsReport = Payout::when($sDate, function ($query) use ($sDate) {
        $payoutsReport = Payout::with('payment_account')
            ->whereHas("earning", function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->when($sDate, function ($query) use ($sDate) {
                return $query->whereDate('created_at', ">=", $sDate);
            })->when($eDate, function ($query) use ($eDate) {
                return $query->whereDate('created_at', "<=", $eDate);
            })->paginate();
        //
        $payoutsReport = PayoutReportResource::collection($payoutsReport);
        return response()->json($payoutsReport);
    }

    public function earnings(Request $request)
    {
        $sDate = $request->start_date;
        $eDate = $request->end_date;
        $userId = Auth::id();
        $earningReports = EarningReport::whereHas("earnings", function ($q) use ($userId) {
            $q->where('user_id', $userId);
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

    public function metrics(Request $request)
    {
        $driver = User::find(Auth::id());
        $driverWallet = $driver->createWallet();
        $walletBalance = $driverWallet->balance ?? 0.0;
        //remittance
        $pendingRemittance = Remittance::where("user_id", $driver->id)->where('status', "pending")->get()->sum('order_total');
        //$orders
        $totalOrdersAllTime = Order::where("driver_id", $driver->id)->count();
        $totalOrdersThisWeek = Order::where("driver_id", $driver->id)
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->count();

        $totalOrdersThisMonth = Order::where("driver_id", $driver->id)
            ->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->count();
        $totalOrdersToday = Order::where('driver_id', $driver->id)
            ->whereBetween('created_at', [
                Carbon::today(),
                Carbon::tomorrow(),
            ])
            ->count();

        //earnings
        $currentEarning = Earning::where("user_id", $driver->id)->first()->amount ?? 0.00;
        $todayEarning = EarningReport::whereHas("earnings", function ($q) use ($driver) {
            $q->where('user_id', $driver->id);
        })->whereBetween('created_at', [
            Carbon::today(),
            Carbon::tomorrow(),
        ])->sum("balance") ?? 0.00;
        $thisWeekEarning = EarningReport::whereHas("earnings", function ($q) use ($driver) {
            $q->where('user_id', $driver->id);
        })->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ])->sum("balance") ?? 0.00;
        $thisMonthEarning = EarningReport::whereHas("earnings", function ($q) use ($driver) {
            $q->where('user_id', $driver->id);
        })->whereBetween('created_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
        ])->sum("balance") ?? 0.00;
        //
        return response()->json([
            "earnings" => [
                "current" => $currentEarning,
                "today" => $todayEarning,
                "week" => $thisWeekEarning,
                "month" => $thisMonthEarning,
            ],
            "orders" => [
                "today" => $totalOrdersToday,
                "week" => $totalOrdersThisWeek,
                "month" => $totalOrdersThisMonth,
                "all_time" => $totalOrdersAllTime,
            ],
            "money" => [
                "pending_remittance" => $pendingRemittance,
                "wallet_balance" => $walletBalance,
            ],
        ]);
    }
}
