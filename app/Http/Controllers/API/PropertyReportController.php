<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PropertyReport;
use App\Models\PropertyReportReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PropertyReportController extends Controller
{
    /**
     * Get list of report reasons
     */
    public function reasons()
    {
        $reasons = PropertyReportReason::active()->get();
        return response()->json($reasons);
    }

    /**
     * Submit a property report
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'property_report_reason_id' => 'required|exists:property_report_reasons,id',
            'details' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            // Check if already reported recently? Optionally.: Yes, to prevent mass reporting by same user.
            $existingReport = PropertyReport::where('user_id', Auth::id())
                ->where('property_id', $request->property_id)
                ->where('property_report_reason_id', $request->property_report_reason_id)
                ->where('created_at', '>=', now()->subHours(24))
                ->exists();

            if ($existingReport) {
                return response()->json([
                    'message' => __('You have already reported this property recently')
                ], 400);
            }

            $report = new PropertyReport();
            $report->user_id = Auth::id();
            $report->property_id = $request->property_id;
            $report->property_report_reason_id = $request->property_report_reason_id;
            $report->details = $request->details;
            $report->status = 'pending';
            $report->save();

            return response()->json([
                'message' => __('Property reported successfully'),
                'report' => $report
            ], 200);

        } catch (\Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage()
            ], 500);
        }
    }
}
