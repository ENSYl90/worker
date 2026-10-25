<?php

namespace App\Http\Controllers\API;

use App\Events\WebsocketDriverLocationUpdatedEvent;
use App\Http\Controllers\Controller;
use App\Models\DriverLocation;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackOrderController extends Controller
{

    //
    public function track(Request $request)
    {

        $vendorTypeId = $request->vendor_type_id;

        //
        try {
            $order = Order::fullData()->where("code", $request->code)
                ->when($vendorTypeId, function ($query) use ($vendorTypeId) {
                    return $query->whereHas("vendor", function ($query) use ($vendorTypeId) {
                        return $query->where('vendor_type_id', $vendorTypeId);
                    });
                })->firstOrFail();

            //
            return $order;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $error) {
            return response()->json([
                "message" => __("Invalid tracking code")
            ], 400);
        }
    }


    //
    public function syncDriverLocation(Request $request, Order $order)
    {
        //
        if (Auth::id() != $order->user_id && Auth::id() != $order->driver_id) {
            return response()->json(
                ['error' => 'Unauthorized'],
                401,
            );
        }

        $driverLocation = DriverLocation::getForDriver($order->driver_id);
        if (!$driverLocation) {
            return response()->json(
                ['message' => 'No driver location available yet.',],
                404,
            );
        }

        //send event of driver location sync
        event(
            new WebsocketDriverLocationUpdatedEvent(
                $order->driver_id,
                $driverLocation->toArray(),
            ),
        );

        return response()->json([
            'message' => 'Driver location synced successfully.',
            'location' => $driverLocation,
        ]);
    }
}