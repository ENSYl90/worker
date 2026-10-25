<?php

namespace App\Http\Livewire;

use App\Models\Vendor;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class MyBusinessDetailsLivewire extends BaseLivewireComponent
{
    public $model = Vendor::class;
    public $selectedModel;

    // Editable Fields
    public $name;
    public $description;
    public $phone;
    public $email;
    public $address;
    public $latitude;
    public $longitude;
    public $photo;
    public $secondPhoto;

    // Operational
    public $min_order;
    public $max_order;
    public $base_delivery_fee;
    public $delivery_fee;
    public $delivery_range;
    public $charge_per_km;
    public $pickup;
    public $delivery;
    public $allow_schedule_order;
    public $auto_accept;
    public $prepare_time;
    public $prepare_time_unit = "minutes";
    public $delivery_time;
    public $delivery_time_unit = "minutes";
    public $timeUnits = ["minutes", "hours", "days", "weeks", "months"];

    // Read-only / Display only properties
    public $commission;
    public $tax;
    public $vendor_type;
    public $is_active;
    public $photoInfo;
    public $secondPhotoInfo;

    // UI Helpers
    public $isPackageVendor = false;
    public $isServiceVendor = false;
    public $isBookingVendor = false;
    public $isEditing = false;


    public function toggleEdit()
    {
        $this->isEditing = !$this->isEditing;
        if ($this->isEditing) {
            $this->emit('initPhone', json_encode(["phoneEdit", "phone", $this->phone]));
            $this->emit('loadSummerNote', "editContent", $this->description);
            $this->emit('initialAddressSelected', $this->address);
        }
    }

    protected function rules()
    {
        return [
            "name" => "required|string",
            "description" => "required|string",
            // "phone" => "required|phone:" . setting('countryCode', "INTERNATIONAL") . "",
            "phone" => "required",
            "email" => "required|email|unique:vendors,email," . ($this->selectedModel->id ?? 0),
            "address" => "required|string",
            "latitude" => "required|numeric",
            "longitude" => "required|numeric",
            "photo" => "nullable|sometimes|image|max:" . setting("filelimit.vendor_logo", 2048),
            "secondPhoto" => "nullable|sometimes|image|max:" . setting("filelimit.vendor_feature", 2048),
            // Operational
            "min_order" => "nullable|numeric",
            "max_order" => "nullable|numeric",
            "base_delivery_fee" => "nullable|numeric",
            "delivery_fee" => "nullable|numeric",
            "delivery_range" => "nullable|numeric",
            "charge_per_km" => "nullable|boolean",
            "pickup" => "nullable|boolean",
            "delivery" => "nullable|boolean",
            "allow_schedule_order" => "nullable|boolean",
            "auto_accept" => "nullable|boolean",
            "prepare_time" => "nullable|numeric",
            "delivery_time" => "nullable|numeric",
        ];
    }

    public function mount()
    {
        $user = Auth::user();
        if (!$user->vendor_id) {
            return redirect()->route('dashboard');
        }

        $this->selectedModel = Vendor::findOrFail($user->vendor_id);

        // Populate fields
        $this->name = $this->selectedModel->name;
        $this->description = $this->selectedModel->description;
        $this->phone = $this->selectedModel->phone;
        $this->email = $this->selectedModel->email;
        $this->address = $this->selectedModel->address;
        $this->latitude = $this->selectedModel->latitude;
        $this->longitude = $this->selectedModel->longitude;

        $this->min_order = $this->selectedModel->min_order;
        $this->max_order = $this->selectedModel->max_order;
        $this->base_delivery_fee = $this->selectedModel->getRawOriginal("base_delivery_fee");
        $this->delivery_fee = $this->selectedModel->getRawOriginal("delivery_fee");
        $this->delivery_range = $this->selectedModel->getRawOriginal("delivery_range");
        $this->charge_per_km = $this->selectedModel->getRawOriginal("charge_per_km");
        $this->pickup = $this->selectedModel->pickup;
        $this->delivery = $this->selectedModel->delivery;
        $this->allow_schedule_order = $this->selectedModel->allow_schedule_order;
        $this->auto_accept = $this->selectedModel->auto_accept;

        $this->prepare_time = $this->selectedModel->prepare_time;
        $this->prepare_time_unit = $this->selectedModel->prepare_time_unit;
        $this->delivery_time = $this->selectedModel->delivery_time;
        $this->delivery_time_unit = $this->selectedModel->delivery_time_unit;

        // Read-only
        $this->commission = $this->selectedModel->commission;
        $this->tax = $this->selectedModel->tax;
        $this->is_active = $this->selectedModel->is_active;
        $this->vendor_type = $this->selectedModel->vendor_type;

        // Logic for types
        $this->isPackageVendor = $this->vendor_type->slug == "parcel";
        $this->isServiceVendor = $this->vendor_type->slug == "service";
        $this->isBookingVendor = $this->vendor_type->slug == "booking";

        // Emit events to initialize UI components after render
        $this->emit('initPhone', json_encode(["phoneEdit", "phone", $this->phone]));
        $this->emit('loadSummerNote', "editContent", $this->description);
        $this->emit('initialAddressSelected', $this->address);
    }

    public function render()
    {
        return view('livewire.my-business-details');
    }

    public function update()
    {
        $this->validate();

        try {
            DB::beginTransaction();
            $model = $this->selectedModel;
            $model->name = $this->name;
            $model->description = $this->description;
            $model->phone = $this->phone;
            $model->email = $this->email;
            $model->address = $this->address;
            $model->latitude = $this->latitude;
            $model->longitude = $this->longitude;

            // Operational
            $model->min_order = $this->min_order;
            $model->max_order = $this->max_order;
            $model->base_delivery_fee = $this->base_delivery_fee;
            $model->delivery_fee = $this->delivery_fee;
            $model->delivery_range = $this->delivery_range;
            $model->charge_per_km = $this->charge_per_km ? 1 : 0;
            $model->pickup = $this->pickup ? 1 : 0;
            $model->delivery = $this->delivery ? 1 : 0;
            $model->allow_schedule_order = $this->allow_schedule_order ? 1 : 0;
            $model->auto_accept = $this->auto_accept ? 1 : 0;

            $model->prepare_time = $this->prepare_time;
            $model->prepare_time_unit = $this->prepare_time_unit;
            $model->delivery_time = $this->delivery_time;
            $model->delivery_time_unit = $this->delivery_time_unit;

            $model->save();

            // Images
            if ($this->photo) {
                $model->clearMediaCollection("logo");
                $model->addMedia($this->photo->getRealPath())
                    ->usingFileName(genFileName($this->photo))
                    ->toMediaCollection("logo");
                $this->photo = null;
            }
            if ($this->secondPhoto) {
                $model->clearMediaCollection("feature_image");
                $model->addMedia($this->secondPhoto->getRealPath())
                    ->usingFileName(genFileName($this->secondPhoto))
                    ->toMediaCollection("feature_image");
                $this->secondPhoto = null;
            }

            DB::commit();
            $this->isEditing = false;
            $this->showSuccessAlert(__("Business details updated successfully!"));
        } catch (Exception $e) {
            DB::rollback();
            $this->showErrorAlert($e->getMessage());
        }
    }

    public function autocompleteAddressSelected($data)
    {
        $this->address = $data["address"];
        $this->latitude = $data["latitude"];
        $this->longitude = $data["longitude"];
    }
}
