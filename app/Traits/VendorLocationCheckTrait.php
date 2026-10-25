<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use App\Models\CityVendor;
use App\Models\StateVendor;
use App\Models\CountryVendor;
use App\Models\DeliveryAddress;
use App\Http\Controllers\API\GeocoderController;
use Illuminate\Http\Request;

trait VendorLocationCheckTrait
{
    use GeoBoundaryCheckTrait;

    public function isLocationAllowedByVendor($vendorId, $stop)
    {
        if (!is_array($stop)) {
            $stopId = $stop;
            $deliveryAddress = DeliveryAddress::find($stopId);
            $lat = $deliveryAddress->latitude;
            $lng = $deliveryAddress->longitude;
        } else {
            $lat = $stop['lat'] ?? $stop['latitude'];
            $lng = $stop['lng'] ?? $stop['long'] ?? $stop['longitude'];
        }

        try {
            //city check
            $found = $this->vendorCityLocationCheck($lat, $lng, $vendorId);
            if ($found) {
                return true;
            }
        } catch (\Exception $e) {
            // logger("Error city check", [$e]);
        }

        try {
            //state check
            $found = $this->vendorStateLocationCheck($lat, $lng, $vendorId);
            if ($found) {
                return true;
            }
        } catch (\Exception $e) {
            // logger("Error state check", [$e]);
        }

        try {
            //country check
            $found = $this->vendorCountryLocationCheck($lat, $lng, $vendorId);
            if ($found) {
                return true;
            }
        } catch (\Exception $e) {
            // logger("Error country check", [$e]);
        }

        return false;
    }

    //for raw city, state and country
    public function isStopServiceByVendor($vendorId, $location)
    {
        $lat = $location["latitude"] ?? $location["lat"];
        $lng = $location["longitude"] ?? $location["lng"] ?? $location["long"];
        try {
            //check iof city is even in the system
            $found = $this->vendorCityLocationCheck($lat, $lng, $vendorId);
            if ($found) {
                return true;
            }
        } catch (\Exception $e) {
            logger("Error city check", [$e]);
        }

        try {
            //now check if delivery state is in the system
            $found = $this->vendorStateLocationCheck($lat, $lng, $vendorId);
            if ($found) {
                return true;
            }
        } catch (\Exception $e) {
            logger("Error state check", [$e]);
        }

        try {
            //now check if delivery country is in the system
            $found = $this->vendorCountryLocationCheck($lat, $lng, $vendorId);
            if ($found) {
                return true;
            }
        } catch (\Exception $e) {
            logger("Error country check", [$e]);
        }

        return false;
    }

    public function vendorCityLocationCheck($lat, $lng, $vendorId)
    {
        $start = microtime(true);

        try {
            $vendorCities = CityVendor::where('vendor_id', $vendorId)
                ->where('is_active', 1)
                ->get();
            $yesVendorHasCitySupport = false;
            foreach ($vendorCities as $vendorCity) {
                if ($vendorCity->city && $vendorCity->city->boundaries) {
                    $yesVendorHasCitySupport = $this->isLocationInBoundaries($vendorCity->city->boundaries, $lat, $lng);
                    if ($yesVendorHasCitySupport) {
                        break;
                    }
                }
            }

            return $yesVendorHasCitySupport;
        } catch (\Exception $ex) {
            logger("Failed", [$ex]);
            $this->logExecutionTime($start, 'Failed');
            return false;
        }
    }

    public function vendorStateLocationCheck($lat, $lng, $vendorId)
    {
        $start = microtime(true);

        try {
            $vendorStates = StateVendor::where('vendor_id', $vendorId)
                ->where('is_active', 1)
                ->get();

            $yesVendorHasStateSupport = false;
            foreach ($vendorStates as $vendorState) {
                if ($vendorState->state && $vendorState->state->boundaries) {
                    $yesVendorHasStateSupport = $this->isLocationInBoundaries($vendorState->state->boundaries, $lat, $lng);
                    if ($yesVendorHasStateSupport) {
                        break;
                    }
                }
            }
            return $yesVendorHasStateSupport;
        } catch (\Exception $ex) {
            logger("Failed", [$ex]);
            $this->logExecutionTime($start, 'Failed');
            return false;
        }
    }

    public function vendorCountryLocationCheck($lat, $lng, $vendorId)
    {
        $start = microtime(true);

        try {
            $vendorCountries = CountryVendor::where('vendor_id', $vendorId)
                ->where('is_active', 1)
                ->get();
            $yesVendorHasCountrySupport = false;
            foreach ($vendorCountries as $vendorCountry) {
                if ($vendorCountry->country && $vendorCountry->country->boundaries) {
                    $yesVendorHasCountrySupport = $this->isLocationInBoundaries($vendorCountry->country->boundaries, $lat, $lng);
                    if ($yesVendorHasCountrySupport) {
                        break;
                    }
                }
            }

            return $yesVendorHasCountrySupport;
        } catch (\Exception $ex) {
            logger("Failed", [$ex]);
            $this->logExecutionTime($start, 'Failed');
            return false;
        }
    }

    /**
     * Log execution time in milliseconds
     */
    protected function logExecutionTime($start, $label)
    {
        $time = round((microtime(true) - $start) * 1000, 2); // in ms
        Log::info("[vendorCityLocationCheck] {$label} - Execution time: {$time} ms");
    }

    public function fetchMissingLocationInfo($location)
    {
        //location is array
        //if city or state or country is missing
        $isCityMissing = empty($location["city"]);
        $isStateMissing = empty($location["state"]);
        $isCountryMissing = empty($location["country"]);
        $administrtiveAreaMissing = $isCityMissing || $isStateMissing || $isCountryMissing;
        //
        if ($administrtiveAreaMissing) {
            //
            $city = null;
            $state = null;
            $country = null;
            //
            $geoController = new GeocoderController();
            $infoRequest = new Request();
            $lat = $location["latitude"] ?? $location["lat"];
            $lng = $location["longitude"] ?? $location["lng"] ?? $location["long"];
            $infoRequest->merge(["lat" => $lat, "lng" => $lng]);
            $coordinateInfo = $geoController->forward($infoRequest);
            //addresses array returned in an array with data key
            $addresses = $coordinateInfo->getData(true)["data"];
            //loop through to get the
            foreach ($addresses as $address) {
                $city = $city ?? $address["subLocality"] ?? $address["locality"];
                $state = $state ?? $address["administrative_area_level_1"] ?? $address["administrative_area_level_2"];
                $country = $country ?? $address["country"];
            }

            //update the location wih the missing info
            if ($isCityMissing) {
                $location["city"] = $city;
            }

            if ($isStateMissing) {
                $location["state"] = $state;
            }

            if ($isCountryMissing) {
                $location["country"] = $country;
            }
        }
        //
        return $location;
    }


    public function isLocationInBoundaries($boundaries, $lat, $lng): bool
    {
        $cLatLng = [
            "lat" => $lat,
            "lng" => $lng,
        ];
        $yesIsIn = false;
        $isMultiPolygon = $this->isMultiPolygon($boundaries);
        if ($isMultiPolygon) {
            $polygonPoints = $this->getMultiPolygon($boundaries);
            foreach ($polygonPoints as $polygonPoint) {
                if ($this->insideBound($cLatLng, $polygonPoint)) {
                    $yesIsIn = true;
                    break;
                }
            }
        } else {
            $points = $this->formatBoundaries($boundaries);
            if ($this->insideBound($cLatLng, $points)) {
                //how to return the country to the calling function
                $yesIsIn = true;
            }
        }

        return $yesIsIn;
    }
}
