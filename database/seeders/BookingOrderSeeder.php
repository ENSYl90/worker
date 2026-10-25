<?php

namespace Database\Seeders;

use App\Models\BookingFeeType;
use App\Models\BookingGuest;
use App\Models\BookingOrder;
use App\Models\BookingOrderFee;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Property;
use App\Models\PropertyFee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookingOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get necessary data
        $properties = Property::with(['cancellationPolicy', 'vendor'])->get();
        $users = User::role('client')->get();
        if ($users->isEmpty()) {
            $users = User::limit(5)->get(); // Fallback
        }
        $paymentMethods = PaymentMethod::active()->get();
        $feeTypes = BookingFeeType::all();

        if ($properties->isEmpty()) {
            $this->command->warn('No properties found. Please seed properties first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');
            return;
        }

        // 1. Assign random fees to properties ensuring we have fee data to work with
        $this->seedPropertyFees($properties, $feeTypes);

        // 2. Create bookings
        foreach ($properties as $property) {
            // Create 3-5 bookings per property
            $bookingCount = rand(3, 5);

            for ($i = 0; $i < $bookingCount; $i++) {
                // Determine booking status/timing (Past, Current, Upcoming)
                $timing = $this->getRandomTiming($i);

                $checkInDate = $timing['check_in'];
                $checkOutDate = $timing['check_out'];
                $status = $timing['status'];

                // Skip if dates overlap with existing bookings for this property/room
                // For simplicity in seeding, we'll just try to space them out or ignore collisions that error out
                // but proper implementation would check availability.

                $this->createBooking(
                    $property,
                    $users->random(),
                    $paymentMethods->isNotEmpty() ? $paymentMethods->random() : null,
                    $checkInDate,
                    $checkOutDate,
                    $status
                );
            }
        }

        $this->command->info('Booking orders seeded successfully!');
    }

    private function seedPropertyFees($properties, $feeTypes)
    {
        if ($feeTypes->isEmpty()) {
            return;
        }

        foreach ($properties as $property) {
            // Clean existing fees to avoid duplicates if run multiple times
            PropertyFee::where('property_id', $property->id)->delete();

            // Assign 1-3 random fees
            $randomFeeTypes = $feeTypes->random(rand(1, 3));

            foreach ($randomFeeTypes as $feeType) {
                PropertyFee::create([
                    'property_id' => $property->id,
                    'booking_fee_type_id' => $feeType->id,
                    'amount' => $feeType->calculation_type == BookingFeeType::CALCULATION_PERCENTAGE ? rand(5, 15) : rand(10, 50),
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Property fees assigned.');
    }

    private function getRandomTiming($index)
    {
        // Mix of past, current, and future bookings
        $now = Carbon::now();

        if ($index == 0) {
            // Current booking (if possible)
            return [
                'check_in' => $now->copy()->subDays(rand(1, 3)),
                'check_out' => $now->copy()->addDays(rand(2, 5)),
                'status' => 'enroute', // Using 'enroute' for 'checked-in' equivalent in order statuses
            ];
        } elseif ($index < 3) {
            // Past booking
            $monthsAgo = rand(1, 6);
            $duration = rand(2, 7);
            $checkIn = $now->copy()->subMonths($monthsAgo);
            $checkOut = $checkIn->copy()->addDays($duration);

            return [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'status' => 'delivered', // Completed
            ];
        } else {
            // Future booking
            $daysInFuture = rand(5, 60);
            $duration = rand(2, 7);
            $checkIn = $now->copy()->addDays($daysInFuture);
            $checkOut = $checkIn->copy()->addDays($duration);

            return [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'status' => 'pending',
            ];
        }
    }

    private function createBooking($property, $user, $paymentMethod, $checkIn, $checkOut, $status)
    {

        // Calculate costs
        $nights = $checkIn->diffInDays($checkOut);
        $basePrice = $property->discount_price > 0 ? $property->discount_price : $property->base_price;
        if ($basePrice <= 0)
            $basePrice = 100; // Fallback price
        $nightsCost = $basePrice * $nights;

        // Calculate Tax (10% estimate)
        $tax = $nightsCost * 0.10;

        // Calculate Fees
        $feesCost = 0;
        $propertyFees = PropertyFee::where('property_id', $property->id)->get();

        foreach ($propertyFees as $fee) {
            $feesCost += $fee->calculateFee($nightsCost, $nights, 2); // Assuming 2 guests
        }

        $total = $nightsCost + $tax + $feesCost;

        // 1. Create Base Order
        try {
            $order = Order::withoutEvents(function () use ($user, $property, $paymentMethod, $nightsCost, $tax, $total, $status) {
                $order = Order::forceCreate([
                    'code' => Str::random(10),
                    "verification_code" => Str::random(5),
                    'user_id' => $user->id,
                    'vendor_id' => $property->vendor_id,
                    'payment_method_id' => $paymentMethod ? $paymentMethod->id : null,
                    'sub_total' => $nightsCost,
                    'tax' => $tax,
                    'total' => $total,
                    'payment_status' => in_array($status, ['delivered', 'enroute']) ? 'successful' : 'pending',
                ]);

                // Set the status using the trait method
                // We do this inside withoutEvents to avoid OrderObserver failing on 'fees' issue
                $order->setStatus($status);

                return $order;
            });

        } catch (\Exception $e) {
            $this->command->error("Failed to create order: " . $e->getMessage());
            return;
        }

        // 2. Create Booking Order
        try {
            $bookingOrder = BookingOrder::create([
                'order_id' => $order->id,
                'property_id' => $property->id,
                'check_in_date' => $checkIn,
                'check_in_time' => '15:00',
                'check_out_date' => $checkOut,
                'check_out_time' => '11:00',
                'number_of_guests' => 2,
                'number_of_adults' => 2,
                'number_of_children' => 0,
                'number_of_nights' => $nights,
                'base_price' => $basePrice,
                'total_nights_cost' => $nightsCost,
                'booking_type' => 'night', // or 'day'
                'cancellation_policy_id' => $property->cancellation_policy_id,
            ]);
        } catch (\Exception $e) {
            $this->command->error("Failed to create booking order: " . $e->getMessage());
            return;
        }

        // 3. Create Guests
        try {
            $names = explode(' ', $user->name, 2);
            $firstName = $names[0];
            $lastName = $names[1] ?? '';

            BookingGuest::create([
                'booking_order_id' => $bookingOrder->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'guest_type' => 'adult', // Changed from age_group
                'age' => 30, // Added age
            ]);

            // Add a secondary guest
            BookingGuest::create([
                'booking_order_id' => $bookingOrder->id,
                'first_name' => 'Guest',
                'last_name' => 'User',
                'guest_type' => 'adult', // Changed from age_group
                'age' => 25, // Added age
            ]);
        } catch (\Exception $e) {
            $this->command->error("Failed to create guests: " . $e->getMessage());
        }

        // 4. Create Booking Fees Records
        try {
            foreach ($propertyFees as $fee) {
                $amount = $fee->calculateFee($nightsCost, $nights, 2);
                BookingOrderFee::create([
                    'booking_order_id' => $bookingOrder->id,
                    'booking_fee_type_id' => $fee->booking_fee_type_id,
                    'name' => $fee->bookingFeeType->name,
                    'calculation_type' => $fee->bookingFeeType->calculation_type,
                    'base_amount' => $fee->amount, // Changed from amount
                    'calculated_amount' => $amount,
                    'is_taxable' => $fee->bookingFeeType->is_taxable ?? false,
                ]);
            }
        } catch (\Exception $e) {
            $this->command->error("Failed to create fees: " . $e->getMessage());
        }

        // 5. Block dates (Availability)
        // Only block dates if the order is valid/confirmed (not cancelled/failed)
        if (!in_array($status, ['cancelled', 'failed'])) {
            try {
                $bookingOrder->blockDates();
            } catch (\Exception $e) {
                // Ignore overlap errors during seeding
            }
        }

        $this->command->info("Created booking {$order->code} for {$property->name} ({$status})");
    }
}
