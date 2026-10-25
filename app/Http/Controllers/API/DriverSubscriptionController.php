<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DriverSubscription;
use App\Models\DriverSubscriptionHistory;
use App\Models\Wallet;
use App\Traits\WalletTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DriverSubscriptionController extends Controller
{
    use WalletTrait;

    public function index()
    {
        $subscriptions = DriverSubscription::active()->orderBy('amount')->get();
        return response()->json($subscriptions);
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'driver_subscription_id' => 'required|exists:driver_subscriptions,id',
        ]);

        try {
            DB::beginTransaction();

            $subscription = DriverSubscription::active()
                ->where('id', $request->driver_subscription_id)
                ->lockForUpdate()
                ->firstOrFail();

            $wallet = Wallet::where('user_id', Auth::id())->first();

            if (empty($wallet)) {
                $wallet = Wallet::create([
                    'user_id' => Auth::id(),
                    'balance' => 0.00,
                ]);
            }

            if ($wallet->balance < $subscription->amount) {
                throw new \Exception(__("Wallet balance is less than subscription amount"));
            }

            $debitReason = __("Driver subscription payment for :subscription", [
                'subscription' => $subscription->name,
            ]);
            $debitResult = $this->debitWallet($wallet, $subscription->amount, $debitReason, 'ds_' . Str::random(10));
            $wallet = $debitResult['wallet'];
            $walletTransaction = $debitResult['transaction'];

            DriverSubscriptionHistory::active()
                ->where('driver_id', Auth::id())
                ->update(['status' => 'cancelled']);

            $history = new DriverSubscriptionHistory();
            $history->driver_subscription_id = $subscription->id;
            $history->driver_id = Auth::id();
            $history->wallet_transaction_id = $walletTransaction->id;
            $history->code = 'dsh_' . Str::random(12);
            $history->type = $subscription->type;
            $history->status = 'successful';
            $history->amount = $subscription->amount;
            $history->starts_at = now();

            if ($subscription->type === DriverSubscription::TYPE_TIME) {
                $history->expires_at = now()->addDays($subscription->days);
            } else {
                $history->order_limit = $subscription->order_limit;
                $history->remaining_orders = $subscription->order_limit;
            }

            $history->save();

            DB::commit();

            return response()->json([
                'message' => __('Driver subscription successful'),
                'subscription' => $history->fresh('subscription'),
                'wallet' => $wallet->fresh(),
            ]);
        } catch (\Exception $ex) {
            DB::rollback();

            return response()->json([
                'message' => $ex->getMessage(),
            ], 400);
        }
    }

    public function state()
    {
        $activeSubscription = DriverSubscriptionHistory::with('subscription')
            ->active()
            ->where('driver_id', Auth::id())
            ->latest()
            ->first();

        $latestSubscription = DriverSubscriptionHistory::with('subscription')
            ->where('driver_id', Auth::id())
            ->latest()
            ->first();

        return response()->json([
            'has_active_subscription' => !empty($activeSubscription),
            'subscription' => $activeSubscription,
            'latest_subscription' => $latestSubscription,
        ]);
    }

    public function history()
    {
        return DriverSubscriptionHistory::with('subscription')
            ->where('driver_id', Auth::id())
            ->latest()
            ->paginate();
    }
}
