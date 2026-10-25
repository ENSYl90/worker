<?php

namespace App\Http\Livewire;

use App\Http\Controllers\API\TaxiOrderController;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\VehicleType;
use App\Traits\GoogleMapApiTrait;
use App\Traits\TaxiTrait;
use Illuminate\Support\Facades\DB;
use Exception;

class NewTaxiOrderLivewire extends BaseLivewireComponent
{

    use GoogleMapApiTrait, TaxiTrait;

    public $user;
    public $payment_method_id;
    public $pickup_address;
    public $dropoff_address;
    public $vehicle_type_id;
    public $vehicleType;
    public $coupon_code;

    // amounts
    public $amount;
    public $discount;
    public $total_amount;
    public $schedule_date;
    public $schedule_time;



    protected $rules = [
        "user" => "required",
        "payment_method_id" => "required|exists:payment_methods,id",
        "vehicle_type_id" => "required|exists:vehicle_types,id",
        "pickup_address" => "required",
        "dropoff_address" => "required",
        'schedule_date' => [
            'nullable',                // field can be empty
            'required_with:schedule_time', // required if schedule_time is present
            'date',                    // must be a valid date
        ],
        'schedule_time' => [
            'nullable',
            'required_with:schedule_date', // required if schedule_date is present
            'date_format:H:i',             // valid time
        ],
    ];


    public function getListeners()
    {
        return $this->listeners + [
            'selectedCoordinates' => 'selectedCoordinates',
            'user_idUpdated' => 'userSelected',
            'pickup_addressUpdated' => 'pickupAddressSelected',
            'dropoff_addressUpdated' => 'dropoffAddressSelected',
        ];
    }


    public function render()
    {
        return view('livewire.taxi_new_order');
    }




    // createOrder function to save order
    public function createOrder()
    {
        
        //show error alert if user is null
        $this->validate();

        //create DB transaction with try catch
        try {
            DB::beginTransaction();
            $taxiOrderController = new TaxiOrderController();
            $request = new \Illuminate\Http\Request();
            $request->merge([
                'user_id' => $this->user->id,
                'vehicle_type_id' => $this->vehicle_type_id,
                'payment_method_id' => $this->payment_method_id,
                'pickup' => [
                    "lat" => $this->pickup_address['geometry']['location']['lat'],
                    "lng" => $this->pickup_address['geometry']['location']['lng'],
                    "address" => $this->pickup_address['formatted_address'],
                ],
                'dropoff' => [
                    "lat" => $this->dropoff_address['geometry']['location']['lat'],
                    "lng" => $this->dropoff_address['geometry']['location']['lng'],
                    "address" => $this->dropoff_address['formatted_address'],
                ],
                'sub_total' => $this->amount,
                'discount' => $this->discount,
                'coupon_code' => $this->coupon_code,
                'total' => $this->total_amount,
                "pickup_date" => $this->schedule_date,
                "pickup_time" => $this->schedule_time,
            ]);
            $response = $taxiOrderController->book($request);
            if ($response->getStatusCode() != 200) {
                logger("error", [$response->getData(true)['message']]);
                throw new Exception($response->getData(true)['message'] ?? __('Something went wrong'));
            }

            DB::commit();
            
            // $this->showSuccessAlert(__('Order created successfully'));
            // $this->reset();
            $this->flashSuccess(__('Order created successfully'));
            return redirect()->route("taxi.order.new");
        } catch (Exception $e) {
            DB::rollBack();
            $this->showErrorAlert($e->getMessage());
        }
    }

    //PROPERTY
    public function getShowOrderButtonProperty()
    {
        $validated = true;
        if (!$this->user) {
            // $this->showErrorAlert(__('Please select user'));
            $this->addError("user", __('Please select user'));
            $validated = false;
        }
        //show error alert if payment method is null
        if (!$this->payment_method_id) {
            $this->addError("payment_method_id", __('Please select user'));
            $validated = false;
            $this->showErrorAlert(__('Please select payment method'));
            return;
        }
        //show error alert if pickup address is null
        if (!$this->pickup_address) {
            $this->showErrorAlert(__('Please select pickup address'));
            return;
        }
        //show error alert if dropoff address is null
        if (!$this->dropoff_address) {
            $this->showErrorAlert(__('Please select dropoff address'));
            return;
        }
        //show error alert if vehicle type is null
        if (!$this->vehicleType) {
            $this->showErrorAlert(__('Please select vehicle type'));
            return;
        }
        //show error alert if amount is null
        if (!$this->amount || !$this->total_amount) {
            $this->showErrorAlert(__('Please select pickup and dropoff address'));
            return;
        }

        return $validated;
    }

    public function getPaymentMethodsProperty()
    {
        return PaymentMethod::active()->where("use_taxi", 1)->select("id", "name")->get();
    }
    public function getVehicleTypesProperty()
    {
        return VehicleType::active()->select("id", "name")->get();
    }

    //events listener
    public function userSelected($data)
    {
        $this->user = User::find($data['value']);
        $this->resetErrorBag("user");
    }



    public function pickupAddressSelected($data)
    {
        try {

            try {
                $addressData = decrypt($data['value']);
            } catch (Exception $ex) {
                $addressData = json_decode($data['value'], true);
            }
            $this->pickup_address = $addressData;
            // check if address is within taxi service area
            $this->pickup_address = $this->checkAddressWithinServiceArea($this->pickup_address);
            $this->resetErrorBag("pickup_address");
        } catch (Exception $e) {
            logger($e->getMessage());
            $this->pickup_address = null;
        }
    }

    public function dropoffAddressSelected($data)
    {
        try {

            try {
                $addressData = decrypt($data['value']);
            } catch (Exception $ex) {
                $addressData = json_decode($data['value'], true);
            }

            $this->dropoff_address = $addressData;
            // check if address is within taxi service area
            $this->dropoff_address = $this->checkAddressWithinServiceArea($this->dropoff_address);
            $this->resetErrorBag("dropoff_address");
        } catch (Exception $e) {
            logger($e->getMessage());
            $this->dropoff_address = null;
        }
    }

    public function generateSummary()
    {
        $this->validate();
        $this->amount = 0;
        $this->total_amount = 0;
        $this->vehicleType = VehicleType::find($this->vehicle_type_id);
        if ($this->pickup_address && $this->dropoff_address && $this->vehicleType) {
            $pickupLocation = $this->pickup_address['geometry']['location']['lat'] . ',' . $this->pickup_address['geometry']['location']['lng'];
            $dropoffLocation = $this->dropoff_address['geometry']['location']['lat'] . ',' . $this->dropoff_address['geometry']['location']['lng'];

            $this->amount = $this->getTaxiOrderTotalPrice($this->vehicleType, $pickupLocation, $dropoffLocation);
            $this->total_amount = $this->amount - $this->discount;
        } else {
            $this->discount = 0;
        }
    }



    //Utils functions
    public function checkAddressWithinServiceArea($address)
    {
        $lat = $address['geometry']['location']['lat'];
        $lng = $address['geometry']['location']['lng'];
        $taxiOrderController = new TaxiOrderController();
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'latitude' => $lat,
            'longitude' => $lng,
        ]);
        $response = $taxiOrderController->location_available($request);
        if ($response->getStatusCode() != 200) {
            $this->showErrorAlert(__('Address is not within service area'));
            return null;
        }
        return $address;
    }
}
