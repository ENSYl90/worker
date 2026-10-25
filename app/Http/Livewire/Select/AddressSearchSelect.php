<?php

namespace App\Http\Livewire\Select;

use App\Http\Controllers\API\GeocoderController;
use Illuminate\Support\Collection;
use App\Models\Vendor;

class AddressSearchSelect extends BaseLivewireSelect
{


    public function options($searchTerm = null): Collection
    {

        $request = new \Illuminate\Http\Request();
        $request->replace(['keyword' => $searchTerm]);
        $geocoderController = new GeocoderController();
        $result = $geocoderController->reverse($request);
        $addresses = $result->getData(true)['data'] ?? [];
        return collect($addresses)->map(function ($address) {
            $itemValue = encrypt($address);
            return [
                'value' => $itemValue, //json_encode($address),
                'description' => $address['formatted_address'],
            ];
        });
    }


    public function selectedOption($value)
    {
        try {
            $address = decrypt($value);
        } catch (\Exception $ex) {
            try {
                $address = json_decode($value, true);
            } catch (\Exception $ex) {
                throw new \Exception(__("Error Selecting address"), 1);
            }
        }

        return [
            'value' => $value,
            'description' => $address['formatted_address'],
        ];
    }
}
