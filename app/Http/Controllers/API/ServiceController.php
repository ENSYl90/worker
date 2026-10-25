<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Traits\ExcludableModelRelations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Traits\ServiceCacheTrait;


class ServiceController extends Controller
{
    use ExcludableModelRelations;
    use ServiceCacheTrait;

    //
    public function index(Request $request)
    {
        // Generate a unique cache key based on request parameters
        $cacheKey = $this->generateCacheKey($request);

        // Determine cache duration based on request type and parameters
        $cacheDuration = $this->getCacheDuration($request);

        return Cache::remember($cacheKey, $cacheDuration, function () use ($request) {
            $query = Service::active()
                ->when($request->keyword, function ($query) use ($request) {
                    return $query->where(function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->keyword . '%')
                            ->orWhere('description', 'like', '%' . $request->keyword . '%');
                    });
                })
                ->when($request->category_id, function ($query) use ($request) {
                    return $query->where('category_id', "=", $request->category_id);
                })
                ->when($request->is_open, function ($query) use ($request) {
                    return $query->where('is_open', "=", $request->is_open);
                })
                ->when($request->vendor_type_id, function ($query) use ($request) {
                    return $query->whereHas('vendor', function ($query) use ($request) {
                        return $query->active()->where('vendor_type_id', $request->vendor_type_id);
                    });
                })
                ->when($request->vendor_id, function ($query) use ($request) {
                    return $query->active()->where('vendor_id', $request->vendor_id);
                })
                ->when($request->latitude, function ($query) use ($request) {
                    return $query->whereHas('vendor', function ($query) use ($request) {
                        return $query->byDeliveryZone($request->latitude, $request->longitude);
                    });
                })->when($request->type == "discount", function ($query) {
                    return $query->where('discount_price', ">", 0)->orderBy('discount_price', 'DESC');
                });

            // Apply excludes
            $this->applyExcludes($query, $request);
            $direction = $request->get('direction', 'asc');

            //
            if ($request->type == "best") {
                $query->withCount('sales')->orderBy('sales_count', $direction);
            } else {
                $query->orderBy('in_order', $direction);
            }

            // Order by in_order and return results
            return $query->when($request->page, function ($query) {
                return $query->paginate($this->perPage);
            }, function ($query) {
                return $query->get();
            });
        });
    }


    public function show(Request $request, $id)
    {
        return Service::find($id);
    }


    public function durations(Request $request)
    {
        return Service::getPossibleEnumValues('duration');
    }
}
