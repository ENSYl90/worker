<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Category;
use App\Models\VendorType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Amenity::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get booking vendor type
        $bookingVendorType = VendorType::whereSlug('booking')->first();

        if (!$bookingVendorType) {
            $this->command->error('Booking vendor type not found. Please run VendorTypesTableSeeder first.');
            return;
        }


        // Seed amenities
        $this->seedGeneralAmenities();
        $this->seedRoomAmenities();
        $this->seedBathroomAmenities();
        $this->seedKitchenAmenities();
        $this->seedEntertainmentAmenities();
        $this->seedOutdoorAmenities();
        $this->seedSafetyAmenities();
        $this->seedAccessibilityAmenities();
        $this->seedServicesAmenities();

        $this->command->info('Amenities seeded successfully!');
    }


    private function seedGeneralAmenities()
    {
        $amenities = [
            ["name" => "Free WiFi", "icon" => "wifi-01"],
            ["name" => "Air Conditioning", "icon" => "air-conditioner"],
            ["name" => "Heating", "icon" => "temperature"],
            ["name" => "Hot Water", "icon" => "hot-tube"],
            ["name" => "Elevator", "icon" => "lift-truck"],
            ["name" => "Parking", "icon" => "parking"],
            ["name" => "Free Parking", "icon" => "car-parking-01"],
            ["name" => "Paid Parking", "icon" => "money-receive-02"],
            ["name" => "Electric Vehicle Charging", "icon" => "charging-station"],
            ["name" => "Workspace", "icon" => "desk-02"],
            ["name" => "Smoke Detector", "icon" => "fire-security"],
            ["name" => "Carbon Monoxide Detector", "icon" => "co2"],
        ];

        $this->createAmenities($amenities);
    }

    private function seedRoomAmenities()
    {
        $amenities = [
            ["name" => "King Bed", "icon" => "bed"],
            ["name" => "Queen Bed", "icon" => "bed-double"],
            ["name" => "Single Bed", "icon" => "bed-single-01"],
            ["name" => "Bunk Bed", "icon" => "bed-bunk"],
            ["name" => "Sofa Bed", "icon" => "sofa-single"],
            ["name" => "Wardrobe", "icon" => "wardrobe-01"],
            ["name" => "Hangers", "icon" => "hanger"],
            ["name" => "Iron", "icon" => "iron-01"],
            ["name" => "Ironing Board", "icon" => "ironing"],
            ["name" => "Hair Dryer", "icon" => "hair-dryer"],
            ["name" => "Safe", "icon" => "safe"],
            ["name" => "Desk", "icon" => "desk"],
            ["name" => "Chair", "icon" => "chair-01"],
            ["name" => "Blackout Curtains", "icon" => "curtains"],
            ["name" => "Alarm Clock", "icon" => "alarm-clock"],
        ];

        $this->createAmenities($amenities);
    }

    private function seedBathroomAmenities()
    {
        $amenities = [
            ["name" => "Private Bathroom", "icon" => "bathroom"],
            ["name" => "Shared Bathroom", "icon" => "door-02"],
            ["name" => "Shower", "icon" => "shower"],
            ["name" => "Bathtub", "icon" => "bath-tub"],
            ["name" => "Hot Tub", "icon" => "hot-tube"],
            ["name" => "Towels", "icon" => "towels"],
            ["name" => "Toiletries", "icon" => "toilet"],
            ["name" => "Shampoo", "icon" => "shampoo"],
            ["name" => "Body Soap", "icon" => "soap-01"],
            ["name" => "Toilet Paper", "icon" => "toilet-paper"],
            ["name" => "Bidet", "icon" => "toilet-01"],
            ["name" => "Slippers", "icon" => "slippers-01"],
            ["name" => "Bathrobe", "icon" => "hanger"],
        ];

        $this->createAmenities($amenities);
    }

    private function seedKitchenAmenities()
    {
        $amenities = [
            ["name" => "Kitchen", "icon" => "fridge"],
            ["name" => "Kitchenette", "icon" => "restaurant-03"],
            ["name" => "Refrigerator", "icon" => "fridge"],
            ["name" => "Microwave", "icon" => "microwave"],
            ["name" => "Oven", "icon" => "oven"],
            ["name" => "Stove", "icon" => "stove"],
            ["name" => "Dishwasher", "icon" => "dishwasher"],
            ["name" => "Coffee Maker", "icon" => "coffee-beans"],
            ["name" => "Electric Kettle", "icon" => "kettle"],
            ["name" => "Toaster", "icon" => "toast"],
            ["name" => "Dining Table", "icon" => "dining-table"],
            ["name" => "Dishes and Silverware", "icon" => "restaurant-01"],
            ["name" => "Cooking Basics", "icon" => "chef-hat"],
            ["name" => "Wine Glasses", "icon" => "wine-glass"],
            ["name" => "Blender", "icon" => "blender"],
        ];

        $this->createAmenities($amenities);
    }

    private function seedEntertainmentAmenities()
    {
        $amenities = [
            ["name" => "TV", "icon" => "television-table"],
            ["name" => "Cable TV", "icon" => "tv-01"],
            ["name" => "Smart TV", "icon" => "smart-tv"],
            ["name" => "Netflix", "icon" => "netflix"],
            ["name" => "Amazon Prime", "icon" => "amazon"],
            ["name" => "Sound System", "icon" => "speaker-01"],
            ["name" => "Bluetooth Speaker", "icon" => "bluetooth"],
            ["name" => "Books and Reading Material", "icon" => "book-02"],
            ["name" => "Board Games", "icon" => "game"],
            ["name" => "Video Games", "icon" => "game-controller-02"],
            ["name" => "Piano", "icon" => "piano"],
            ["name" => "Guitar", "icon" => "guitar"],
        ];

        $this->createAmenities($amenities);
    }

    private function seedOutdoorAmenities()
    {
        $amenities = [
            ["name" => "Balcony", "icon" => "balcony"],
            ["name" => "Patio", "icon" => "parasol"],
            ["name" => "Garden", "icon" => "plant-03"],
            ["name" => "Terrace", "icon" => "terrace"],
            ["name" => "BBQ Grill", "icon" => "bbq-grill"],
            ["name" => "Outdoor Furniture", "icon" => "chair-02"],
            ["name" => "Pool", "icon" => "swimming-pool-01"],
            ["name" => "Private Pool", "icon" => "swimming-pool-02"],
            ["name" => "Shared Pool", "icon" => "swimming"],
            ["name" => "Beach Access", "icon" => "beach"],
            ["name" => "Beachfront", "icon" => "beach-02"],
            ["name" => "Lake Access", "icon" => "lake"],
            ["name" => "Mountain View", "icon" => "mountain"],
            ["name" => "City View", "icon" => "city-01"],
            ["name" => "Ocean View", "icon" => "waves"],
            ["name" => "Garden View", "icon" => "tree-01"],
        ];

        $this->createAmenities($amenities);
    }

    private function seedSafetyAmenities()
    {
        $amenities = [
            ["name" => "First Aid Kit", "icon" => "first-aid-kit"],
            ["name" => "Fire Extinguisher", "icon" => "fire"],
            ["name" => "Security Cameras", "icon" => "cctv-camera"],
            ["name" => "24-Hour Security", "icon" => "security"],
            ["name" => "Lockbox", "icon" => "lock"],
            ["name" => "Smart Lock", "icon" => "smart-lock"],
            ["name" => "Keypad Lock", "icon" => "key-01"],
            ["name" => "Emergency Exit", "icon" => "door-01"],
            ["name" => "Window Guards", "icon" => "window"],
            ["name" => "Gated Community", "icon" => "gate"],
        ];

        $this->createAmenities($amenities);
    }

    private function seedAccessibilityAmenities()
    {
        $amenities = [
            ["name" => "Wheelchair Accessible", "icon" => "wheelchair"],
            ["name" => "Step-Free Access", "icon" => "stairs"],
            ["name" => "Wide Doorways", "icon" => "door"],
            ["name" => "Accessible Bathroom", "icon" => "bathroom"],
            ["name" => "Grab Rails", "icon" => "hanger"],
            ["name" => "Elevator Access", "icon" => "lift-truck"],
            ["name" => "Ground Floor Access", "icon" => "building-01"],
            ["name" => "Accessible Parking", "icon" => "car-parking-02"],
        ];

        $this->createAmenities($amenities);
    }

    private function seedServicesAmenities()
    {
        $amenities = [
            ["name" => "Self Check-In", "icon" => "tick-02"],
            ["name" => "24-Hour Check-In", "icon" => "time-04"],
            ["name" => "Luggage Storage", "icon" => "package-open"],
            ["name" => "Concierge Service", "icon" => "service"],
            ["name" => "Room Service", "icon" => "room-service"],
            ["name" => "Daily Housekeeping", "icon" => "cleaning"],
            ["name" => "Laundry Service", "icon" => "washing-machine-01"],
            ["name" => "Washer", "icon" => "washing-machine-02"],
            ["name" => "Dryer", "icon" => "tumble-dryer"],
            ["name" => "Free Breakfast", "icon" => "restaurant-02"],
            ["name" => "Breakfast Available", "icon" => "croissant"],
            ["name" => "Restaurant", "icon" => "restaurant-table"],
            ["name" => "Bar", "icon" => "drink"],
            ["name" => "Gym", "icon" => "gym-01"],
            ["name" => "Fitness Center", "icon" => "gym-02"],
            ["name" => "Spa", "icon" => "spa"],
            ["name" => "Sauna", "icon" => "sauna"],
            ["name" => "Jacuzzi", "icon" => "hot-tube"],
            ["name" => "Massage Services", "icon" => "massage"],
            ["name" => "Pets Allowed", "icon" => "dog"],
            ["name" => "Pet Friendly", "icon" => "cat"],
            ["name" => "Long-Term Stays", "icon" => "calendar-03"],
            ["name" => "Suitable for Children", "icon" => "baby-01"],
            ["name" => "Family Friendly", "icon" => "family"],
            ["name" => "Crib", "icon" => "baby-bed-01"],
            ["name" => "High Chair", "icon" => "chair-01"],
            ["name" => "Children's Books and Toys", "icon" => "toy-train"],
            ["name" => "Smoking Allowed", "icon" => "cigarette"],
            ["name" => "Non-Smoking", "icon" => "no-smoking"],
        ];

        $this->createAmenities($amenities);
    }

    private function createAmenities($amenities)
    {
        foreach ($amenities as $amenityData) {
            // Create or get amenity (without category_id)
            $amenity = Amenity::firstOrCreate(
                ['slug' => Str::slug($amenityData['name'])],
                [
                    'name' => $amenityData['name'],
                    'is_active' => true,
                ]
            );



            // Download and attach icon from Hugeicons (only if not already done)
            if (isset($amenityData['icon']) && !$amenity->hasMedia()) {
                $tempPath = downloadHugeicon($amenityData['icon']);

                if ($tempPath) {
                    // Store the icon in media collection
                    $amenity->addMedia($tempPath)
                        ->preservingOriginal()
                        ->toMediaCollection();

                    cleanupTempIcon($tempPath);
                }
            }

            $this->command->info("  ✓ Created amenity: {$amenityData['name']}");
        }
    }
}
