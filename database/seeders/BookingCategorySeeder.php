<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\VendorType;
use Illuminate\Database\Seeder;

class BookingCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key check for this operation
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Get the booking vendor type
        $bookingVendorType = VendorType::whereSlug('booking')->first();

        if (!$bookingVendorType) {
            $this->command->error('Booking vendor type not found. Please run VendorTypesTableSeeder first.');
            return;
        }

        // Delete existing booking categories
        Category::where('vendor_type_id', $bookingVendorType->id)->delete();

        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Booking categories with Hugeicons
        $categories = [
            [
                "name" => "Hotels",
                "icon" => "hotel-01",
                "subs" => [
                    ["name" => "Luxury Hotels", "icon" => "crown"],
                    ["name" => "Budget Hotels", "icon" => "money-bag-02"],
                    ["name" => "Business Hotels", "icon" => "briefcase-02"],
                    ["name" => "Boutique Hotels", "icon" => "hotel-01"],
                    ["name" => "Resort Hotels", "icon" => "beach-02"],
                    ["name" => "Airport Hotels", "icon" => "airplane-01"],
                    ["name" => "Eco Hotels", "icon" => "leaf-01"],
                ],
            ],
            [
                "name" => "Apartments",
                "icon" => "building-03",
                "subs" => [
                    ["name" => "Studio Apartments", "icon" => "home-03"],
                    ["name" => "1 Bedroom", "icon" => "bed-single-01"],
                    ["name" => "2 Bedroom", "icon" => "bed-double"],
                    ["name" => "3+ Bedroom", "icon" => "building-03"],
                    ["name" => "Serviced Apartments", "icon" => "hotel-bell"],
                    ["name" => "Penthouse", "icon" => "building-06"],
                    ["name" => "Loft Apartments", "icon" => "entrance-stairs"],
                ],
            ],
            [
                "name" => "Vacation Rentals",
                "icon" => "home-05",
                "subs" => [
                    ["name" => "Beach House", "icon" => "beach"],
                    ["name" => "Mountain Cabin", "icon" => "mountain"],
                    ["name" => "Villa", "icon" => "home-06"],
                    ["name" => "Cottage", "icon" => "home-04"],
                    ["name" => "Farmhouse", "icon" => "barns"],
                    ["name" => "Lake House", "icon" => "lake"],
                    ["name" => "Chalet", "icon" => "mountain"],
                ],
            ],
            [
                "name" => "Guest Houses",
                "icon" => "home-06",
                "subs" => [
                    ["name" => "Bed & Breakfast", "icon" => "restaurant-01"],
                    ["name" => "Private Guest House", "icon" => "key-01"],
                    ["name" => "Shared Guest House", "icon" => "user-group"],
                    ["name" => "Family Guest House", "icon" => "man-woman"],
                ],
            ],
            [
                "name" => "Hostels",
                "icon" => "bed-double",
                "subs" => [
                    ["name" => "Mixed Dormitory", "icon" => "bed-bunk"],
                    ["name" => "Female Dormitory", "icon" => "female-symbol"],
                    ["name" => "Male Dormitory", "icon" => "male-symbol"],
                    ["name" => "Private Room", "icon" => "door-02"],
                    ["name" => "Capsule Hostel", "icon" => "bed-single-02"],
                ],
            ],
            [
                "name" => "Resorts",
                "icon" => "beach-02",
                "subs" => [
                    ["name" => "Beach Resort", "icon" => "beach"],
                    ["name" => "Spa Resort", "icon" => "equipment-bench-press"],
                    ["name" => "Ski Resort", "icon" => "ice-skating"],
                    ["name" => "Golf Resort", "icon" => "golf-bat"],
                    ["name" => "All-Inclusive Resort", "icon" => "tick-double-02"],
                    ["name" => "Family Resort", "icon" => "family"],
                ],
            ],
            [
                "name" => "Unique Stays",
                "icon" => "colosseum",
                "subs" => [
                    ["name" => "Treehouse", "icon" => "tree-02"],
                    ["name" => "Houseboat", "icon" => "boat"],
                    ["name" => "Castle", "icon" => "castle-01"],
                    ["name" => "Lighthouse", "icon" => "lighthouse"],
                    ["name" => "Yurt", "icon" => "yurt"],
                    ["name" => "Igloo", "icon" => "snow"],
                    ["name" => "Cave", "icon" => "mountain"],
                ],
            ],
            [
                "name" => "Serviced Accommodation",
                "icon" => "hotel-bell",
                "subs" => [
                    ["name" => "Corporate Housing", "icon" => "office"],
                    ["name" => "Extended Stay", "icon" => "calendar-03"],
                    ["name" => "Furnished Apartments", "icon" => "sofa-01"],
                    ["name" => "Temporary Housing", "icon" => "time-03"],
                ],
            ],
        ];

        $this->syncCategories($categories, $bookingVendorType);

        $this->command->info('Booking categories seeded successfully!');
    }

    private function syncCategories($categories, $vendorType)
    {
        $savedColor = setting('websiteColor', '#246fd0');
        $appColor = new \OzdemirBurak\Iris\Color\Hex($savedColor);
        $primaryColorFaint = $appColor->brighten(70);

        foreach ($categories as $index => $categoryData) {
            $category = new Category();
            $category->name = $categoryData['name'];
            $category->color = $primaryColorFaint ?? "#def7e5";
            $category->is_active = true;
            $category->vendor_type_id = $vendorType->id;
            $category->save();

            // Download and attach icon from Hugeicons
            $this->attachIconFromHugeicons($category, $categoryData['icon']);

            // Create subcategories
            foreach ($categoryData['subs'] as $subData) {
                $subcategory = new Subcategory();
                $subcategory->name = $subData['name'];
                $subcategory->is_active = true;
                $subcategory->category_id = $category->id;
                $subcategory->save();
                // Attach icon to subcategory
                $this->attachIconFromHugeicons($subcategory, $subData['icon']);
            }

            $this->command->info("Created category: {$categoryData['name']} with " . count($categoryData['subs']) . " subcategories");
        }
    }

    private function attachIconFromHugeicons($model, $iconName)
    {
        $modelType = class_basename($model);

        // Use the global helper function to attach the icon
        $success = attachHugeiconToModel($model, $iconName);

        if ($success) {
            $this->command->info("    ✓ Icon downloaded for {$modelType}: {$iconName}");
        } else {
            $this->command->warn("    ✗ Failed to download icon: {$iconName}");
        }
    }
}
