<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\CancellationPolicy;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Vendor;
use App\Models\VendorType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get necessary data
        $vendors = Vendor::whereHas('vendor_type', function ($query) {
            return $query->where('slug', 'booking');
        })->get();

        if ($vendors->isEmpty()) {
            $vendors = $this->generatePropertyVendors();
        }

        $cancellationPolicies = CancellationPolicy::all();
        $amenities = Amenity::all();
        $propertyTypes = PropertyType::all();

        if ($vendors->isEmpty()) {
            $this->command->warn('No vendors found. Please seed vendors first.');

            return;
        }

        // World Renowned Properties
        $properties = [
            [
                'name' => 'The Plaza Hotel',
                'description' => 'The Plaza Hotel is a landmark 20-story luxury hotel and condominium apartment building in the Midtown Manhattan neighborhood of New York City. Since its opening in 1907, it has been an icon of New York luxury, hosting world leaders, celebrities, and legends.',
                'address' => '768 5th Ave, New York',
                'city' => 'New York',
                'state' => 'New York',
                'country' => 'United States',
                'postal_code' => '10019',
                'latitude' => 40.7645,
                'longitude' => -73.9744,
                'max_guests' => 4,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'beds' => 2,
                'square_meters' => 120,
                'check_in_time_start' => '15:00',
                'check_in_time_end' => '23:00',
                'check_out_time' => '11:00',
                'minimum_nights' => 1,
                'maximum_nights' => 30,
                'preparation_time' => 1,
                'is_active' => true,
                'is_instant_booking' => true,
                'house_rules' => "• Formal attire required in certain areas\n• No smoking inside\n• Check-in after 3:00 PM\n• Quiet hours after 11:00 PM",
                'amenities_count' => [10, 20],
                'base_price' => 500,
                'discount_price' => 450,
                'photo_keywords' => ['The Plaza Hotel New York', 'luxury suite', 'landmark hotel'],
            ],
            [
                'name' => 'Burj Al Arab Jumeirah',
                'description' => 'The distinctive sail-shaped silhouette of Burj Al Arab Jumeirah is more than just a stunning hotel, it is a symbol of modern Dubai. Repeatedly voted the world\'s most luxurious hotel, this magnificent destination offers you the finest service and experiences.',
                'address' => 'Jumeirah St, Umm Suqeim 3',
                'city' => 'Dubai',
                'state' => 'Dubai',
                'country' => 'United Arab Emirates',
                'postal_code' => '74147',
                'latitude' => 25.1412,
                'longitude' => 55.1852,
                'max_guests' => 4,
                'bedrooms' => 2,
                'bathrooms' => 3,
                'beds' => 2,
                'square_meters' => 170,
                'check_in_time_start' => '15:00',
                'check_in_time_end' => '00:00',
                'check_out_time' => '12:00',
                'minimum_nights' => 1,
                'maximum_nights' => 14,
                'preparation_time' => 1,
                'is_active' => true,
                'is_instant_booking' => true,
                'house_rules' => "• Private butler service included\n• Dress code: Smart Casual\n• No photography in certain areas\n• Exclusive access regulations apply",
                'amenities_count' => [15, 25],
                'base_price' => 1500,
                'discount_price' => 1200,
                'photo_keywords' => ['Burj Al Arab', 'Dubai luxury hotel', 'seven star hotel'],
            ],
            [
                'name' => 'Marina Bay Sands',
                'description' => 'Experience the world\'s largest infinity pool, award-winning dining, and a wide range of shopping and entertainment at Marina Bay Sands. This iconic integrated resort features three cascading hotel towers topped by the extraordinary Sands SkyPark.',
                'address' => '10 Bayfront Ave',
                'city' => 'Singapore',
                'state' => 'Singapore',
                'country' => 'Singapore',
                'postal_code' => '018956',
                'latitude' => 1.2834,
                'longitude' => 103.8607,
                'max_guests' => 3,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'beds' => 2,
                'square_meters' => 60,
                'check_in_time_start' => '15:00',
                'check_in_time_end' => '22:00',
                'check_out_time' => '11:00',
                'minimum_nights' => 1,
                'maximum_nights' => 30,
                'preparation_time' => 1,
                'is_active' => true,
                'is_instant_booking' => true,
                'house_rules' => "• SkyPark access for guests only\n• Infinity pool rules apply\n• No smoking in corridors\n• Smart casual dress code",
                'amenities_count' => [10, 18],
                'base_price' => 450,
                'discount_price' => 0,
                'photo_keywords' => ['Marina Bay Sands Singapore', 'infinity pool Singapore', 'Sands SkyPark'],
            ],
            [
                'name' => 'The Ritz-Carlton, Paris',
                'description' => 'Overlooking the Place Vendôme, The Ritz Paris is one of the most famous and luxurious hotels in the world. Its suites, decorated in Louis XIV style, have hosted the world’s most famous names, including Coco Chanel and Ernest Hemingway.',
                'address' => '15 Place Vendôme',
                'city' => 'Paris',
                'state' => 'Île-de-France',
                'country' => 'France',
                'postal_code' => '75001',
                'latitude' => 48.8681,
                'longitude' => 2.3289,
                'max_guests' => 2,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'beds' => 1,
                'square_meters' => 45,
                'check_in_time_start' => '15:00',
                'check_in_time_end' => '23:00',
                'check_out_time' => '12:00',
                'minimum_nights' => 1,
                'maximum_nights' => 21,
                'preparation_time' => 1,
                'is_active' => true,
                'is_instant_booking' => false,
                'house_rules' => "• Quiet elegance expected\n• Dress code: Elegant\n• Respect heritage furnishings\n• No flash photography in lobby",
                'amenities_count' => [12, 22],
                'base_price' => 1200,
                'discount_price' => 1000,
                'photo_keywords' => ['Ritz Paris', 'Place Vendome', 'luxury French suite'],
            ],
            [
                'name' => 'Hotel de Paris Monte-Carlo',
                'description' => 'Located on the Place du Casino, the Hôtel de Paris Monte-Carlo is an iconic palace that offers an unparalleled experience in the heart of Monaco. Featuring Michelin-starred restaurants and the legendary Le Bar Américain.',
                'address' => 'Pl. du Casino',
                'city' => 'Monaco',
                'state' => 'Monaco',
                'country' => 'Monaco',
                'postal_code' => '98000',
                'latitude' => 43.7392,
                'longitude' => 7.4278,
                'max_guests' => 4,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'beds' => 2,
                'square_meters' => 90,
                'check_in_time_start' => '15:00',
                'check_in_time_end' => '22:00',
                'check_out_time' => '12:00',
                'minimum_nights' => 1,
                'maximum_nights' => 14,
                'preparation_time' => 1,
                'is_active' => true,
                'is_instant_booking' => false,
                'house_rules' => "• Casino proximity conduct required\n• Formal evening attire\n• Valet service only\n• No professional filming without permit",
                'amenities_count' => [15, 25],
                'base_price' => 850,
                'discount_price' => 0,
                'photo_keywords' => ['Hotel de Paris Monte Carlo', 'Monaco luxury', 'Casino Square Monaco'],
            ],
            [
                'name' => 'Fairmont Château Frontenac',
                'description' => 'The Most Photographed Hotel in the World. This stately castle hotel towers over historic Québec City, offering breathtaking views of the St. Lawrence River. A National Historic Site with over a century of history.',
                'address' => '1 Rue des Carrières',
                'city' => 'Québec City',
                'state' => 'Québec',
                'country' => 'Canada',
                'postal_code' => 'G1R 4P5',
                'latitude' => 46.8120,
                'longitude' => -71.2050,
                'max_guests' => 4,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'beds' => 2,
                'square_meters' => 55,
                'check_in_time_start' => '16:00',
                'check_in_time_end' => '23:00',
                'check_out_time' => '11:00',
                'minimum_nights' => 1,
                'maximum_nights' => 30,
                'preparation_time' => 1,
                'is_active' => true,
                'is_instant_booking' => true,
                'house_rules' => "• Historic building - No alterations\n• Guided tours for public restricted\n• Winter gear storage available\n• No smoking",
                'amenities_count' => [8, 15],
                'base_price' => 350,
                'discount_price' => 300,
                'photo_keywords' => ['Chateau Frontenac', 'Quebec City landmark', 'castle hotel Canada'],
            ],
            [
                'name' => 'Royal Mansour Marrakech',
                'description' => 'A palace built for a king. Royal Mansour offers an exceptional accommodation experience with its unique concept of private riads instead of rooms. Crafted by the finest artisans of Morocco, it is a showcase of Moroccan architecture.',
                'address' => 'Rue Abou Abbas El Sebti',
                'city' => 'Marrakech',
                'state' => 'Marrakesh-Safi',
                'country' => 'Morocco',
                'postal_code' => '40000',
                'latitude' => 31.6258,
                'longitude' => -7.9891,
                'max_guests' => 2,
                'bedrooms' => 1,
                'bathrooms' => 2,
                'beds' => 1,
                'square_meters' => 140,
                'check_in_time_start' => '15:00',
                'check_in_time_end' => '21:00',
                'check_out_time' => '12:00',
                'minimum_nights' => 2,
                'maximum_nights' => 21,
                'preparation_time' => 2,
                'is_active' => true,
                'is_instant_booking' => false,
                'house_rules' => "• Private Riad respect\n• No outside visitors without registration\n• Traditional Moroccan etiquette\n• Quiet hours in Medina vicinity",
                'amenities_count' => [20, 30],
                'base_price' => 1800,
                'discount_price' => 1600,
                'photo_keywords' => ['Royal Mansour Marrakech', 'Moroccan palace', 'luxury riad'],
            ],
            [
                'name' => 'Villa d\'Este, Lake Como',
                'description' => 'One of the world\'s most iconic luxury hotels, the Villa d\'Este is an opulent 16th-century palace set in 25 acres of stunning Renaissance gardens on the shores of Lake Como. A favorite of aristocracy and film stars.',
                'address' => 'Via Regina, 40',
                'city' => 'Cernobbio',
                'state' => 'Como',
                'country' => 'Italy',
                'postal_code' => '22012',
                'latitude' => 45.8458,
                'longitude' => 9.0772,
                'max_guests' => 2,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'beds' => 1,
                'square_meters' => 70,
                'check_in_time_start' => '14:00',
                'check_in_time_end' => '20:00',
                'check_out_time' => '12:00',
                'minimum_nights' => 3,
                'maximum_nights' => 14,
                'preparation_time' => 2,
                'is_active' => true,
                'is_instant_booking' => false,
                'house_rules' => "• Garden heritage protection\n• No children under 8 unaccompanied\n• Formal attire for dinner\n• Private event restrictions apply",
                'amenities_count' => [12, 18],
                'base_price' => 950,
                'discount_price' => 0,
                'photo_keywords' => ['Villa dEste Lake Como', 'Renaissance garden Italy', 'luxury villa Como'],
            ],
            [
                'name' => 'The Savoy, London',
                'description' => 'The Savoy is the most iconic luxury hotel in London. Situated on the North Bank of the River Thames, it has more than 130 years of history as a glamorous social hub. Home to the world-famous American Bar.',
                'address' => 'Strand',
                'city' => 'London',
                'state' => 'Greater London',
                'country' => 'United Kingdom',
                'postal_code' => 'WC2R 0EZ',
                'latitude' => 51.5104,
                'longitude' => -0.1204,
                'max_guests' => 2,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'beds' => 1,
                'square_meters' => 50,
                'check_in_time_start' => '15:00',
                'check_in_time_end' => '23:00',
                'check_out_time' => '12:00',
                'minimum_nights' => 1,
                'maximum_nights' => 60,
                'preparation_time' => 1,
                'is_active' => true,
                'is_instant_booking' => true,
                'house_rules' => "• Smoking prohibited\n• Smart casual in common areas\n• Pets policy applies\n• Courtesy to other guests regarding noise",
                'amenities_count' => [10, 15],
                'base_price' => 650,
                'discount_price' => 600,
                'photo_keywords' => ['The Savoy London', 'London landmark hotel', 'luxury Thames hotel'],
            ],
            [
                'name' => 'Bellagio Las Vegas',
                'description' => 'Inspired by the Lake Como town of Bellagio in Italy, the Bellagio is famed for its elegance. Its most notable feature is an 8-acre lake between the building and the Las Vegas Strip, which houses the Fountains of Bellagio.',
                'address' => '3600 S Las Vegas Blvd',
                'city' => 'Las Vegas',
                'state' => 'Nevada',
                'country' => 'United States',
                'postal_code' => '89109',
                'latitude' => 36.1126,
                'longitude' => -115.1767,
                'max_guests' => 4,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'beds' => 2,
                'square_meters' => 100,
                'check_in_time_start' => '15:00',
                'check_in_time_end' => '00:00',
                'check_out_time' => '11:00',
                'minimum_nights' => 1,
                'maximum_nights' => 21,
                'preparation_time' => 1,
                'is_active' => true,
                'is_instant_booking' => true,
                'house_rules' => "• Casino age restrictions (21+)\n• No smoking in non-smoking suites\n• Pool access guidelines\n• Maximum 4 guests per suite",
                'amenities_count' => [15, 25],
                'base_price' => 400,
                'discount_price' => 350,
                'photo_keywords' => ['Bellagio Fountains Las Vegas', 'Bellagio Resort', 'Las Vegas Strip luxury'],
            ],
        ];

        foreach ($properties as $propertyData) {
            // Extract amenities_count and photo_keywords before creating property
            $amenitiesCount = $propertyData['amenities_count'];
            $photoKeywords = $propertyData['photo_keywords'];
            unset($propertyData['amenities_count']);
            unset($propertyData['photo_keywords']);

            // Assign random vendor
            $propertyData['vendor_id'] = $vendors->random()->id;

            // Assign random cancellation policy if available
            if ($cancellationPolicies->isNotEmpty()) {
                $propertyData['cancellation_policy_id'] = $cancellationPolicies->random()->id;
            }

            // Assign random property type if available
            if ($propertyTypes->isNotEmpty()) {
                $propertyData['property_type_id'] = $propertyTypes->random()->id;
            }

            // Create slug
            $propertyData['slug'] = Str::slug($propertyData['name']);

            // Create property
            $property = Property::create($propertyData);

            // Add photos from Unsplash
            // Add photos
            foreach ($photoKeywords as $keyword) {
                try {
                    // Use loremflickr as alternative to unsplash
                    // Format: https://loremflickr.com/{width}/{height}/{keywords}
                    $formattedKeyword = str_replace(' ', ',', $keyword);
                    $imageUrl = 'https://loremflickr.com/800/600/'.urlencode($formattedKeyword);

                    $property->addMediaFromUrl($imageUrl)
                        ->toMediaCollection('default');
                } catch (\Exception $e) {
                    $this->command->warn("    ✗ Failed to download photo for: {$property->name} with keyword: {$keyword}");
                }
            }

            // Attach random amenities
            if ($amenities->isNotEmpty()) {
                $randomAmenitiesCount = rand($amenitiesCount[0], $amenitiesCount[1]);
                $selectedAmenities = $amenities->random(min($randomAmenitiesCount, $amenities->count()));
                $property->amenities()->attach($selectedAmenities->pluck('id')->toArray());
            }

            $this->command->info("Created property: {$property->name} with ".count($photoKeywords).' photos');
        }

        $this->command->info('Property seeder completed successfully!');
    }

    public function generatePropertyVendors($number = 2)
    {
        $vendorType = VendorType::where('slug', 'booking')->first();
        $vendorsData = [
            [
                'name' => 'Marriott International',
                'description' => 'Marriott International, Inc. is an American multinational company that operates, franchises, and licenses lodging including hotel, residential, and timeshare properties.',
                'email' => 'contact@marriott.com',
                'phone' => '+1 301-380-3000',
                'address' => '7750 Wisconsin Ave',
                'city' => 'Bethesda',
                'state' => 'MD',
                'country' => 'USA',
                'latitude' => 38.9897,
                'longitude' => -77.0933,
            ],
            [
                'name' => 'Hilton Worldwide',
                'description' => 'Hilton Worldwide Holdings Inc. is an American multinational hospitality company that manages and franchises a broad portfolio of hotels and resorts.',
                'email' => 'info@hilton.com',
                'phone' => '+1 703-883-1000',
                'address' => '7930 Jones Branch Dr',
                'city' => 'McLean',
                'state' => 'VA',
                'country' => 'USA',
                'latitude' => 38.9191,
                'longitude' => -77.2185,
            ],
            [
                'name' => 'Hyatt Hotels Corporation',
                'description' => 'Hyatt Hotels Corporation is an American multinational hospitality company headquartered in Chicago that manages and franchises luxury and business hotels, resorts, and vacation properties.',
                'email' => 'support@hyatt.com',
                'phone' => '+1 312-750-1234',
                'address' => '150 North Riverside Plaza',
                'city' => 'Chicago',
                'state' => 'IL',
                'country' => 'USA',
                'latitude' => 41.8853,
                'longitude' => -87.6391,
            ],
            [
                'name' => 'Wyndham Hotels & Resorts',
                'description' => 'Wyndham Hotels & Resorts, Inc. is an American hotel company based in Parsippany, New Jersey, United States. It is the world\'s largest hotel franchisor.',
                'email' => 'corporate@wyndham.com',
                'phone' => '+1 973-753-6000',
                'address' => '22 Sylvan Way',
                'city' => 'Parsippany',
                'state' => 'NJ',
                'country' => 'USA',
                'latitude' => 40.8407,
                'longitude' => -74.4552,
            ],
            [
                'name' => 'MGM Resorts International',
                'description' => 'MGM Resorts International is an American global hospitality and entertainment company operating regional destinations.',
                'email' => 'reservations@mgmresorts.com',
                'phone' => '+1 702-693-7120',
                'address' => '3600 Las Vegas Blvd S',
                'city' => 'Las Vegas',
                'state' => 'NV',
                'country' => 'USA',
                'latitude' => 36.1126,
                'longitude' => -115.1767,
            ],
            [
                'name' => 'Choice Hotels International',
                'description' => 'Choice Hotels International, Inc. is an American multinational hospitality company based in Rockville, Maryland.',
                'email' => 'info@choicehotels.com',
                'phone' => '+1 301-592-5000',
                'address' => '1 Choice Hotels Cir',
                'city' => 'Rockville',
                'state' => 'MD',
                'country' => 'USA',
                'latitude' => 39.0840,
                'longitude' => -77.1528,
            ],
        ];

        if ($number != null && $number > count($vendorsData)) {
            $number = count($vendorsData);
        }
        $mVendors = array_slice($vendorsData, 0, $number);
        $vendors = [];
        foreach ($mVendors as $data) {
            // Remove keys that are not in the vendors table
            unset($data['city']);
            unset($data['state']);
            unset($data['country']);

            $data['vendor_type_id'] = $vendorType->id;
            $data['is_active'] = true;
            $data['base_delivery_fee'] = 0;
            $data['delivery_fee'] = 0;
            $data['delivery_range'] = 100;
            $data['tax'] = '0';
            $data['commission'] = 0;
            $data['pickup'] = true;
            $data['delivery'] = true;

            $vendor = Vendor::create($data);
            $vendors[] = $vendor;

            // Add logo/feature image placeholder if possible
            try {
                $vendor->addMediaFromUrl('https://loremflickr.com/400/400/building,logo')
                    ->toMediaCollection('logo');
                $vendor->addMediaFromUrl('https://loremflickr.com/1280/720/hotel,facade')
                    ->toMediaCollection('feature_image');
            } catch (\Exception $e) {
                // Silently fail if image download fails
            }
        }

        return collect($vendors);
    }
}
