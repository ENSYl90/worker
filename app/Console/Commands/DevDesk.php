<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Commission;
use App\Models\Vendor;
use App\Models\VendorType;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\ProductReview;
use App\Support\DemoCatalog;
use App\Traits\MediaModelConnectorTrait;
use Illuminate\Console\Command;

class DevDesk extends Command
{
    use MediaModelConnectorTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:desk {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run dev commands for some tasks';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $action = $this->argument('action');

        if ($action == "time") {
            if (!\App::environment('production')) {
                //generate random prepare time and delivery time for vendors
                $vendors = Vendor::all();
                foreach ($vendors as $vendor) {

                    //prepare time range or not
                    if (rand(0, 1)) {
                        $vendor->prepare_time = "" . rand(10, 30) . " - " . rand(60, 90) . "";
                    } else {
                        $vendor->prepare_time = "" . rand(20, 90) . "";
                    }

                    //delivery time range or not
                    if (rand(0, 1)) {
                        $vendor->delivery_time = "" . rand(10, 30) . " - " . rand(60, 90) . "";
                    } else {
                        $vendor->delivery_time = "" . rand(20, 90) . "";
                    }

                    //
                    $vendor->save();
                }
            }
        }

        if ($action == "commerce") {
            //generate data to test out e-commerce
            if (!\App::environment('production')) {
                //
                $commerceVendorType = VendorType::where('slug', 'commerce')->first();
                if (!$commerceVendorType) {
                    $this->error("Commerce vendor type not found");
                    return 0;
                }

                $vendorIds = Vendor::where('vendor_type_id', $commerceVendorType->id)->pluck('id');
                Product::whereIn('vendor_id', $vendorIds)->delete();
                Vendor::where('vendor_type_id', $commerceVendorType->id)->delete();

                $faker = \Faker\Factory::create();
                foreach (DemoCatalog::vendorsFor('commerce') as $vendorData) {
                    $vendor = new Vendor();
                    $vendor->name = $vendorData['name'];
                    $vendor->description = $vendorData['description'];
                    $vendor->delivery_fee = rand(5, 60);
                    $vendor->delivery_range = 40075;
                    $vendor->phone = preg_replace('/[^0-9+]/', '', $faker->phoneNumber);
                    $vendor->email = $faker->safeEmail;
                    $vendor->address = $faker->address;
                    $vendor->latitude = $faker->latitude();
                    $vendor->longitude = $faker->longitude();
                    $vendor->tax = rand(0, 1);
                    $vendor->pickup = 1;
                    $vendor->delivery = 1;
                    $vendor->is_active = 1;
                    $vendor->vendor_type_id = $commerceVendorType->id;
                    $vendor->save();
                    $this->setVendorImages($vendor);

                    foreach ($vendorData['products'] as $productData) {
                        $product = new Product();
                        $product->name = $productData["name"];
                        $product->description = $productData["description"];
                        $product->price = rand(8, 80);
                        $product->discount_price = rand(0, $product->price);
                        $product->capacity = "";
                        $product->unit = "";
                        $product->package_count = 1;
                        $product->featured = rand(0, 1);
                        $product->deliverable = 1;
                        $product->is_active = 1;
                        $product->approved = true;
                        $product->vendor_id = $vendor->id;
                        $product->save();
                        $this->setProductImages($product);
                    }
                }
            }
        }

        //recalculate admin commission
        if ($action == "commission") {
            $generalVendorCommission = setting('vendorsCommission', "0");
            $generalDriverCommission = setting('driversCommission', "0");
            $commissions = Commission::get();
            foreach ($commissions as $commission) {
                //admin vendor commission
                if ($commission->order->vendor != null) {
                    $vendorCommission = $commission->order->vendor->commission;
                    if (empty($vendorCommission)) {
                        $vendorCommission = $generalVendorCommission;
                    }
                    //get system commission in amount from the order subtotal
                    $systemCommission = ($vendorCommission / 100) * $commission->order->sub_total;
                    $commission->vendor_commission = $systemCommission;
                }

                //admin driver commission
                if (!empty($commission->order->driver)) {
                    $driver = $commission->order->driver;
                    //
                    if (empty($driver->commission)) {
                        $driver->commission = $generalDriverCommission;
                    }
                    //driver commission from delivery fee + tip from customer
                    if (!empty($commission->order->taxi_order)) {
                        $earnedAmount = ($driver->commission / 100) * $commission->order->total;
                    } else {
                        $earnedAmount = (($driver->commission / 100) * $commission->order->delivery_fee) + $commission->order->tip;
                    }
                    $systemDriverCommission = $commission->order->delivery_fee - $earnedAmount;
                    $commission->driver_commission = $systemDriverCommission > $commission->order->delivery_fee ? $systemDriverCommission : $commission->order->delivery_fee;
                }

                $commission->save();
            }
        }

        //review generate random product rating
        if ($action == "rating") {

            ProductReview::whereNotNull("id")->delete();
            $products = Product::select('id')->get();
            // $products = Product::select('id')->limit(1)->get();
            $orderId = Order::latest()->first()->id ?? 0;
            $reviews = [
                "Bad product",
                "Not what i wanted",
                "Decent enough",
                "Good product, just as expected",
                "No issue at all from purchase to delivery/pickup. Thanks for the great service",
            ];
            //
            foreach ($products as $product) {
                //times the product should be rated
                $times = rand(2, 10);

                for ($i = 0; $i < $times; $i++) {
                    $client = User::select('id')->client()->inRandomOrder()->first();
                    $rating = rand(1, 5);
                    $productReview = new ProductReview();
                    $productReview->user_id = $client->id;
                    $productReview->product_id = $product->id;
                    $productReview->order_id = $orderId;
                    $productReview->rating = $rating;
                    $productReview->review = $reviews[$rating - 1];
                    $productReview->save();
                }
            }
        }

        return 0;
    }
}
