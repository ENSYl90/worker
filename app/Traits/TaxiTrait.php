<?php

namespace App\Traits;

use App\Models\VehicleType;
use App\Services\DriverLocationService;
use Illuminate\Support\Facades\Http;

trait TaxiTrait
{
    use GoogleMapApiTrait;

    public function getTaxiOrderTotalPrice($vehicleType, $pickup, $dropoff)
    {

        //distance of trip
        $distance = $this->getRelativeDistance($pickup, $dropoff);
        //2 decimal places
        $distance = round($distance, 2);
        $drivingSpeed = setting("taxi.drivingSpeed", 50);
        //calculate the driving time and convert to minutes from hours
        $drivingTime = ($distance / $drivingSpeed) * 60;
        $drivingTime = ceil($drivingTime);

        $timeFare = $vehicleType->time_fare * $drivingTime;
        $distanceFare = $distance * $vehicleType->distance_fare;
        $totalTripFare = $vehicleType->base_fare + $timeFare + $distanceFare;
        //if the total amount is less than the set minimum fare by admin, then the min_fare should be used then
        if ($totalTripFare < $vehicleType->min_fare) {
            return $vehicleType->min_fare;
        } else {
            return  $totalTripFare;
        }
    }

    public function getRecalculatedTaxiOrderTotalPrice($order)
    {

        if (!((bool) setting('taxi.recalculateFare', false))) {
            return $order->total;
        }
        //
        $pickup = $order->taxi_order->pickup_latitude . "," . $order->taxi_order->pickup_longitude;
        $dropoff = $order->taxi_order->dropoff_latitude . "," . $order->taxi_order->dropoff_longitude;
        //get dropoff location from request
        if (request()->latlng) {
            $dropoff = request()->latlng;
        }
        //distance of trip
        $distance = $this->getRelativeDistance($pickup, $dropoff);
        //2 decimal places
        $distance = round($distance, 2);
        $drivingSpeed = setting("taxi.drivingSpeed", 50);
        //calculate the driving time and convert to minutes from hours
        $drivingTime = ($distance / $drivingSpeed) * 60;
        //check for the total new time for the trip
        $orderEnrouteStatusModel = $order->latestStatus('enroute');
        if ($orderEnrouteStatusModel != null) {
            $enrouteTime = $orderEnrouteStatusModel->created_at;
            $newDrivingTime = now()->diffInMinutes($enrouteTime);
        } else {
            $newDrivingTime = now()->diffInMinutes($order->getOriginal('updated_at'));
        }
        //
        $drivingTime = $newDrivingTime;
        $drivingTime = ceil($drivingTime);

        $vehicleType = VehicleType::find($order->taxi_order->vehicle_type_id);
        $timeFare = $vehicleType->time_fare * $drivingTime;
        $distanceFare = $distance * $vehicleType->distance_fare;
        $totalTripFare = $vehicleType->base_fare + $timeFare + $distanceFare;
        //if the total amount is less than the set minimum fare by admin, then the min_fare should be used then
        if ($totalTripFare < $vehicleType->min_fare) {
            return $vehicleType->min_fare;
        } else {
            return  $totalTripFare;
        }
    }




    public function getFareBreakdown($vehicleType, $pickup, $dropoff)
    {
        //distance of trip
        $distance = $this->getRelativeDistance($pickup, $dropoff);
        //2 decimal places
        $distance = round($distance, 2);
        $drivingSpeed = setting("taxi.drivingSpeed", 50);
        //calculate the driving time and convert to minutes from hours
        $drivingTime = ($distance / $drivingSpeed) * 60;
        $drivingTime = ceil($drivingTime);
        $vehicleType->trip_distance = $distance;
        $vehicleType->trip_time = $drivingTime;
        return $vehicleType;
    }

    public function getRecalculatedTaxiOrderBreakdown($order)
    {
        //Must return taxi_order
        $taxiOrder = $order->taxi_order;
        //distance of trip
        $pickup = $taxiOrder->pickup_latitude . "," . $taxiOrder->pickup_longitude;
        $dropoff = $taxiOrder->dropoff_latitude . "," . $taxiOrder->dropoff_longitude;
        $distance = $this->getRelativeDistance($pickup, $dropoff);
        //2 decimal places
        $distance = round($distance, 2);
        $drivingSpeed = setting("taxi.drivingSpeed", 50);
        //calculate the driving time and convert to minutes from hours
        $orderEnrouteStatusModel = $order->latestStatus('enroute');
        if ($orderEnrouteStatusModel != null) {
            $enrouteTime = $orderEnrouteStatusModel->created_at;
            $newDrivingTime = now()->diffInMinutes($enrouteTime);
            $drivingTime = ceil($newDrivingTime);
        } else {
            $drivingTime = ($distance / $drivingSpeed) * 60;
            $drivingTime = ceil($drivingTime);
        }
        $taxiOrder->trip_distance = $distance;
        $taxiOrder->trip_time = $drivingTime;
        return $taxiOrder;
    }




    //misc.
    public function includeNearestDriverEta(&$vehicleType, $pickupLatLng)
    {
        if (!isUsingWebsocket()) {
            return;
        }
        $lat1 = (float) explode(",", $pickupLatLng)[0];
        $lon1 = (float) explode(",", $pickupLatLng)[1];
        $maxDriverOrderNotificationAtOnce = (int) setting('maxDriverOrderNotificationAtOnce', 1);

        $driverDocuments = (new DriverLocationService())->getNearbyTaxiDrivers(
            $lat1,
            $lon1,
            driverSearchRadius(null),
            $maxDriverOrderNotificationAtOnce,
            $vehicleType->id,
        );
        $newDriverDocuments = collect($driverDocuments);
        $newDriverDocuments = $newDriverDocuments->sortBy('distance');
        $nearestDriverData = $newDriverDocuments->first();
        if ($nearestDriverData != null) {
            $expectedDriverSpeed = setting('taxi.drivingSpeed', 50);
            $driverDistance = $nearestDriverData["distance"] ?? 100;
            $etaInMinutes = round(($driverDistance / $expectedDriverSpeed) * 60);
            $etaInMinutes = (int) $etaInMinutes;
            if ($etaInMinutes <= 0) {
                $etaInMinutes = 5;
            }
            $vehicleType->nearest_driver_in_minutes = $etaInMinutes;
        }

        return;
    }
}
