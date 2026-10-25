<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait ServiceCacheTrait
{

    /**
     * Generate a unique cache key based on request parameters
     */
    private function generateCacheKey(Request $request): string
    {
        $keyParts = [
            'services_index',
            $request->type ?? 'all',
            $request->keyword ? md5($request->keyword) : 'no_keyword',
            $request->category_id ?? 'all_categories',
            $request->is_open ?? 'all_status',
            $request->vendor_type_id ?? 'all_vendor_types',
            $request->vendor_id ?? 'all_vendors',
            $request->latitude && $request->longitude ?
                md5($request->latitude . '_' . $request->longitude) : 'no_location',
            $request->page ?? '1',
            $this->perPage,
            // Include any exclude parameters from applyExcludes method
            $request->exclude_ids ? md5(implode(',', (array)$request->exclude_ids)) : 'no_excludes'
        ];

        return implode('_', $keyParts);
    }

    /**
     * Determine cache duration based on request parameters
     */
    private function getCacheDuration(Request $request): int
    {
        // Base cache time in minutes
        $baseCacheTime = 30; // 30 minutes default

        // Adjust cache time based on request type and parameters
        if ($request->type === 'best') {
            // Sales data changes more frequently, shorter cache
            return 15; // 15 minutes
        }

        if ($request->keyword) {
            // Search results can be cached longer as they're less likely to change
            return 45; // 45 minutes
        }

        if ($request->is_open !== null) {
            // Open/close status might change during business hours
            return 10; // 10 minutes
        }

        if ($request->latitude && $request->longitude) {
            // Location-based queries can be cached moderately
            return 20; // 20 minutes
        }

        if ($request->vendor_id) {
            // Specific vendor queries can be cached longer
            return 40; // 40 minutes
        }

        if ($request->category_id || $request->vendor_type_id) {
            // Category/vendor type filters can be cached longer
            return 35; // 35 minutes
        }

        // General listing cache time
        return $baseCacheTime;
    }
}
