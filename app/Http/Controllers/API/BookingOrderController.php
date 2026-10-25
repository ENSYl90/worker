<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\BookingOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingOrderController extends Controller
{
    protected $bookingOrderService;

    public function __construct(BookingOrderService $bookingOrderService)
    {
        $this->middleware('auth:sanctum');
        $this->bookingOrderService = $bookingOrderService;
    }

    /**
     * Get user's bookings
     *
     * GET /api/bookings
     */
    public function index(Request $request)
    {
        try {
            $status = $request->status; // upcoming, current, past, cancelled

            $query = Order::with([
                'booking_order.property',
                'vendor'
            ])
                ->whereHas('booking_order')
                ->where('user_id', Auth::id());

            // Filter by status
            if ($status === 'upcoming') {
                $query->whereHas('booking_order', function ($q) {
                    $q->where('check_in_date', '>', now());
                });
            } elseif ($status === 'current') {
                $query->whereHas('booking_order', function ($q) {
                    $q->where('check_in_date', '<=', now())
                        ->where('check_out_date', '>=', now());
                });
            } elseif ($status === 'past') {
                $query->whereHas('booking_order', function ($q) {
                    $q->where('check_out_date', '<', now());
                });
            } elseif ($status === 'cancelled') {
                $query->where('status', 'cancelled');
            }

            $bookings = $query->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 20);

            return response()->json($bookings, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get single booking details
     *
     * GET /api/bookings/{id}
     */
    public function show($id)
    {
        try {
            $order = Order::with([
                'booking_order.property.vendor',
                'booking_order.property',
                'booking_order.property.amenities',
                'booking_order.guests',
                'booking_order.fees.bookingFeeType',
                'booking_order.cancellationPolicy',
                'payment_method',
                'statuses',
            ])
                ->whereHas('booking_order')
                ->where('user_id', Auth::id())
                ->where(function ($query) use ($id) {
                    $query->where('id', $id)->orWhere('code', $id);
                })
                ->firstOrFail();

            return response()->json($order, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }
    }

    /**
     * Create a new booking
     *
     * POST /api/bookings
     */
    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'number_of_guests' => 'required|integer|min:1',
            'number_of_adults' => 'nullable|integer|min:1',
            'number_of_children' => 'nullable|integer|min:0',
            'number_of_infants' => 'nullable|integer|min:0',
            'number_of_pets' => 'nullable|integer|min:0',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'guests' => 'nullable|array',

            'guests.*.guest_type' => 'required_with:guests|in:adult,child',
            'guests.*.first_name' => 'nullable|string|max:255',
            'guests.*.last_name' => 'nullable|string|max:255',
            'guests.*.age' => 'nullable|integer|min:0',
        ]);

        try {
            $data = $request->all();
            $data['user_id'] = Auth::id();

            $result = $this->bookingOrderService->createBooking($data);

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel a booking
     *
     * POST /api/bookings/{id}/cancel
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $order = Order::whereHas('booking_order')
                ->where('user_id', Auth::id())
                ->findOrFail($id);

            $result = $this->bookingOrderService->cancelBooking($id, $request->reason);

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get cancellation info (potential refund)
     *
     * GET /api/bookings/{id}/cancellation-info
     */
    public function cancellationInfo($id)
    {
        try {
            $order = Order::with(['booking_order.cancellationPolicy'])
                ->whereHas('booking_order')
                ->where('user_id', Auth::id())
                ->findOrFail($id);

            if (!$order->can_cancel) {
                return response()->json([
                    'can_cancel' => false,
                    'message' => __('This booking cannot be cancelled'),
                ], 200);
            }

            $refundAmount = $order->booking_refund_amount;
            $refundPercentage = ($refundAmount / $order->total) * 100;

            return response()->json([
                'can_cancel' => true,
                'total_paid' => $order->total,
                'refund_amount' => $refundAmount,
                'refund_percentage' => round($refundPercentage, 2),
                'cancellation_policy' => $order->booking_order->cancellationPolicy,
                'days_until_check_in' => $order->days_until_check_in,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('Booking not found'),
            ], 404);
        }
    }

    /**
     * Get booking summary for review before creating
     *
     * POST /api/bookings/preview
     */
    public function preview(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'room_type_id' => 'nullable|exists:room_types,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'number_of_guests' => 'required|integer|min:1',
            'coupon_code' => 'nullable|string',
        ]);

        try {
            // Check availability
            $availability = $this->bookingOrderService->checkAvailability(
                $request->property_id,
                $request->check_in_date,
                $request->check_out_date,
            );

            if (!$availability['available']) {
                return response()->json([
                    'available' => false,
                    'message' => $availability['message'],
                ], 200);
            }

            // Calculate pricing
            $pricing = $this->bookingOrderService->calculateBookingCost(
                $request->property_id,
                $request->check_in_date,
                $request->check_out_date,
                $request->number_of_guests,
                $request->coupon_code,
            );

            // Get property details
            $property = \App\Models\Property::with(['vendor', 'cancellationPolicy'])
                ->findOrFail($request->property_id);

            return response()->json([
                'available' => true,
                'property' => $property,
                'pricing' => $pricing,
                'is_instant_booking' => $property->is_instant_booking,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
