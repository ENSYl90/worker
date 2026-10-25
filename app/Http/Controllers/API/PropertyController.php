<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyReviewResource;
use App\Models\Property;
use App\Models\Amenity;
use App\Models\PropertyReview;
use App\Models\PropertyType;
use App\Services\BookingOrderService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    protected $bookingOrderService;

    public function __construct(BookingOrderService $bookingOrderService)
    {
        $this->bookingOrderService = $bookingOrderService;
    }

    /**
     * Get list of properties with optional filters
     *
     * GET /api/properties
     */
    public function index(Request $request)
    {
        try {
            $query = Property::with(['vendor', 'amenities', 'propertyType', 'cancellationPolicy'])
                ->withCount('ratings')
                ->active();

            // Location search
            if ($request->has('latitude') && $request->has('longitude')) {
                $radius = $request->radius ?? 50;
                // $query->withinLocation($request->latitude, $request->longitude, $radius);
                if ($request->has('radius')) {
                    $query->withinLocation($request->latitude, $request->longitude, $radius);
                } else {
                    $query->sortByDistance($request->latitude, $request->longitude);
                }
            }

            // Property type
            if ($request->has('property_types_ids')) {
                $query->whereIn('property_type_id', $request->property_types_ids);
            }
            if ($request->has('property_type_id')) {
                $query->where('property_type_id', $request->property_type_id);
            }
            if ($request->has('property_type_slug')) {
                $slug = $request->property_type_slug;
                $query->whereHas('propertyType', function ($q) use ($slug) {
                    $q->where('slug', $slug);
                });
            }



            // Guest capacity
            if ($request->has('guests')) {
                $query->where('max_guests', '>=', $request->guests);
            }

            // Price range
            if ($request->has('min_price')) {
                $query->where('base_price', '>=', $request->min_price);
            }
            if ($request->has('max_price')) {
                $query->where('base_price', '<=', $request->max_price);
            }

            //check free dates:
            if ($request->has('check_in_date') && $request->has('check_out_date')) {
                $query->available($request->check_in_date, $request->check_out_date);
            }

            // Amenities
            if ($request->has('amenities_ids') && is_array($request->amenities_ids)) {
                $query->whereHas('amenities', function ($q) use ($request) {
                    $q->whereIn('amenities.id', $request->amenities_ids);
                });
            }

            // featured
            if ($request->has('featured')) {
                $query->where('featured', $request->featured);
            }

            // instant booking
            if ($request->has('instant_booking')) {
                $query->where('instant_booking', $request->instant_booking);
            }

            $perPage = $request->per_page ?? 20;
            $properties = $query->paginate($perPage);

            return response()->json($properties, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get single property details
     *
     * GET /api/properties/{id}
     */
    public function show($id)
    {
        try {

            $property = Property::with([
                'vendor',
                'amenities',
                'cancellationPolicy',
                'activeFees.bookingFeeType',
                'propertyType',
            ])
                ->withCount('reviews')
                ->with('reviews')
                ->findOrFail($id);

            if (!$property->is_active) {
                return response()->json([
                    'message' => 'Property not available',
                ], 404);
            }

            $property->topReviews = PropertyReviewResource::collection($property->reviews()->with('user')->orderBy('created_at', 'desc')->limit(5)->get());

            // Calculate next available dates (max 2 days)
            try {
                $lookAheadDays = 60;
                $today = \Carbon\Carbon::today();

                // If current time > check_in_time_start, today is no longer available as a start date
                $checkInTime = $property->check_in_time_start ? \Carbon\Carbon::parse($property->check_in_time_start) : \Carbon\Carbon::parse('14:00');
                if (\Carbon\Carbon::now()->gt($checkInTime)) {
                    $today->addDay();
                }

                $endDate = $today->copy()->addDays($lookAheadDays);

                $unavailableDates = \App\Models\PropertyAvailability::where('property_id', $id)
                    ->where('is_available', 0)
                    ->whereBetween('date', [$today->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->pluck('date')
                    ->map(function ($date) {
                        return \Carbon\Carbon::parse($date)->format('Y-m-d');
                    })
                    ->toArray();



                // Calculate available ranges for the next 365 days
                $availableRanges = [];
                $rangeLookAheadDays = 365;
                $rangeEndDate = $today->copy()->addDays($rangeLookAheadDays);

                $rangeUnavailableDates = \App\Models\PropertyAvailability::where('property_id', $id)
                    ->where('is_available', 0)
                    ->whereBetween('date', [$today->format('Y-m-d'), $rangeEndDate->format('Y-m-d')])
                    ->pluck('date')
                    ->map(function ($date) {
                        return \Carbon\Carbon::parse($date)->format('Y-m-d');
                    })
                    ->toArray();

                $minNights = $property->minimum_nights ?? 1;
                $currentRangeStart = null;
                $currentRangeLength = 0;

                for ($i = 0; $i < $rangeLookAheadDays; $i++) {
                    $loopDate = $today->copy()->addDays($i);
                    $dateStr = $loopDate->format('Y-m-d');

                    if (!in_array($dateStr, $rangeUnavailableDates)) {
                        if ($currentRangeStart === null) {
                            $currentRangeStart = $dateStr;
                        }
                        $currentRangeLength++;
                    } else {
                        // End of a contiguous block
                        if ($currentRangeStart !== null && $currentRangeLength >= $minNights) {
                            $availableRanges[] = [
                                'start' => $currentRangeStart,
                                'end' => $today->copy()->addDays($i - 1)->format('Y-m-d')
                            ];
                        }
                        $currentRangeStart = null;
                        $currentRangeLength = 0;
                    }
                }

                // Handle case where range extends to the end of the period
                $property->available_from = null;
                $property->available_to = null;
                if ($currentRangeStart !== null && $currentRangeLength >= $minNights) {
                    $availableRanges[] = [
                        'start' => $currentRangeStart,
                        'end' => $today->copy()->addDays($rangeLookAheadDays - 1)->format('Y-m-d')
                    ];
                }

                //
                $property->available_ranges = $availableRanges;

                // Existing logic for available_from/to (kept for backward compatibility or immediate next slot)
                for ($i = 0; $i < $lookAheadDays; $i++) {
                    $currentDate = $today->copy()->addDays($i);
                    $dateString = $currentDate->format('Y-m-d');

                    if (!in_array($dateString, $unavailableDates)) {
                        $property->available_from = $dateString;
                        $isConsecutiveAvailable = true;

                        // Check availability for minimum nights (excluding the first night which we already checked)
                        for ($j = 1; $j < $minNights; $j++) {
                            $nextDate = $currentDate->copy()->addDays($j);
                            $nextDateString = $nextDate->format('Y-m-d');
                            if (in_array($nextDateString, $unavailableDates)) {
                                $isConsecutiveAvailable = false;
                                break;
                            }
                        }

                        if ($isConsecutiveAvailable) {
                            $property->available_to = $currentDate->copy()->addDays($minNights)->format('Y-m-d');
                            break;
                        }
                        // If minimum nights are not available consecutively, we continue the outer loop 
                        // to find the next available start date that satisfies the minimum stay.
                        // Reset available_from if this block isn't valid
                        $property->available_from = null;
                    }
                }
            } catch (\Exception $ex) {
                // Ignore error, just don't return dates
                \Log::error($ex->getMessage());
            }

            return response()->json($property, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Property not found',
            ], 404);
        }
    }

    /**
     * Check property availability for specific dates
     *
     * POST /api/properties/{id}/check-availability
     */
    public function checkAvailability(Request $request, $id)
    {
        $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        try {
            $availability = $this->bookingOrderService->checkAvailability(
                $id,
                $request->check_in,
                $request->check_out
            );

            return response()->json($availability, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Calculate booking cost for property
     *
     * POST /api/properties/{id}/calculate-price
     */
    public function calculatePrice(Request $request, $id)
    {
        $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'number_of_guests' => 'required|integer|min:1',
            'coupon_code' => 'nullable|string',
        ]);

        try {
            $pricing = $this->bookingOrderService->calculateBookingCost(
                $id,
                $request->check_in,
                $request->check_out,
                $request->number_of_guests,
                $request->coupon_code
            );

            return response()->json($pricing, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }


    /**
     * Get available amenities
     *
     * GET /api/amenities
     */
    public function amenities(Request $request)
    {
        try {
            $amenities = Amenity::active()
                ->orderBy('name')
                ->when($request->has('limit'), function ($query) use ($request) {
                    return $query->limit($request->limit);
                })
                ->when($request->has('page'), function ($query) use ($request) {
                    return $query->paginate($request->page);
                }, function ($query) {
                    return $query->get();
                });

            return response()->json($amenities, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get distinct property types
     * 
     * GET /api/property/types
     */
    public function types()
    {
        try {
            $types = PropertyType::active()->get();
            return response()->json($types, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }



    public function reviews(Request $request, $id)
    {
        try {
            //use PropertyReviewResource
            $reviews = PropertyReview::with('user')
                ->where('property_id', $id)
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 20);
            return response()->json(PropertyReviewResource::collection($reviews), 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Rate and review a property
     * 
     * POST /api/properties/{id}/rate
     */
    public function rate(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
            'review' => 'nullable|string',
        ]);

        try {
            $property = Property::findOrFail($id);
            $userId = auth('api')->id();

            // Check if already rated (using laravel-rateable)
            // But we also want a PropertyReview entry for the comment

            // Create or update review
            PropertyReview::updateOrCreate(
                ['user_id' => $userId, 'property_id' => $id],
                ['rating' => $request->rating, 'review' => $request->review]
            );

            // Save rating to property (laravel-rateable)
            $property->rateOnce($request->rating, $request->review, $userId);

            return response()->json([
                'message' => 'Property rated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get similar properties
     * 
     * GET /api/properties/{id}/similar
     */
    public function similar(Request $request, $id)
    {
        try {
            $property = Property::findOrFail($id);
            $maxLimit = 5;
            $limit = $request->limit ?? $maxLimit;
            //can't be more tha 20
            if ($limit > 20) {
                $limit = 20;
            }

            $similar = Property::where('id', '!=', $id)
                ->where('is_active', 1)
                ->where(function ($q) use ($property) {
                    $q->where('city', $property->city)
                        ->orWhere('property_type_id', $property->property_type_id);
                })
                ->limit($limit)
                ->get();

            return response()->json($similar, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
