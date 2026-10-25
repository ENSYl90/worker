<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\VendorType;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::truncate();
        Subcategory::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get all vendor types
        $vendorTypes = [
            'parcel' => $this->getParcelCategories(),
            'food' => $this->getFoodCategories(),
            'grocery' => $this->getGroceryCategories(),
            'pharmacy' => $this->getPharmacyCategories(),
            'service' => $this->getServiceCategories(),
            'taxi' => $this->getTaxiCategories(),
            'booking' => $this->getBookingCategories(),
            'commerce' => $this->getCommerceCategories(),
        ];

        // Seed categories for each vendor type
        foreach ($vendorTypes as $slug => $categories) {
            $vendorType = VendorType::whereSlug($slug)->first();

            if ($vendorType) {
                $this->syncCategories($categories, $vendorType);
                $this->command->info("✓ Created categories for: {$vendorType->name}");
            } else {
                $this->command->warn("✗ Vendor type not found: {$slug}");
            }
        }

        $this->command->info('All categories seeded successfully!');
    }

    private function getParcelCategories(): array
    {
        return [
            [
                "name" => "Documents",
                "icon" => "file-02",
                "subs" => [
                    ["name" => "Legal Documents", "icon" => "legal-01"],
                    ["name" => "Business Documents", "icon" => "briefcase-02"],
                    ["name" => "Personal Documents", "icon" => "user-account"],
                    ["name" => "Certificates", "icon" => "certificate-01"],
                ],
            ],
            [
                "name" => "Packages",
                "icon" => "package",
                "subs" => [
                    ["name" => "Small Packages", "icon" => "box"],
                    ["name" => "Medium Packages", "icon" => "package"],
                    ["name" => "Large Packages", "icon" => "package-open"],
                    ["name" => "Fragile Items", "icon" => "glass"],
                ],
            ],
            [
                "name" => "Electronics",
                "icon" => "computer",
                "subs" => [
                    ["name" => "Mobile Phones", "icon" => "mobile-programming-01"],
                    ["name" => "Laptops", "icon" => "laptop"],
                    ["name" => "Gadgets", "icon" => "smart-watch-01"],
                ],
            ],
            [
                "name" => "Food Delivery",
                "icon" => "food-truck",
                "subs" => [
                    ["name" => "Hot Meals", "icon" => "hot-air-balloon"],
                    ["name" => "Cold Items", "icon" => "ice-cream-01"],
                    ["name" => "Beverages", "icon" => "coffee-01"],
                ],
            ],
            [
                "name" => "Fashion & Clothing",
                "icon" => "shopping-bag-01",
                "subs" => [
                    ["name" => "Apparel", "icon" => "tie"],
                    ["name" => "Shoes", "icon" => "shoe-01"],
                    ["name" => "Accessories", "icon" => "watch-01"],
                ],
            ],
            [
                "name" => "Gifts & Flowers",
                "icon" => "gift",
                "subs" => [
                    ["name" => "Gift Boxes", "icon" => "gift-card"],
                    ["name" => "Flowers", "icon" => "flower-pot"],
                    ["name" => "Cakes", "icon" => "cake-03"],
                ],
            ],
        ];
    }

    private function getFoodCategories(): array
    {
        return [
            [
                "name" => "Fast Food",
                "icon" => "pizza-01",
                "subs" => [
                    ["name" => "Burgers", "icon" => "hamburger-01"],
                    ["name" => "Pizza", "icon" => "pizza-01"],
                    ["name" => "Fried Chicken", "icon" => "chicken-thighs"],
                    ["name" => "Sandwiches", "icon" => "sandwich"],
                ],
            ],
            [
                "name" => "Asian Cuisine",
                "icon" => "sushi-01",
                "subs" => [
                    ["name" => "Chinese", "icon" => "chinese-temple"],
                    ["name" => "Japanese", "icon" => "sushi-01"],
                    ["name" => "Thai", "icon" => "restaurant-02"],
                    ["name" => "Indian", "icon" => "taj-mahal"],
                ],
            ],
            [
                "name" => "Healthy & Organic",
                "icon" => "salad",
                "subs" => [
                    ["name" => "Salads", "icon" => "salad"],
                    ["name" => "Smoothies", "icon" => "blender"],
                    ["name" => "Vegan", "icon" => "leaf-01"],
                    ["name" => "Protein Bowls", "icon" => "bowl"],
                ],
            ],
            [
                "name" => "Desserts & Bakery",
                "icon" => "cake-03",
                "subs" => [
                    ["name" => "Cakes", "icon" => "cake-01"],
                    ["name" => "Pastries", "icon" => "cupcake-01"],
                    ["name" => "Ice Cream", "icon" => "ice-cream-03"],
                    ["name" => "Donuts", "icon" => "doughnut"],
                ],
            ],
            [
                "name" => "Beverages",
                "icon" => "coffee-beans",
                "subs" => [
                    ["name" => "Coffee", "icon" => "coffee-01"],
                    ["name" => "Tea", "icon" => "tea"],
                    ["name" => "Juice", "icon" => "juice"],
                    ["name" => "Soft Drinks", "icon" => "drink"],
                ],
            ],
            [
                "name" => "Local & Traditional",
                "icon" => "restaurant-01",
                "subs" => [
                    ["name" => "Local Dishes", "icon" => "restaurant-table"],
                    ["name" => "Street Food", "icon" => "food-truck"],
                    ["name" => "Home Cooked", "icon" => "chef-hat"],
                ],
            ],
        ];
    }

    private function getGroceryCategories(): array
    {
        return [
            [
                "name" => "Fruits & Vegetables",
                "icon" => "apple",
                "subs" => [
                    ["name" => "Fresh Fruits", "icon" => "apple"],
                    ["name" => "Fresh Vegetables", "icon" => "organic-food"],
                    ["name" => "Leafy Greens", "icon" => "leaf-02"],
                    ["name" => "Exotic Fruits", "icon" => "cherry"],
                ],
            ],
            [
                "name" => "Dairy & Eggs",
                "icon" => "milk-bottle",
                "subs" => [
                    ["name" => "Milk", "icon" => "milk-carton"],
                    ["name" => "Cheese", "icon" => "cheese"],
                    ["name" => "Eggs", "icon" => "egg"],
                    ["name" => "Yogurt", "icon" => "yogurt"],
                ],
            ],
            [
                "name" => "Meat & Seafood",
                "icon" => "chicken-thighs",
                "subs" => [
                    ["name" => "Chicken", "icon" => "chicken-thighs"],
                    ["name" => "Beef", "icon" => "meat"],
                    ["name" => "Fish", "icon" => "fish"],
                    ["name" => "Seafood", "icon" => "shrimp"],
                ],
            ],
            [
                "name" => "Bakery & Bread",
                "icon" => "bread-01",
                "subs" => [
                    ["name" => "Fresh Bread", "icon" => "bread-02"],
                    ["name" => "Pastries", "icon" => "croissant"],
                    ["name" => "Buns & Rolls", "icon" => "bread-03"],
                ],
            ],
            [
                "name" => "Beverages",
                "icon" => "drink",
                "subs" => [
                    ["name" => "Water", "icon" => "water-bottle"],
                    ["name" => "Soft Drinks", "icon" => "drink"],
                    ["name" => "Juice", "icon" => "juice"],
                    ["name" => "Energy Drinks", "icon" => "energy-ellipse"],
                ],
            ],
            [
                "name" => "Snacks & Packaged Foods",
                "icon" => "chips",
                "subs" => [
                    ["name" => "Chips & Crisps", "icon" => "chips"],
                    ["name" => "Cookies", "icon" => "cookie"],
                    ["name" => "Candy", "icon" => "candy"],
                    ["name" => "Nuts", "icon" => "peanut"],
                ],
            ],
            [
                "name" => "Household Essentials",
                "icon" => "cleaning-bucket",
                "subs" => [
                    ["name" => "Cleaning Supplies", "icon" => "cleaning"],
                    ["name" => "Paper Products", "icon" => "toilet-paper"],
                    ["name" => "Laundry", "icon" => "washing-machine-01"],
                ],
            ],
        ];
    }

    private function getPharmacyCategories(): array
    {
        return [
            [
                "name" => "Prescription Medicines",
                "icon" => "medicine-01",
                "subs" => [
                    ["name" => "Antibiotics", "icon" => "capsule"],
                    ["name" => "Pain Relief", "icon" => "medicine-02"],
                    ["name" => "Chronic Disease", "icon" => "injection"],
                    ["name" => "Vaccines", "icon" => "syringe"],
                ],
            ],
            [
                "name" => "Over-the-Counter",
                "icon" => "pharmacy",
                "subs" => [
                    ["name" => "Cold & Flu", "icon" => "thermometer"],
                    ["name" => "Vitamins", "icon" => "vitamin"],
                    ["name" => "Supplements", "icon" => "supplement"],
                    ["name" => "First Aid", "icon" => "first-aid-kit"],
                ],
            ],
            [
                "name" => "Personal Care",
                "icon" => "lotion",
                "subs" => [
                    ["name" => "Skincare", "icon" => "skincare"],
                    ["name" => "Oral Care", "icon" => "tooth"],
                    ["name" => "Hair Care", "icon" => "shampoo"],
                ],
            ],
            [
                "name" => "Medical Devices",
                "icon" => "stethoscope",
                "subs" => [
                    ["name" => "Blood Pressure Monitors", "icon" => "stethoscope-02"],
                    ["name" => "Thermometers", "icon" => "thermometer"],
                    ["name" => "Glucose Meters", "icon" => "medical-file"],
                ],
            ],
            [
                "name" => "Baby & Mother Care",
                "icon" => "baby-01",
                "subs" => [
                    ["name" => "Baby Formula", "icon" => "baby-bottle"],
                    ["name" => "Diapers", "icon" => "baby-02"],
                    ["name" => "Baby Care", "icon" => "baby-girl-dress"],
                ],
            ],
            [
                "name" => "Health & Wellness",
                "icon" => "wellness",
                "subs" => [
                    ["name" => "Fitness Supplements", "icon" => "gym-01"],
                    ["name" => "Herbal Products", "icon" => "mortar"],
                    ["name" => "Nutrition", "icon" => "apple"],
                ],
            ],
        ];
    }

    private function getServiceCategories(): array
    {
        return [
            [
                "name" => "Home Services",
                "icon" => "home-05",
                "subs" => [
                    ["name" => "Cleaning", "icon" => "cleaning-bucket"],
                    ["name" => "Plumbing", "icon" => "plumber"],
                    ["name" => "Electrical", "icon" => "electricity"],
                    ["name" => "Painting", "icon" => "paint-brush-02"],
                    ["name" => "Carpentry", "icon" => "saw"],
                ],
            ],
            [
                "name" => "Beauty & Wellness",
                "icon" => "spa",
                "subs" => [
                    ["name" => "Hair Salon", "icon" => "hair-dryer"],
                    ["name" => "Spa & Massage", "icon" => "spa"],
                    ["name" => "Nail Salon", "icon" => "nail-polish"],
                    ["name" => "Makeup Artist", "icon" => "makeup-brush-01"],
                ],
            ],
            [
                "name" => "Fitness & Health",
                "icon" => "gym-01",
                "subs" => [
                    ["name" => "Personal Training", "icon" => "gym-02"],
                    ["name" => "Yoga Classes", "icon" => "yoga-01"],
                    ["name" => "Nutrition Coaching", "icon" => "apple"],
                ],
            ],
            [
                "name" => "Automotive",
                "icon" => "car-01",
                "subs" => [
                    ["name" => "Car Wash", "icon" => "car-wash"],
                    ["name" => "Auto Repair", "icon" => "mechanic"],
                    ["name" => "Oil Change", "icon" => "oil-barrel"],
                    ["name" => "Tire Service", "icon" => "wheel"],
                ],
            ],
            [
                "name" => "Professional Services",
                "icon" => "briefcase-02",
                "subs" => [
                    ["name" => "Legal Services", "icon" => "legal-02"],
                    ["name" => "Accounting", "icon" => "calculator"],
                    ["name" => "Consulting", "icon" => "consulting"],
                    ["name" => "Photography", "icon" => "camera-01"],
                ],
            ],
            [
                "name" => "Education & Tutoring",
                "icon" => "graduation-scroll",
                "subs" => [
                    ["name" => "Academic Tutoring", "icon" => "book-02"],
                    ["name" => "Music Lessons", "icon" => "music-note-01"],
                    ["name" => "Language Classes", "icon" => "translation"],
                    ["name" => "Art Classes", "icon" => "paint-board"],
                ],
            ],
        ];
    }

    private function getTaxiCategories(): array
    {
        return [
            [
                "name" => "Economy",
                "icon" => "car-02",
                "subs" => [
                    ["name" => "Standard Car", "icon" => "car-01"],
                    ["name" => "Shared Ride", "icon" => "car-parking-01"],
                    ["name" => "Budget Ride", "icon" => "money-01"],
                ],
            ],
            [
                "name" => "Premium",
                "icon" => "car-03",
                "subs" => [
                    ["name" => "Luxury Car", "icon" => "car-04"],
                    ["name" => "Business Class", "icon" => "briefcase-01"],
                    ["name" => "SUV", "icon" => "car-parking-02"],
                ],
            ],
            [
                "name" => "Special Services",
                "icon" => "taxi",
                "subs" => [
                    ["name" => "Airport Transfer", "icon" => "airplane-02"],
                    ["name" => "Long Distance", "icon" => "route-01"],
                    ["name" => "Hourly Rental", "icon" => "time-02"],
                ],
            ],
            [
                "name" => "Family & Groups",
                "icon" => "user-group",
                "subs" => [
                    ["name" => "Family Car", "icon" => "family"],
                    ["name" => "Van/Minibus", "icon" => "van"],
                    ["name" => "XL Capacity", "icon" => "bus-02"],
                ],
            ],
            [
                "name" => "Two Wheeler",
                "icon" => "motorcycle-01",
                "subs" => [
                    ["name" => "Bike Taxi", "icon" => "motorcycle-02"],
                    ["name" => "Scooter", "icon" => "scooter"],
                ],
            ],
        ];
    }

    private function getBookingCategories(): array
    {
        return [
            [
                "name" => "Hotels",
                "icon" => "hotel-01",
                "subs" => [
                    ["name" => "Luxury Hotels", "icon" => "crown"],
                    ["name" => "Budget Hotels", "icon" => "money-bag-02"],
                    ["name" => "Business Hotels", "icon" => "briefcase-02"],
                    ["name" => "Boutique Hotels", "icon" => "hotel-02"],
                ],
            ],
            [
                "name" => "Apartments",
                "icon" => "building-03",
                "subs" => [
                    ["name" => "Studio Apartments", "icon" => "home-03"],
                    ["name" => "1 Bedroom", "icon" => "bed-single-01"],
                    ["name" => "2 Bedroom", "icon" => "bed-double"],
                    ["name" => "Penthouse", "icon" => "building-06"],
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
                ],
            ],
            [
                "name" => "Hostels",
                "icon" => "bed-bunk",
                "subs" => [
                    ["name" => "Mixed Dormitory", "icon" => "bed-bunk"],
                    ["name" => "Private Room", "icon" => "door-02"],
                    ["name" => "Capsule Hostel", "icon" => "bed-single-02"],
                ],
            ],
            [
                "name" => "Resorts",
                "icon" => "beach-02",
                "subs" => [
                    ["name" => "Beach Resort", "icon" => "beach"],
                    ["name" => "Spa Resort", "icon" => "spa"],
                    ["name" => "Ski Resort", "icon" => "skiing"],
                    ["name" => "Golf Resort", "icon" => "golf"],
                ],
            ],
            [
                "name" => "Unique Stays",
                "icon" => "tent",
                "subs" => [
                    ["name" => "Treehouse", "icon" => "tree-02"],
                    ["name" => "Houseboat", "icon" => "boat"],
                    ["name" => "Castle", "icon" => "castle-01"],
                    ["name" => "Lighthouse", "icon" => "lighthouse"],
                ],
            ],
        ];
    }

    private function getCommerceCategories(): array
    {
        return [
            [
                "name" => "Electronics",
                "icon" => "computer",
                "subs" => [
                    ["name" => "Smartphones", "icon" => "mobile-programming-02"],
                    ["name" => "Laptops & Computers", "icon" => "laptop"],
                    ["name" => "Cameras", "icon" => "camera-02"],
                    ["name" => "Audio & Headphones", "icon" => "headphones"],
                    ["name" => "Gaming", "icon" => "game-controller-01"],
                ],
            ],
            [
                "name" => "Fashion & Apparel",
                "icon" => "shopping-bag-02",
                "subs" => [
                    ["name" => "Men's Clothing", "icon" => "tie"],
                    ["name" => "Women's Clothing", "icon" => "dress-01"],
                    ["name" => "Kids Clothing", "icon" => "baby-girl-dress"],
                    ["name" => "Shoes & Footwear", "icon" => "shoe-01"],
                    ["name" => "Accessories", "icon" => "watch-01"],
                ],
            ],
            [
                "name" => "Home & Furniture",
                "icon" => "sofa-02",
                "subs" => [
                    ["name" => "Furniture", "icon" => "sofa-01"],
                    ["name" => "Home Decor", "icon" => "lamp"],
                    ["name" => "Kitchen & Dining", "icon" => "restaurant-03"],
                    ["name" => "Bedding", "icon" => "bed"],
                ],
            ],
            [
                "name" => "Sports & Outdoors",
                "icon" => "basketball-01",
                "subs" => [
                    ["name" => "Fitness Equipment", "icon" => "gym-01"],
                    ["name" => "Sports Gear", "icon" => "football"],
                    ["name" => "Camping & Hiking", "icon" => "tent"],
                    ["name" => "Cycling", "icon" => "bicycle"],
                ],
            ],
            [
                "name" => "Beauty & Personal Care",
                "icon" => "makeup-brush-02",
                "subs" => [
                    ["name" => "Skincare", "icon" => "skincare"],
                    ["name" => "Makeup", "icon" => "lipstick"],
                    ["name" => "Fragrances", "icon" => "perfume"],
                    ["name" => "Hair Care", "icon" => "hair-dryer"],
                ],
            ],
            [
                "name" => "Books & Media",
                "icon" => "book-01",
                "subs" => [
                    ["name" => "Books", "icon" => "book-03"],
                    ["name" => "eBooks", "icon" => "book-open-01"],
                    ["name" => "Movies & Music", "icon" => "video-01"],
                ],
            ],
            [
                "name" => "Toys & Baby Products",
                "icon" => "baby-01",
                "subs" => [
                    ["name" => "Toys", "icon" => "toy-train"],
                    ["name" => "Baby Care", "icon" => "baby-bottle"],
                    ["name" => "Kids Furniture", "icon" => "baby-bed-01"],
                ],
            ],
        ];
    }

    private function syncCategories($categories, $vendorType)
    {
        $savedColor = setting('websiteColor', '#246fd0');
        $appColor = new \OzdemirBurak\Iris\Color\Hex($savedColor);
        $primaryColorFaint = $appColor->brighten(70);

        foreach ($categories as $categoryData) {
            $category = new Category();
            $category->name = $categoryData['name'];
            $category->color = $primaryColorFaint ?? "#def7e5";
            $category->is_active = true;
            $category->vendor_type_id = $vendorType->id;
            $category->save();

            // Download and attach icon from Hugeicons
            attachHugeiconToModel($category, $categoryData['icon']);

            // Create subcategories
            if (isset($categoryData['subs'])) {
                foreach ($categoryData['subs'] as $subData) {
                    $subcategory = Subcategory::create([
                        "name" => $subData['name'],
                        "category_id" => $category->id,
                        'is_active' => 1,
                    ]);

                    // Attach icon to subcategory
                    attachHugeiconToModel($subcategory, $subData['icon']);
                }
            }
        }
    }
}
