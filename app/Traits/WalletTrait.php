<?php

namespace App\Traits;


use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

trait WalletTrait
{

    public function recordWalletDebit($wallet, $amount, $reason = "New Order")
    {
        $walletTransaction = new WalletTransaction();
        $walletTransaction->wallet_id = $wallet->id;
        $walletTransaction->amount = $amount;
        $walletTransaction->reason = __($reason);
        $walletTransaction->status = "successful";
        $walletTransaction->is_credit = 0;
        $walletTransaction->save();
    }

    // lockForUpdate prevents concurrent debits/credits from racing on the same wallet row
    public function debitWallet($wallet, $amount, $reason = "New Order", $ref = null)
    {
        return DB::transaction(function () use ($wallet, $amount, $reason, $ref) {
            $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->firstOrFail();

            if ($lockedWallet->balance < $amount) {
                throw new \Exception(__("Wallet balance is less than the requested amount"));
            }

            $lockedWallet->balance -= $amount;
            $lockedWallet->save();

            $walletTransaction = $lockedWallet->saveWalletTransaction($amount, __($reason), false, $ref);

            return [
                'wallet' => $lockedWallet,
                'transaction' => $walletTransaction,
            ];
        });
    }

    // like debitWallet() but skips the insufficient-balance guard, for call sites that intentionally allow a wallet to go negative (admin overrides, fee deductions)
    public function debitWalletAllowOverdraft($wallet, $amount, $reason = "Debit", $ref = null)
    {
        return DB::transaction(function () use ($wallet, $amount, $reason, $ref) {
            $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->firstOrFail();

            $lockedWallet->balance -= $amount;
            $lockedWallet->save();

            $walletTransaction = $lockedWallet->saveWalletTransaction($amount, __($reason), false, $ref);

            return [
                'wallet' => $lockedWallet,
                'transaction' => $walletTransaction,
            ];
        });
    }

    // omit $reason when the caller already owns/updates its own WalletTransaction row (e.g. flipping a pending topup to successful)
    public function creditWallet($wallet, $amount, $reason = null, $ref = null)
    {
        return DB::transaction(function () use ($wallet, $amount, $reason, $ref) {
            $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->firstOrFail();

            $lockedWallet->balance += $amount;
            $lockedWallet->save();

            $walletTransaction = null;
            if (!empty($reason)) {
                $walletTransaction = $lockedWallet->saveWalletTransaction($amount, __($reason), true, $ref);
            }

            return [
                'wallet' => $lockedWallet,
                'transaction' => $walletTransaction,
            ];
        });
    }


}
