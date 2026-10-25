<?php

namespace App\Console\Commands;

use App\Models\Vendor;
use App\Models\Category;
use App\Models\Product;
use App\Traits\MediaModelConnectorTrait;
use Illuminate\Console\Command;

class DevImagePopulate extends Command
{
    use MediaModelConnectorTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:img:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dev populate model images';

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

        $confirmText = __('Do you wish to continue?');
        if (!\App::environment('production')) {
            $confirmText = __('In Production, do you wish to continue?');
        }


        if (!$this->confirm($confirmText, false)) {
            $this->error('Operation cancelled');
            return 0;
        }


        //
        $products = Product::doesntHave('media')->get();
        foreach ($products as $product) {

            try {
                $this->setProductImages($product);

                if (!$product->fresh()->hasMedia()) {
                    $link = "https://source.unsplash.com/420x240/?" . urlencode($product->name);
                    $product->addMediaFromUrl($link)->toMediaCollection();
                }
            } catch (\Exception $ex) {
                logger("unsplash Error", [$ex->getMessage()]);
            }
        }

        //
        $categories = Category::doesntHave('media')->get();
        foreach ($categories as $category) {

            try {
                $link = "https://source.unsplash.com/420x240/?" . urlencode("category,{$category->name}");
                $category->addMediaFromUrl($link)->toMediaCollection();
            } catch (\Exception $ex) {
                logger("unsplash Error", [$ex->getMessage()]);
            }
        }

        //vendors
        $vendors = Vendor::doesntHave('media')->get();
        foreach ($vendors as $vendor) {

            try {
                $this->setVendorImages($vendor);

                if (!$vendor->fresh()->hasMedia("logo")) {
                    $lLink = "https://source.unsplash.com/240x240/?" . urlencode("store logo {$vendor->name}");
                    $vendor->addMediaFromUrl($lLink)->toMediaCollection("logo");
                }

                if (!$vendor->fresh()->hasMedia("feature_image")) {
                    $fLink = "https://source.unsplash.com/420x240/?" . urlencode("storefront {$vendor->name}");
                    $vendor->addMediaFromUrl($fLink)->toMediaCollection("feature_image");
                }
            } catch (\Exception $ex) {
                logger("Error Vendor", [$ex->getMessage()]);
            }
        }




        return 0;
    }
}
