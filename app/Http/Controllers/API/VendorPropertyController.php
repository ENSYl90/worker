<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyFee;
use App\Models\BookingFeeType;
use App\Models\PropertyAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VendorPropertyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get vendor's properties
     *
     * GET /api/vendor/properties
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

            $query = Property::with(['amenities', 'cancellationPolicy', 'fees.bookingFeeType']);

            if ($user->hasRole('manager')) {
                $query->where('vendor_id', $user->vendor_id);
            } elseif ($user->hasRole('admin')) {
                // Admin can see all properties
            } else {
                // Regular vendor user
                $query->where('vendor_id', $user->vendor_id);
            }
            //keyword search
            $query->when($request->keyword, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->keyword . '%')
                        ->orWhere('description', 'like', '%' . $request->keyword . '%')
                        ->orWhere('address', 'like', '%' . $request->keyword . '%');
                });
            });

            $properties = $query->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 20);

            return response()->json($properties, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get single property
     *
     * GET /api/vendor/properties/{id}
     */
    public function show($id)
    {
        try {
            $user = Auth::user();

            $property = Property::with([
                'vendor',
                'amenities',
                'cancellationPolicy',
                'fees.bookingFeeType',
            ])->findOrFail($id);

            // Check permission
            if (!$user->hasRole('admin') && $property->vendor_id != $user->vendor_id) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            return response()->json($property, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Property not found',
            ], 404);
        }
    }

    /**
     * Create new property
     *
     * POST /api/vendor/properties
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'check_in_time_start' => 'nullable|date_format:H:i',
            'check_in_time_end' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'max_guests' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'beds' => 'required|integer|min:0',
            'square_meters' => 'nullable|integer|min:0',
            'is_instant_booking' => 'nullable|boolean',
            'minimum_nights' => 'required|integer|min:1',
            'maximum_nights' => 'required|integer|min:1|gte:minimum_nights',
            'preparation_time' => 'nullable|integer|min:0',
            'cancellation_policy_id' => 'nullable|exists:cancellation_policies,id',
            'house_rules' => 'nullable|string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        try {
            $user = Auth::user();
            $vendorId = $user->vendor_id;

            if (!$vendorId && !$user->hasRole('admin')) {
                return response()->json([
                    'message' => 'Vendor account required',
                ], 403);
            }

            $property = new Property();
            $property->vendor_id = $vendorId;
            $property->name = $request->name;
            $property->slug = Str::slug($request->name);
            $property->description = $request->description;
            $property->property_type_id = $request->property_type_id;
            $property->base_price = $request->base_price;
            $property->discount_price = $request->discount_price ?? 0;
            $property->address = $request->address;
            $property->latitude = $request->latitude;
            $property->longitude = $request->longitude;
            $property->city = $request->city;
            $property->state = $request->state;
            $property->country = $request->country;
            $property->postal_code = $request->postal_code;
            $property->check_in_time_start = $request->check_in_time_start ?? '14:00';
            $property->check_in_time_end = $request->check_in_time_end ?? '22:00';
            $property->check_out_time = $request->check_out_time ?? '11:00';
            $property->max_guests = $request->max_guests;
            $property->bedrooms = $request->bedrooms;
            $property->bathrooms = $request->bathrooms;
            $property->beds = $request->beds;
            $property->square_meters = $request->square_meters;
            $property->is_instant_booking = $request->is_instant_booking ?? false;
            $property->minimum_nights = $request->minimum_nights ?? 1;
            $property->maximum_nights = $request->maximum_nights;
            $property->preparation_time = $request->preparation_time ?? 0;
            $property->cancellation_policy_id = $request->cancellation_policy_id;
            $property->house_rules = $request->house_rules;
            $property->save();

            // Attach amenities
            if ($request->amenities) {
                $property->amenities()->sync($request->amenities);
            }

            return response()->json([
                'message' => 'Property created successfully',
                'property' => $property->load(['amenities']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update property
     *
     * PUT /api/vendor/properties/{id}
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'sometimes|required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'address' => 'sometimes|required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'check_in_time_start' => 'nullable|date_format:H:i',
            'check_in_time_end' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'max_guests' => 'sometimes|required|integer|min:1',
            'bedrooms' => 'sometimes|required|integer|min:0',
            'bathrooms' => 'sometimes|required|integer|min:0',
            'beds' => 'sometimes|required|integer|min:0',
            'square_meters' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_instant_booking' => 'nullable|boolean',
            'minimum_nights' => 'nullable|integer|min:1',
            'maximum_nights' => 'sometimes|required|integer|min:1',
            'preparation_time' => 'nullable|integer|min:0',
            'cancellation_policy_id' => 'nullable|exists:cancellation_policies,id',
            'house_rules' => 'nullable|string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        try {
            $user = Auth::user();
            $property = Property::findOrFail($id);

            // Check permission
            if (!$user->hasRole('admin') && $property->vendor_id != $user->vendor_id) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            $property->fill($request->except(['amenities']));
            $property->save();

            // Update amenities
            if ($request->has('amenities')) {
                $property->amenities()->sync($request->amenities);
            }

            return response()->json([
                'message' => 'Property updated successfully',
                'property' => $property->load(['amenities']),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete property
     *
     * DELETE /api/vendor/properties/{id}
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $property = Property::findOrFail($id);

            // Check permission
            if (!$user->hasRole('admin') && $property->vendor_id != $user->vendor_id) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Check if there are active bookings
            $activeBookings = \App\Models\BookingOrder::where('property_id', $id)
                ->whereHas('order', function ($q) {
                    $q->whereNotIn('status', ['cancelled', 'failed', 'delivered']);
                })
                ->count();

            if ($activeBookings > 0) {
                return response()->json([
                    'message' => 'Cannot delete property with active bookings',
                ], 400);
            }

            $property->delete();

            return response()->json([
                'message' => 'Property deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Manage property availability (block/unblock dates)
     *
     * POST /api/vendor/properties/{id}/availability
     */
    public function manageAvailability(Request $request, $id)
    {
        $request->validate([
            'dates' => 'required|array',
            'dates.*' => 'required|date',
            'action' => 'required|in:block,unblock',
            'price_override' => 'nullable|numeric|min:0',
        ]);

        try {
            $user = Auth::user();
            $property = Property::findOrFail($id);

            // Check permission
            if (!$user->hasRole('admin') && $property->vendor_id != $user->vendor_id) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            if ($request->action === 'block') {
                PropertyAvailability::blockDates(
                    $id,
                    $request->dates,
                    'blocked'
                );

                // Set price override if provided
                if ($request->price_override) {
                    foreach ($request->dates as $date) {
                        PropertyAvailability::where('property_id', $id)
                            ->where('date', $date)
                            ->update(['price_override' => $request->price_override]);
                    }
                }
            } else {
                PropertyAvailability::releaseDates(
                    $id,
                    $request->dates
                );
            }

            return response()->json([
                'message' => ucfirst($request->action) . ' dates updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Manage property fees
     *
     * POST /api/vendor/properties/{id}/fees
     */
    public function storeFee(Request $request, $id)
    {
        $request->validate([
            'booking_fee_type_id' => 'required|exists:booking_fee_types,id',
            'amount' => 'required|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $user = Auth::user();
            $property = Property::findOrFail($id);

            // Check permission
            if (!$user->hasRole('admin') && $property->vendor_id != $user->vendor_id) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            $fee = PropertyFee::updateOrCreate(
                [
                    'property_id' => $id,
                    'booking_fee_type_id' => $request->booking_fee_type_id,
                ],
                [
                    'amount' => $request->amount,
                    'min_amount' => $request->min_amount,
                    'max_amount' => $request->max_amount,
                    'is_active' => true,
                ]
            );

            return response()->json([
                'message' => 'Fee saved successfully',
                'fee' => $fee->load('bookingFeeType'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete property fee
     *
     * DELETE /api/vendor/properties/{propertyId}/fees/{feeId}
     */
    public function destroyFee($propertyId, $feeId)
    {
        try {
            $user = Auth::user();
            $property = Property::findOrFail($propertyId);

            // Check permission
            if (!$user->hasRole('admin') && $property->vendor_id != $user->vendor_id) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            $fee = PropertyFee::where('property_id', $propertyId)
                ->findOrFail($feeId);

            $fee->delete();

            return response()->json([
                'message' => 'Fee deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get available booking fee types
     *
     * GET /api/vendor/booking-fee-types
     */
    public function feeTypes()
    {
        try {
            $feeTypes = BookingFeeType::active()->ordered()->get();

            return response()->json($feeTypes, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
