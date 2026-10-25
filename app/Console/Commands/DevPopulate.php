<?php

namespace App\Console\Commands;

use App\Models\Vendor;
use App\Models\VendorType;
use App\Models\Product;
use App\Models\Service;
use App\Models\Category;
use App\Models\Subcategory;
use App\Support\DemoCatalog;
use App\Traits\MediaModelConnectorTrait;
use Illuminate\Console\Command;

class DevPopulate extends Command
{
    use MediaModelConnectorTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:gen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dev populate vendor and product data';

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

        $useSlugs = ["food", "grocery", "commerce", "pharmacy", "service"];

        $foundVendorTypesId = VendorType::whereIn('slug', $useSlugs)->pluck('id');
        if ($this->confirm("Delete existing vendors?", false)) {
            Vendor::whereIn('vendor_type_id', $foundVendorTypesId)->delete();
            $this->info('Vendors Deleted');
        }
        if ($this->confirm("Delete existing products?", false)) {
            \Schema::disableForeignKeyConstraints();
            Product::truncate();
            Service::truncate();
            \Schema::enableForeignKeyConstraints();
            $this->info('Products and services deleted');
        }

        foreach ($useSlugs as $slug) {
            $this->info("Starting Slug:: {$slug}");
            $foundVendorType = VendorType::where('slug', $slug)->first();
            if (!$foundVendorType) {
                $this->warn("Vendor type not found for slug: {$slug}");
                continue;
            }

            $vendorDataSet = DemoCatalog::vendorsFor($slug);
            $this->info("Creating " . count($vendorDataSet) . " demo vendors for {$slug}");
            foreach ($vendorDataSet as $vendorData) {
                $vendor = Vendor::updateOrCreate(
                    [
                        'name' => $vendorData['name'],
                        'vendor_type_id' => $foundVendorType->id,
                    ],
                    $this->vendorPayload($vendorData, $foundVendorType->id)
                );
                $this->setVendorImages($vendor);

                if ($slug === 'service') {
                    $this->syncServices($vendor, $vendorData['services']);
                } else {
                    $this->syncProducts($vendor, $vendorData['products']);
                }
            }
        }

        return 0;
    }

    private function vendorPayload(array $vendorData, int $vendorTypeId): array
    {
        $faker = \Faker\Factory::create();

        return [
            'description' => $vendorData['description'],
            'delivery_fee' => rand(5, 60),
            'delivery_range' => 40075,
            'tax' => rand(0, 1),
            'phone' => preg_replace('/[^0-9+]/', '', $faker->phoneNumber),
            'email' => $faker->safeEmail,
            'address' => $faker->address,
            'latitude' => $faker->latitude(),
            'longitude' => $faker->longitude(),
            'pickup' => 1,
            'delivery' => 1,
            'is_active' => 1,
            'vendor_type_id' => $vendorTypeId,
        ];
    }

    private function syncProducts(Vendor $vendor, array $products): void
    {
        Product::where('vendor_id', $vendor->id)->delete();

        foreach ($products as $productData) {
            $product = new Product();
            $product->name = $productData['name'];
            $product->description = $productData['description'];
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
            $this->info("Product:: {$product->name}");
        }
    }

    private function syncServices(Vendor $vendor, array $services): void
    {
        $category = Category::firstOrCreate([
            'vendor_type_id' => $vendor->vendor_type_id,
            'name' => 'General',
        ], [
            'is_active' => 1,
        ]);

        $subcategory = Subcategory::firstOrCreate([
            'category_id' => $category->id,
            'name' => 'General',
        ], [
            'is_active' => 1,
        ]);

        Service::where('vendor_id', $vendor->id)->delete();

        foreach ($services as $serviceData) {
            $service = new Service();
            $service->name = $serviceData['name'];
            $service->description = $serviceData['description'];
            $service->price = rand(20, 150);
            $service->is_active = 1;
            $service->duration = "fixed";
            $service->vendor_id = $vendor->id;
            $service->category_id = $category->id;
            $service->subcategory_id = $subcategory->id;
            $service->save();
            $this->setProductImages($service);
            $this->info("Service:: {$service->name}");
        }
    }
}
