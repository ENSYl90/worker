<?php

namespace App\Http\Livewire;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Traits\WalletTrait;
use Illuminate\Support\Facades\DB;

class WalletTransactionLivewire extends BaseLivewireComponent
{
    use WalletTrait;

    //
    public $model = WalletTransaction::class;
    public $user_id;
    public $wallet;
    public $actions = [
        "credit" => "Credit",
        "debit" => "Debit",
    ];
    public $selectedAction = "credit";
    public $amount;
    public $note;

    public function getListeners()
    {
        return $this->listeners + [
            'user_idUpdated' => 'userIdUpdated',
        ];
    }

    public function mount()
    {
        \Cache::put('wallet_current_url', route('wallet.transactions'));
    }

    public function render()
    {
        return view('livewire.wallet-transactions');
    }


    public function userIdUpdated($selected)
    {
        $userId = $selected['value'];
        $this->user_id = $userId;
        $this->wallet = Wallet::where("user_id", $userId)->first();
        if ($this->wallet == null && $userId != null) {
            $this->wallet = new Wallet();
            $this->wallet->user_id = $userId;
            $this->wallet->balance = 0.00;
            $this->wallet->save();
            //
            $this->wallet->refresh();
        }
    }


    public function save()
    {

        $this->validate(
            [
                "user_id" => "required|exists:users,id",
                "selectedAction" => "required",
                "amount" => "required|numeric",
            ]
        );


        try {

            DB::beginTransaction();

            $reason = $this->note ?: "Wallet Adjustment";
            if ($this->selectedAction == "credit") {
                $this->creditWallet($this->wallet, $this->amount, $reason);
            } else {
                $this->debitWalletAllowOverdraft($this->wallet, $this->amount, $reason);
            }

            DB::commit();
            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Wallet") . " " . __('updated successfully!'));
            $this->emit('clearAutocompleteFieldsEvent');
            $this->emit('refreshTable');
        } catch (\Exception $ex) {
            DB::rollback();
            $this->showErrorAlert($ex->getMessage() ?? __('Failed'));
        }
    }
}
