<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BookingOrder;
use App\Models\Order;
use App\Services\BookingOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorBookingController extends Controller
{
    protected $bookingOrderService;

    public function __construct(BookingOrderService $bookingOrderService)
    {
        $this->middleware('auth:sanctum');
        $this->bookingOrderService = $bookingOrderService;
    }

    /**
     * Get vendor's bookings
     *
     * GET /api/vendor/bookings
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            // Check if user is admin, manager, or vendor
            if (!$user->hasAnyRole(['admin', 'manager']) && !$user->vendor_id) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            $status = $request->status; // pending, confirmed, current, upcoming, past, cancelled
            $propertyId = $request->property_id;

            $query = Order::with([
                'booking_order.property',
                'booking_order.guests',
                'user',
                'payment_method',
            ])->whereHas('booking_order');

            // Filter by vendor
            if ($user->hasRole('manager')) {
                $query->where('vendor_id', $user->vendor_id);
            } elseif ($user->hasRole('admin')) {
                // Admin can see all
            } else {
                $query->where('vendor_id', $user->vendor_id);
            }

            // Filter by property
            if ($propertyId) {
                $query->whereHas('booking_order', function ($q) use ($propertyId) {
                    $q->where('property_id', $propertyId);
                });
            }

            // Filter by status
            if ($status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($status === 'confirmed') {
                $query->whereIn('status', ['preparing', 'ready']);
            } elseif ($status === 'current') {
                $query->whereHas('booking_order', function ($q) {
                    $q->where('check_in_date', '<=', now())
                        ->where('check_out_date', '>=', now());
                });
            } elseif ($status === 'upcoming') {
                $query->whereHas('booking_order', function ($q) {
                    $q->where('check_in_date', '>', now());
                })->whereNotIn('status', ['cancelled', 'failed']);
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
     * GET /api/vendor/bookings/{id}
     */
    public function show($id)
    {
        try {
            $user = Auth::user();

            $order = Order::with([
                'booking_order.property.vendor',
                'booking_order.guests',
                'booking_order.fees.bookingFeeType',
                'booking_order.cancellationPolicy',
                'user',
                'payment_method',
                'statuses',
            ])->whereHas('booking_order')->findOrFail($id);

            // Check permission
            if (!$user->hasRole('admin') && $order->vendor_id != $user->vendor_id) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            return response()->json($order, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }
    }

    /**
     * Approve/confirm a booking (for non-instant bookings)
     *
     * POST /api/vendor/bookings/{id}/approve
     */
    public function approve($id)
    {
        try {
            $user = Auth::user();
            $order = Order::whereHas('booking_order')->findOrFail($id);

            // Check permission
            if (!$user->hasRole('admin') && $order->vendor_id != $user->vendor_id) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            $this->bookingOrderService->confirmBooking($id);

            return response()->json([
                'message' => 'Booking approved successfully',
                'order' => $order->fresh(['booking_order']),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reject a booking
     *
     * POST /api/vendor/bookings/{id}/reject
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $user = Auth::user();
            $order = Order::whereHas('booking_order')->findOrFail($id);

            // Check permission
            if (!$user->hasRole('admin') && $order->vendor_id != $user->vendor_id) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            if ($order->status !== 'pending') {
                return response()->json([
                    'message' => 'Only pending bookings can be rejected',
                ], 400);
            }

            // Cancel the booking
            $result = $this->bookingOrderService->cancelBooking($id, $request->reason);

            return response()->json([
                'message' => 'Booking rejected successfully',
                'refund_amount' => $result['refund_amount'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get booking statistics for vendor
     *
     * GET /api/vendor/bookings/statistics
     */
    public function statistics(Request $request)
    {
        try {
            $user = Auth::user();
            $vendorId = $user->vendor_id;

            if (!$vendorId && !$user->hasRole('admin')) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            $query = Order::whereHas('booking_order');

            if (!$user->hasRole('admin')) {
                $query->where('vendor_id', $vendorId);
            }

            // Filter by property if specified
            if ($request->property_id) {
                $query->whereHas('booking_order', function ($q) use ($request) {
                    $q->where('property_id', $request->property_id);
                });
            }

            // Filter by date range if specified
            if ($request->start_date) {
                $query->where('created_at', '>=', $request->start_date);
            }
            if ($request->end_date) {
                $query->where('created_at', '<=', $request->end_date);
            }

            $totalBookings = $query->count();
            $pendingBookings = (clone $query)->where('status', 'pending')->count();
            $confirmedBookings = (clone $query)->whereIn('status', ['preparing', 'ready'])->count();
            $completedBookings = (clone $query)->whereIn('status', ['delivered', 'completed'])->count();
            $cancelledBookings = (clone $query)->where('status', 'cancelled')->count();
            $totalRevenue = (clone $query)->whereIn('payment_status', ['successful'])->sum('total');

            // Upcoming check-ins (next 7 days)
            $upcomingCheckIns = Order::whereHas('booking_order', function ($q) {
                $q->whereBetween('check_in_date', [now(), now()->addDays(7)]);
            })
                ->where('vendor_id', $vendorId)
                ->whereNotIn('status', ['cancelled', 'failed'])
                ->count();

            // Current guests (checked in, not checked out)
            $currentGuests = Order::whereHas('booking_order', function ($q) {
                $q->where('check_in_date', '<=', now())
                    ->where('check_out_date', '>=', now());
            })
                ->where('vendor_id', $vendorId)
                ->whereNotIn('status', ['cancelled', 'failed'])
                ->count();

            return response()->json([
                'total_bookings' => $totalBookings,
                'pending_bookings' => $pendingBookings,
                'confirmed_bookings' => $confirmedBookings,
                'completed_bookings' => $completedBookings,
                'cancelled_bookings' => $cancelledBookings,
                'total_revenue' => $totalRevenue,
                'upcoming_check_ins' => $upcomingCheckIns,
                'current_guests' => $currentGuests,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update booking status
     *
     * POST /api/vendor/bookings/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        $allowedStatuses = [
            BookingOrder::PENDING,
            BookingOrder::CONFIRMED,
            BookingOrder::AWAITING_HOST_APPROVAL,
            BookingOrder::CHECKIN,
            BookingOrder::CHECKOUT,
            BookingOrder::CANCELLED,
            BookingOrder::COMPLETED,
            BookingOrder::EXPIRED,
            "failed",
        ];
        $request->validate([
            'status' => 'required|in:' . implode(',', $allowedStatuses),
        ]);

        try {
            $user = Auth::user();
            $order = Order::whereHas('booking_order')->findOrFail($id);

            // Check permission
            if (!$user->hasRole('admin') && $order->vendor_id != $user->vendor_id) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            //perform verification for checkin and checkout
            $verificationCode = $request->verification_code;
            if (in_array($request->status, [BookingOrder::CHECKIN, BookingOrder::CHECKOUT])) {
                if (!$verificationCode) {
                    return response()->json([
                        'message' => __('Verification code is required'),
                    ], 400);
                } else if ($verificationCode != $order->verification_code) {
                    return response()->json([
                        'message' => __('Invalid verification code'),
                    ], 400);
                }
            }

            $order->setStatus($request->status);

            return response()->json([
                'message' => 'Booking status updated successfully',
                'order' => $order->fresh(['booking_order', 'statuses']),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
