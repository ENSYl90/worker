<?php

namespace App\Http\Controllers\API;

use App\Events\DriverLocationGeohashUpdatedEvent;
use App\Events\WebsocketDriverLocationUpdatedEvent;
use App\Http\Controllers\Controller;
use App\Models\DriverLocation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\DriverLocationRedisTrait;

class DriverLocationSyncController extends Controller
{
    use DriverLocationRedisTrait;

    //
    public function store(Request $request)
    {

        //prevent calling this if websocket option is not enabled
        //TODO: Add this for production release
        if (!isUsingWebsocket()) {
            logger("Driver location syncing but websocket system is not allowed");
            return response()->json([
                "message" => __("Service not allowed"),
            ], 400);
        }


        $validator = Validator::make(
            $request->all(),
            [
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'rotation' => 'nullable|numeric',
            ],
        );

        if ($validator->fails()) {
            return response()->json([
                "message" => $this->readalbeError($validator),
            ], 400);
        }

        try {
            $user = Auth::user();
            $vehicleTypeId = $user->vehicle ? $user->vehicle->vehicle_type_id : null;

            //for record sake, save to db
            $driverLocation = DriverLocation::updateOrCreate(
                ['driver_id' => Auth::id()],
                [
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                    'rotation' => $request->rotation ?? 0,
                ]
            );

            //save to redis too, for faster access through the order matchin jobs
            if (isQueueRedis()) {
                $this->saveDriverLocationToRedis(
                    $user->id,
                    $request->lat,
                    $request->lng,
                    $request->rotation ?? 0,
                    $vehicleTypeId
                );

                // Simulate driver location object structure for the event below
                $driverLocation = new DriverLocation([
                    'driver_id' => $user->id,
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                    'rotation' => $request->rotation ?? 0,
                ]);
            }

            //send event of driver location sync
            event(new WebsocketDriverLocationUpdatedEvent(Auth::id(), $driverLocation->toArray()));
            event(new DriverLocationGeohashUpdatedEvent(Auth::id(), $driverLocation->toArray(), null, $vehicleTypeId));

            return response()->json([
                "success" => true,
            ], 200);
        } catch (Exception $ex) {
            logger("Error syncing driver location", [$ex]);
            return response()->json([
                "message" => __("Try again later"),
            ], 500);
        }
    }
}
