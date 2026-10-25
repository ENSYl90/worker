<?php

namespace App\Http\Livewire;

use App\Models\Order;

class OrderDetailsLivewire extends BaseLivewireComponent
{
    //
    public $model = Order::class;


    public function mount($code)
    {
        $this->selectedModel = Order::with('booking_order')->where("code", $code)->firstOrFail();
    }

    public function render()
    {
        return view('livewire.orders_details');
    }


}