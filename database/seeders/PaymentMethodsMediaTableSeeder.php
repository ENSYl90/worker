<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodsMediaTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        $files = [
            "cash",
            "stripe",
            "paystack",
            "razorpay",
            "flutterwave",
            "offline",
            "billplz",
            "wallet",
            "abitmedia",
            "paypal",
            "paytm",
            "payu",
        ];

        foreach ($files as $fileName) {
            $photo = public_path('images/payment_methods/' . $fileName . ".png");
            $paymentMethod = PaymentMethod::where("slug", $fileName)->first();
            $paymentMethod->clearMediaCollection();
            $paymentMethod->addMedia($photo)
                ->preservingOriginal()
                ->toMediaCollection();
        }
        //
    }
}