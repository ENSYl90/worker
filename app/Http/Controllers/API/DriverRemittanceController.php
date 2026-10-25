<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Remittance;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DriverRemittanceController extends Controller
{

    public function pay(Request $request)
    {

        //first get the user wallet
        $driver = User::find(Auth::id());

        try {
            DB::beginTransaction();

            //Get wallet and lock it
            $driverWallet = $driver->wallet()->lockForUpdate()->first();
            if (empty($driverWallet)) {
                $driverWallet = $driver->createWallet();
                $driverWallet->lockForUpdate();
            }

            //Get pending remittances and lock them
            $pendingRemittances = Remittance::where("user_id", $driver->id)->where('status', "pending")->lockForUpdate()->get();
            $totalPendingRemittance = $pendingRemittances->sum('order_total');
            $remWalletBalance = $driverWallet->balance;
            $isPartialSettlement = false;

            $totalWalletDebit = 0;
            if ($totalPendingRemittance <= $driverWallet->balance) {
                $totalWalletDebit = $totalPendingRemittance;
                $remWalletBalance -= $totalPendingRemittance;
                //perform mass update status
                Remittance::whereIn('id', $pendingRemittances->pluck('id'))->update([
                    "status" => "collected"
                ]);
            } else {

                //loop through each pendingRemittances and 
                foreach ($pendingRemittances as $remittance) {
                    if ($remWalletBalance < $remittance->order_total) {
                        $isPartialSettlement = true;
                        break;
                    }

                    //reduce wallet balance = 
                    $remWalletBalance -= $remittance->order_total;
                    $totalWalletDebit += $remittance->order_total;
                    //update the remittance record
                    $remittance->status = "collected";
                    $remittance->save();
                }
            }

            //debit the wallet balance and write wallet transaction
            $driver->updateWallet(
                $remWalletBalance,
                __("Remittance Collected")
            );

            DB::commit();

            return response()->json([
                "message" => __("Remittance Collected"),
                "partial_settlement" => $isPartialSettlement
            ], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                "message" => $ex->getMessage(),
                "status" => false
            ], 400);
        }
    }
}