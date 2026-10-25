<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Property;
use App\Models\BookingOrder;
use App\Models\PropertyAvailability;
use App\Models\PropertyFee;
use App\Models\BookingOrderFee;
use App\Models\BookingGuest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Traits\OrderTrait;
use App\Traits\WalletTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BookingOrderService
{
    use OrderTrait, WalletTrait;

    public function __construct()
    {
        //
    }

    public function checkAvailability($propertyId, $checkIn, $checkOut)
    {
        $property = Property::find($propertyId);

        if (!$property || !$property->is_active) {
            return [
                'available' => false,
                'message' => __('Property not found or inactive'),
            ];
        }

        // Validate dates
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        if ($checkInDate->isPast()) {
            return [
                'available' => false,
                'message' => __('Check-in date cannot be in the past'),
            ];
        }

        if ($checkOutDate->lte($checkInDate)) {
            return [
                'available' => false,
                'message' => __('Check-out must be after check-in'),
            ];
        }

        $numberOfNights = $checkInDate->diffInDays($checkOutDate);

        // Check minimum/maximum nights
        if ($numberOfNights < $property->minimum_nights) {
            return [
                'available' => false,
                'message' => __('Minimum stay is :min_nights night(s)', ['min_nights' => $property->minimum_nights]),
            ];
        }

        if ($property->maximum_nights && $numberOfNights > $property->maximum_nights) {
            return [
                'available' => false,
                'message' => __('Maximum stay is :max_nights night(s)', ['max_nights' => $property->maximum_nights]),
            ];
        }

        // Check availability
        $isAvailable = $property->isAvailable($checkIn, $checkOut);

        if (!$isAvailable) {
            return [
                'available' => false,
                'message' => __('Property/room is not available for selected dates'),
            ];
        }

        return [
            'available' => true,
            'message' => __('Property is available'),
            'nights' => $numberOfNights,
        ];
    }

    public function calculateBookingCost($propertyId, $checkIn, $checkOut, $numberOfGuests, $couponCode = null)
    {
        $property = Property::with(['activeFees.bookingFeeType'])->find($propertyId);

        if (!$property) {
            throw new \Exception(__('Property not found'));
        }

        // Get pricing for dates
        $pricing = $property->getPricingForDates($checkIn, $checkOut);
        $numberOfNights = $pricing['number_of_nights'];
        $nightsCost = $pricing['total_nights_cost'];

        // Get applicable fees
        $fees = PropertyFee::where('property_id', $propertyId)
            ->where('is_active', 1)
            ->with('bookingFeeType')
            ->get();

        $totalFees = 0;
        $feeBreakdown = [];

        foreach ($fees as $fee) {
            $calculatedAmount = $fee->calculateFee($nightsCost, $numberOfNights, $numberOfGuests);
            $totalFees += $calculatedAmount;

            $feeBreakdown[] = [
                'id' => $fee->bookingFeeType->id,
                'name' => $fee->bookingFeeType->name,
                'calculation_type' => $fee->bookingFeeType->calculation_type,
                'amount' => $calculatedAmount,
                'is_taxable' => $fee->bookingFeeType->is_taxable,
            ];
        }

        // Calculate subtotal (nights cost)
        $subTotal = $nightsCost;

        // Apply coupon if provided
        $discount = 0;
        if ($couponCode) {
            $couponDiscount = $this->checkCoupon($couponCode, $subTotal, $property->vendor_id);
            $discount = $couponDiscount['discount'] ?? 0;
        }

        // Calculate tax
        $taxRate = (float) ($property->vendor->tax ?? 0);
        $taxableAmount = (float) ($subTotal - $discount);
        $tax = ($taxableAmount * $taxRate) / 100;

        // Calculate total
        $total = (float) ($subTotal - $discount + ($tax + $totalFees));

        return [
            'property_id' => $propertyId,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'number_of_nights' => $numberOfNights,
            'base_price' => $pricing['base_price'],
            'nights_cost' => $nightsCost,
            'fees' => $feeBreakdown,
            'total_fees' => $totalFees,
            'sub_total' => $subTotal,
            'discount' => $discount,
            'tax_rate' => $taxRate,
            'tax' => $tax,
            'total' => $total,
        ];
    }

    /**
     * Create a booking order
     *
     * @param array $data
     * @return array
     */
    public function createBooking($data)
    {
        try {
            DB::beginTransaction();

            $userId = Auth::id() ?? $data['user_id'];
            $propertyId = $data['property_id'];
            $checkIn = $data['check_in_date'];
            $checkOut = $data['check_out_date'];
            $numberOfGuests = $data['number_of_guests'];
            $numberOfAdults = $data['number_of_adults'] ?? $numberOfGuests;
            $numberOfChildren = $data['number_of_children'] ?? 0;
            $numberOfInfants = $data['number_of_infants'] ?? 0;
            $numberOfPets = $data['number_of_pets'] ?? 0;
            $paymentMethodId = $data['payment_method_id'];
            $specialRequests = $data['special_requests'] ?? null;

            // Validate availability
            $availability = $this->checkAvailability($propertyId, $checkIn, $checkOut);
            if (!$availability['available']) {
                throw new \Exception($availability['message']);
            }

            // Get property
            $property = Property::with('vendor')->findOrFail($propertyId);

            // Calculate costs
            $costs = $this->calculateBookingCost(
                $propertyId,
                $checkIn,
                $checkOut,
                $numberOfGuests,
                $data['coupon_code'] ?? null
            );

            // Create main Order
            $order = new Order();
            $order->code = $this->generateOrderCode();
            $order->verification_code = $this->generateVerificationCode();
            $order->vendor_id = $property->vendor_id;
            $order->user_id = $userId;
            $order->payment_method_id = $paymentMethodId;
            $order->sub_total = $costs['sub_total'];
            $order->discount = $costs['discount'];
            $order->delivery_fee = 0; // Not applicable for bookings
            $order->tax = $costs['tax'];
            $order->tax_rate = $costs['tax_rate'];
            $order->tip = 0;
            $order->total = $costs['total'];
            $order->payment_status = 'pending';
            $order->note = $data['note'] ?? null;
            $order->save();

            // Create BookingOrder
            $bookingOrder = new BookingOrder();
            $bookingOrder->order_id = $order->id;
            $bookingOrder->property_id = $propertyId;
            $bookingOrder->check_in_date = $checkIn;
            $bookingOrder->check_in_time = $data['check_in_time'] ?? $property->check_in_time_start;
            $bookingOrder->check_out_date = $checkOut;
            $bookingOrder->check_out_time = $data['check_out_time'] ?? $property->check_out_time;
            $bookingOrder->number_of_guests = $numberOfGuests;
            $bookingOrder->number_of_adults = $numberOfAdults;
            $bookingOrder->number_of_children = $numberOfChildren;
            $bookingOrder->number_of_infants = $numberOfInfants;
            $bookingOrder->number_of_pets = $numberOfPets;
            $bookingOrder->number_of_nights = $costs['number_of_nights'];
            $bookingOrder->base_price = $costs['base_price'];
            $bookingOrder->total_nights_cost = $costs['nights_cost'];
            $bookingOrder->booking_type = 'entire_property';
            $bookingOrder->special_requests = $specialRequests;
            $bookingOrder->cancellation_policy_id = $property->cancellation_policy_id;
            $bookingOrder->save();

            // Create BookingOrderFees (snapshot fees at booking time)
            foreach ($costs['fees'] as $feeData) {
                $bookingOrderFee = new BookingOrderFee();
                $bookingOrderFee->booking_order_id = $bookingOrder->id;
                $bookingOrderFee->booking_fee_type_id = $feeData['id'];
                $bookingOrderFee->name = $feeData['name'];
                $bookingOrderFee->calculation_type = $feeData['calculation_type'];
                $bookingOrderFee->base_amount = $feeData['amount'];
                $bookingOrderFee->calculated_amount = $feeData['amount'];
                $bookingOrderFee->is_taxable = $feeData['is_taxable'];
                $bookingOrderFee->save();
            }

            // Create BookingGuests if provided
            if (isset($data['guests']) && is_array($data['guests'])) {
                foreach ($data['guests'] as $guestData) {
                    $guest = new BookingGuest();
                    $guest->booking_order_id = $bookingOrder->id;
                    $guest->guest_type = $guestData['guest_type'] ?? 'adult';
                    $guest->first_name = $guestData['first_name'] ?? null;
                    $guest->last_name = $guestData['last_name'] ?? null;
                    $guest->age = $guestData['age'] ?? null;
                    $guest->save();
                }
            }

            // Set order status
            $order->setStatus(BookingOrder::PENDING);
            // Block dates, if payment failed it will be released
            $bookingOrder->blockDates();

            // Process payment
            $paymentProcessed = $this->processOrderPayment($order);
            if ($paymentProcessed) {
                // Set order status
                if ($property->is_instant_booking) {
                    $order->setStatus(BookingOrder::CONFIRMED); // Confirmed
                } else {
                    $order->setStatus(BookingOrder::AWAITING_HOST_APPROVAL); // Awaiting host approval
                }

            }

            DB::commit();

            return [
                'success' => true,
                'order' => $order->fresh(['booking_order', 'vendor', 'user']),
                'payment_link' => $order->payment_link,
                'message' => $property->is_instant_booking
                    ? __('Booking confirmed!')
                    : __('Booking request sent. Awaiting host approval.'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Confirm booking (for non-instant bookings, by host)
     *
     * @param int $orderId
     * @return bool
     */
    public function confirmBooking($orderId)
    {
        $order = Order::with('booking_order')->findOrFail($orderId);

        //then only auth with manager role and owns the property can confirm the booking: skip if role is admin
        if (!Auth::user()->hasRole('admin')) {
            if (!Auth::user()->hasRole('manager') || Auth::user()->vendor_id !== $order->booking_order->property->vendor_id) {
                throw new \Exception(__('You are not authorized to confirm this booking'));
            }
        }

        if (!$order->is_booking) {
            throw new \Exception(__('This is not a booking order'));
        }

        if ($order->status !== 'pending') {
            throw new \Exception(__('Booking cannot be confirmed in current status'));
        }



        $order->setStatus(BookingOrder::CONFIRMED); // Confirmed
        return true;
    }

    /**
     * Cancel booking and process refund
     *
     * @param int $orderId
     * @param string|null $reason
     * @return array
     */
    public function cancelBooking($orderId, $reason = null)
    {
        try {
            DB::beginTransaction();

            $order = Order::with('booking_order.cancellationPolicy')->findOrFail($orderId);

            if (!$order->is_booking) {
                throw new \Exception(__('This is not a booking order'));
            }

            if (!$order->can_cancel) {
                throw new \Exception(__('Booking cannot be cancelled'));
            }

            //only the owner, manager and admin can cancel the booking
            if (!Auth::user()->hasRole('admin')) {
                if (Auth::user()->hasRole('manager') && Auth::user()->vendor_id !== $order->vendor_id) {
                    throw new \Exception(__('You are not authorized to cancel this booking'));
                } else if (Auth::id() != $order->user_id) {
                    throw new \Exception(__('You are not authorized to cancel this booking'));
                }
            }

            // Calculate refund
            $refundAmount = $order->booking_refund_amount;

            // Release blocked dates
            $order->booking_order->releaseDates();

            // Update order status
            $order->reason = $reason;
            $order->save();
            $order->setStatus(BookingOrder::CANCELLED);

            // Process refund if applicable
            if ($refundAmount > 0 && $order->payment_status === 'successful') {
                $order->refundUser(true);
            }

            DB::commit();

            return [
                'success' => true,
                'refund_amount' => $refundAmount,
                'message' => __('Booking cancelled successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate unique verification code for order
     */
    public function generateVerificationCode()
    {
        return $this->generateOrderCode(12);
    }

    /**
     * Process order payment
     */
    public function processOrderPayment($order)
    {
        $paymentMethod = $order->payment_method;
        if (!$paymentMethod) {
            return false;
        }

        if ($paymentMethod->is_cash) {
            if ($paymentMethod->slug == 'wallet') {
                $wallet = Wallet::where('user_id', $order->user_id)->first();
                if ($wallet && $wallet->balance >= $order->total) {
                    $this->orderWalletPaymentProcess($wallet, $order->total, $order);
                    $order->payment_status = 'successful';
                    $order->save();
                    return true;
                }
                return false;
            }
        }
        return false; // Online payments need external processing
    }

    /**
     * Check coupon and return discount
     */
    public function checkCoupon($code, $subTotal, $vendorId)
    {
        $coupon = \App\Models\Coupon::where('code', $code)->active()->first();
        if (!$coupon) {
            return ['discount' => 0];
        }

        // Check if coupon belongs to vendor OR is global
        $vendorCoupon = $coupon->vendors()->where('id', $vendorId)->exists();
        if (!$vendorCoupon && $coupon->vendors()->count() > 0) {
            return ['discount' => 0];
        }

        // Simple percent/fixed calculation
        $discount = 0;
        if ($coupon->percentage) {
            $discount = ($subTotal * $coupon->discount) / 100;
        } else {
            $discount = $coupon->discount;
        }

        return ['discount' => min($discount, $subTotal)];
    }
}
