<?php

namespace App\Http\Livewire;

use App\Models\BookingOrder;
use App\Models\Order;
use App\Services\BookingOrderService;
use Exception;
use Illuminate\Support\Facades\DB;

class BookingOrderDetailsLivewire extends BaseLivewireComponent
{
    public $orderId;
    public $order;
    public $bookingOrder;

    // For editing
    public $status;
    public $paymentStatus;
    public $note;

    // Available statuses
    public $order_status = [];
    public $order_payment_status = [];

    protected $bookingOrderService;

    public function boot(BookingOrderService $bookingOrderService)
    {
        $this->bookingOrderService = $bookingOrderService;
    }

    public function mount($code)
    {
        // Find order by code instead of id for security
        $order = Order::where('code', $code)->firstOrFail();
        $this->orderId = $order->id;
        $this->loadOrder();
        $this->setupStatuses();
        $this->status = $this->order->status;
    }

    public function loadOrder()
    {
        $this->order = Order::with([
            'booking_order.property.vendor',
            'booking_order.property.media',
            'booking_order.property.amenities.media',
            'booking_order.guests',
            'booking_order.fees.bookingFeeType',
            'booking_order.cancellationPolicy',
            'user',
            'vendor',
            'payment_method',
            'payment',
            'statuses',
        ])->findOrFail($this->orderId);

        // Verify user has access to this booking
        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('manager')) {
                // Managers can only view their vendor's bookings
                if ($this->order->vendor_id !== $user->vendor_id) {
                    abort(403, 'Unauthorized access to this booking');
                }
            } else {
                // Regular users can only view their own bookings
                if ($this->order->user_id !== $user->id) {
                    abort(403, 'Unauthorized access to this booking');
                }
            }
        }

        $this->bookingOrder = $this->order->booking_order;
        $this->paymentStatus = $this->order->payment_status;

        // Reload statuses based on new order state
        $this->setupStatuses();

    }

    public function setupStatuses()
    {
        $allStatuses = collect([
            ['id' => BookingOrder::PENDING, 'name' => __('Pending')],
            ['id' => BookingOrder::CONFIRMED, 'name' => __('Confirmed')],
            ['id' => BookingOrder::AWAITING_HOST_APPROVAL, 'name' => __('Ready for Check-in')],
            ['id' => BookingOrder::CHECKIN, 'name' => __('Checked In')],
            ['id' => BookingOrder::CHECKOUT, 'name' => __('Checked Out')],
            ['id' => BookingOrder::CANCELLED, 'name' => __('Cancelled')],
            ['id' => BookingOrder::COMPLETED, 'name' => __('Completed')],
            ['id' => BookingOrder::EXPIRED, 'name' => __('Expired')],
        ]);

        // Filter statuses for non-admin users based on current status
        if (!auth()->user()->hasRole('admin')) {
            $statusFlow = [
                BookingOrder::PENDING,
                BookingOrder::CONFIRMED,
                BookingOrder::AWAITING_HOST_APPROVAL,
                BookingOrder::CHECKIN,
                BookingOrder::CHECKOUT,
                BookingOrder::COMPLETED
            ];

            $currentStatus = $this->order->status;
            $currentIndex = array_search($currentStatus, $statusFlow);

            if ($currentIndex !== false) {
                // Return statuses that are NEXT in the flow, plus Cancelled/Expired
                // "all status before checkin and checkin is hidden" => index > currentIndex
                $this->order_status = $allStatuses->filter(function ($status) use ($statusFlow, $currentIndex) {
                    $statusIndex = array_search($status['id'], $statusFlow);

                    // If it is part of the flow
                    if ($statusIndex !== false) {
                        return $statusIndex > $currentIndex;
                    }

                    // Always allow Cancelled/Expired (unless logic dictates otherwise later)
                    return true;
                })
                    // Also remove Checkin/Checkout/Completed from dropdown as they require verification code
                    ->reject(function ($status) {
                        return in_array($status['id'], [
                            BookingOrder::CHECKIN,
                            BookingOrder::CHECKOUT,
                            BookingOrder::COMPLETED
                        ]);
                    })
                    ->values()->toArray();
            } else {
                // If current status is not in flow (e.g. Cancelled, Expired, or Completed if distinct), show no options or limited
                $this->order_status = [];
            }
        } else {
            $this->order_status = $allStatuses->toArray();
        }

        $this->order_payment_status = collect([
            ['id' => 'pending', 'name' => __('Pending')],
            ['id' => 'request', 'name' => __('Request')],
            ['id' => 'review', 'name' => __('Review')],
            ['id' => 'successful', 'name' => __('Successful')],
            ['id' => 'failed', 'name' => __('Failed')],
            ['id' => 'cancelled', 'name' => __('Cancelled')],
        ])->toArray();
    }

    public function render()
    {
        $this->loadOrder();
        return view('livewire.booking-order-details');
    }

    public function approveBooking()
    {
        try {

            if ($this->order->status !== 'pending') {
                $this->showErrorAlert(__('Only pending bookings can be approved'));
                return;
            }

            DB::beginTransaction();
            $this->bookingOrderService->confirmBooking($this->orderId);
            DB::commit();

            $this->loadOrder();
            $this->showSuccessAlert(__('Booking approved successfully!'));
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __('Booking approval failed!'));
        }
    }

    public function rejectBooking()
    {
        try {

            if ($this->order->status !== 'pending') {
                $this->showErrorAlert(__('Only pending bookings can be rejected'));
                return;
            }

            DB::beginTransaction();
            $this->bookingOrderService->cancelBooking($this->orderId, $this->note ?? 'Rejected by host');
            DB::commit();

            $this->loadOrder();
            $this->showSuccessAlert(__('Booking rejected successfully!'));
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __('Booking rejection failed!'));
        }
    }

    public function updateStatus()
    {
        try {
            // Prevent non-admins from manually setting status to Checkin/Checkout/Completed
            if (
                !auth()->user()->hasRole('admin') && in_array($this->status, [
                    BookingOrder::CHECKIN,
                    BookingOrder::CHECKOUT,
                    BookingOrder::COMPLETED
                ])
            ) {
                $this->showErrorAlert(__('This status change requires verification code based action.'));
                return;
            }

            // Check if payment is successful for statuses other than failed/cancelled
            if (!in_array($this->status, [BookingOrder::CANCELLED, 'failed']) && $this->order->payment_status !== 'successful') {
                $this->showErrorAlert(__('Payment must be successful before updating status.'));
                return;
            }

            DB::beginTransaction();

            $this->order->setStatus($this->status);

            if ($this->note) {
                $this->order->note = $this->note;
                $this->order->save();
            }

            DB::commit();

            $this->loadOrder();
            $this->showSuccessAlert(__('Booking status updated successfully!'));
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __('Status update failed!'));
        }
    }

    public function updatePaymentStatus()
    {
        try {

            if (!auth()->user()->hasRole('admin') && !auth()->user()->can('order-edit-payment-status')) {
                $this->showErrorAlert(__('Unauthorized'));
                return;
            }

            DB::beginTransaction();

            $this->order->payment_status = $this->paymentStatus;
            $this->order->save();

            DB::commit();

            $this->loadOrder();
            $this->showSuccessAlert(__('Payment status updated successfully!'));
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __('Payment status update failed!'));
        }
    }

    public function acceptPayment()
    {
        try {

            if (!auth()->user()->hasRole('admin') && !auth()->user()->can('order-edit-payment-status')) {
                $this->showErrorAlert(__('Unauthorized'));
                return;
            }

            DB::beginTransaction();

            $this->order->payment_status = 'successful';
            $this->order->save();

            DB::commit();

            $this->loadOrder();
            $this->showSuccessAlert(__('Payment accepted successfully!'));
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __('Payment acceptance failed!'));
        }
    }

    public function rejectPayment()
    {
        try {

            if (!auth()->user()->hasRole('admin') && !auth()->user()->can('order-edit-payment-status')) {
                $this->showErrorAlert(__('Unauthorized'));
                return;
            }

            DB::beginTransaction();

            $this->order->payment_status = 'failed';
            $this->order->save();
            $this->order->setStatus('failed');

            DB::commit();

            $this->loadOrder();
            $this->showSuccessAlert(__('Payment rejected successfully!'));
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __('Payment rejection failed!'));
        }
    }

    public $verificationCode;

    protected $rules = [
        'verificationCode' => 'required|size:6',
    ];

    public function verifyPickupCode()
    {
        $this->validate([
            'verificationCode' => 'required',
        ]);

        try {

            if ($this->order->payment_status !== 'successful') {
                $this->showErrorAlert(__('Payment must be successful before verification'));
                return;
            }

            if ($this->verificationCode !== $this->order->verification_code) {
                $this->showErrorAlert(__('Invalid verification code'));
                return;
            }

            DB::beginTransaction();

            if (in_array($this->order->status, [BookingOrder::AWAITING_HOST_APPROVAL, BookingOrder::CONFIRMED])) {
                // Checkin
                $this->order->setStatus(BookingOrder::CHECKIN);
                // Regenerate code for checkout security
                $newCode = $this->bookingOrderService->generateVerificationCode();
                $this->order->verification_code = $newCode;
                $this->showSuccessAlert(__('Check-in successful! New code generated for checkout.'));
            } elseif ($this->order->status === BookingOrder::CHECKIN) {
                // Checkout
                $this->order->setStatus(BookingOrder::COMPLETED); // Or whatever represents checkout/completed
                $this->showSuccessAlert(__('Check-out successful! Booking completed.'));
            } else {
                throw new Exception(__('Invalid status for verification'));
            }

            $this->order->save();
            DB::commit();

            $this->verificationCode = '';
            $this->loadOrder();

        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage());
        }
    }

    public function cancelBooking()
    {
        try {

            if (!$this->order->can_cancel) {
                $this->showErrorAlert(__('This booking cannot be cancelled'));
                return;
            }

            DB::beginTransaction();
            $result = $this->bookingOrderService->cancelBooking($this->orderId, $this->note ?? 'Cancelled');
            DB::commit();

            $this->loadOrder();
            $this->showSuccessAlert(__('Booking cancelled successfully! Refund: ') . currencyFormat($result['refund_amount']));
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __('Booking cancellation failed!'));
        }
    }
}
