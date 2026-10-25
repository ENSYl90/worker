<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Order;
use App\Models\LoyaltyPoint;

class UserDetailsLivewire extends BaseLivewireComponent
{

    //
    public $model = User::class;
    public $ordersCount = 0;
    public $loyaltyPoints = 0;
    public $expensiveOrders = 0;
    public $prevUserId;
    public $nextUserId;

    public function mount($id)
    {
        // $this->selectedModel = User::withTrashed()->where('id', $id)->orWhere("uuid", $id)->firstOrFail();
        $this->selectedModel = User::withTrashed()->where("uuid", $id)->firstOrFail();
        $userId = $this->selectedModel->id;
        $this->ordersCount = Order::where('user_id', $userId)->count();
        $this->expensiveOrders = Order::where('user_id', $userId)->orderBy('total', 'DESC')->first()->total ?? 0;
        $this->prevUserId = User::where('id', '<', $this->selectedModel->id)->max('uuid');
        $this->nextUserId = User::where('id', '>', $this->selectedModel->id)->min('uuid');
        $this->loyaltyPoints = LoyaltyPoint::where('user_id', $userId)->first()->points ?? 0;
    }

    public function render()
    {
        return view('livewire.user_details');
    }
}