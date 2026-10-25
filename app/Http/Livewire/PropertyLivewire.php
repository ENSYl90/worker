<?php

namespace App\Http\Livewire;

use App\Models\Amenity;
use App\Models\CancellationPolicy;
use App\Models\Property;
use App\Models\Vendor;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropertyLivewire extends BaseLivewireComponent
{
    public $model = Property::class;

    // Modal Management
    public $showCreate = false;

    public $showEdit = false;

    public $showDetails = false;

    // Basic info
    public $name;

    public $description;

    public $vendor_id;

    public $property_type_id;

    public $base_price;

    public $discount_price = 0;

    // Location
    public $address;

    public $latitude;

    public $longitude;

    public $city;

    public $state;

    public $country;

    public $postal_code;

    // Times
    public $check_in_time_start = '14:00';

    public $check_in_time_end = '22:00';

    public $check_out_time = '11:00';

    // Capacity
    public $max_guests = 1;

    public $bedrooms = 1;

    public $bathrooms = 1;

    public $beds = 1;

    public $square_meters;

    // Settings
    public $is_active = true;

    public $featured = false;

    public $is_instant_booking = false;

    public $is_host_residing = false;

    public $minimum_nights = 1;

    public $maximum_nights;

    public $preparation_time = 0;

    public $cancellation_policy_id;

    public $house_rules;

    // Relations
    public $selectedAmenities = [];

    // Data sources
    public $vendor_id_selected;

    public $property_type_id_selected;

    public $cancellation_policy_id_selected;

    public $selectedAmenities_ids = [];

    public $photos = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'vendor_id' => 'required|exists:vendors,id',
        'property_type_id' => 'required|exists:property_types,id',
        'base_price' => 'required|numeric|min:0',
        'discount_price' => 'nullable|numeric|min:0',
        'photos' => 'nullable|array',
        'photos.*' => 'nullable|image|max:10240',
        'address' => 'required|string',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'city' => 'nullable|string|max:255',
        'state' => 'nullable|string|max:255',
        'country' => 'nullable|string|max:255',
        'postal_code' => 'nullable|string|max:20',
        'check_in_time_start' => 'required',
        'check_in_time_end' => 'required',
        'check_out_time' => 'required',
        'max_guests' => 'required|integer|min:1',
        'bedrooms' => 'required|integer|min:0',
        'bathrooms' => 'required|integer|min:0',
        'beds' => 'required|integer|min:0',
        'square_meters' => 'required|integer|min:0',
        'is_active' => 'boolean',
        'featured' => 'boolean',
        'is_instant_booking' => 'boolean',
        'is_host_residing' => 'boolean',
        'minimum_nights' => 'required|integer|min:1',
        'maximum_nights' => 'required|integer|min:1|gte:minimum_nights',
        'preparation_time' => 'required|integer|min:0',
        'cancellation_policy_id' => 'nullable|exists:cancellation_policies,id',
        'house_rules' => 'nullable|string',
    ];

    public function getListeners()
    {
        return $this->listeners + [
            'vendorSelected' => 'vendorSelected',
            'propertyTypeSelected' => 'propertyTypeSelected',
            'cancellationPolicySelected' => 'cancellationPolicySelected',
            'amenitiesSelected' => 'amenitiesSelected',
            'autocompleteAddressSelected' => 'autocompleteAddressSelected',
            'showDetailsModal' => 'showDetailsModal',
        ];
    }

    public function showDetailsModal($id)
    {
        $this->selectedModel = $this->model::with(['vendor', 'propertyType', 'amenities', 'cancellationPolicy'])->find($id);
        $this->showDetails = true;
    }

    public function getPropertyTypesProperty()
    {
        return \App\Models\PropertyType::active()
            ->get()
            ->map(function ($type) {
                return ['id' => $type->id, 'name' => $type->name];
            })
            ->toArray();
    }

    public function getVendorsProperty()
    {
        return Vendor::active()
            ->whereHas('vendor_type', function ($query) {
                return $query->where('slug', 'booking');
            })
            ->get()
            ->map(function ($vendor) {
                return ['id' => $vendor->id, 'name' => $vendor->name];
            })
            ->toArray();
    }

    public function getCancellationPoliciesProperty()
    {
        return CancellationPolicy::active()
            ->get()
            ->map(function ($policy) {
                return ['id' => $policy->id, 'name' => $policy->name];
            })
            ->toArray();
    }

    public function getAmenitiesProperty()
    {
        return Amenity::with('categories')
            ->get()
            ->groupBy(function ($amenity) {
                return $amenity->categories->first()->name ?? 'Other';
            })
            ->toArray();
    }

    public function getAmenitiesListProperty()
    {
        return Amenity::all()
            ->map(function ($amenity) {
                return ['id' => $amenity->id, 'name' => $amenity->name];
            })
            ->toArray();
    }

    public function showCreateModal()
    {
        parent::showCreateModal();
        $this->emit('initialAddressSelected', '');
        $this->loadSelects();
    }

    public function loadSelects()
    {
        // Load vendors
        if ($this->showCreate) {
            $this->showSelect2(
                '#newVendorSelect',
                $this->vendor_id_selected,
                'vendorSelected',
                $this->vendors ?? []
            );

            $this->showSelect2(
                '#newPropertyTypeSelect',
                $this->property_type_id_selected,
                'propertyTypeSelected',
                $this->propertyTypes ?? []
            );

            $this->showSelect2(
                '#newCancellationPolicySelect',
                $this->cancellation_policy_id_selected,
                'cancellationPolicySelected',
                $this->cancellationPolicies ?? []
            );

            $this->showSelect2(
                '#newAmenitiesSelect',
                $this->selectedAmenities_ids,
                'amenitiesSelected',
                $this->amenitiesList ?? []
            );
        } else {
            $this->showSelect2(
                '#editVendorSelect',
                $this->vendor_id_selected,
                'vendorSelected',
                $this->vendors ?? []
            );

            $this->showSelect2(
                '#editPropertyTypeSelect',
                $this->property_type_id_selected,
                'propertyTypeSelected',
                $this->propertyTypes ?? []
            );

            $this->showSelect2(
                '#editCancellationPolicySelect',
                $this->cancellation_policy_id_selected,
                'cancellationPolicySelected',
                $this->cancellationPolicies ?? []
            );

            $this->showSelect2(
                '#editAmenitiesSelect',
                $this->selectedAmenities_ids,
                'amenitiesSelected',
                $this->amenitiesList ?? []
            );
        }
    }

    public function vendorSelected($vendor)
    {
        $this->vendor_id = is_array($vendor) ? ($vendor[0] ?? null) : $vendor;
        $this->vendor_id_selected = $this->vendor_id;
    }

    public function propertyTypeSelected($propertyType)
    {
        $this->property_type_id = is_array($propertyType) ? ($propertyType[0] ?? null) : $propertyType;
        $this->property_type_id_selected = $this->property_type_id;
    }

    public function cancellationPolicySelected($policy)
    {
        $this->cancellation_policy_id = is_array($policy) ? ($policy[0] ?? null) : $policy;
        $this->cancellation_policy_id_selected = $this->cancellation_policy_id;
    }

    public function amenitiesSelected($amenities)
    {
        if ($this->selectedAmenities_ids == null) {
            $this->selectedAmenities_ids = [];
        }
        $this->selectedAmenities_ids = $amenities;
        $this->selectedAmenities = $amenities;
    }

    public function removeExistingPhoto($index)
    {
        if ($this->selectedModel && isset($this->selectedModel->photos[$index])) {
            $media = $this->selectedModel->getMedia('property_photos')->get($index);
            if ($media) {
                $this->photosToDelete[] = $media->id;
            }
        }
    }

    public function render()
    {
        return view('livewire.property');
    }

    public function save()
    {

        $this->validatePhotos();
        if (empty($this->vendor_id)) {
            $this->vendor_id = \Auth::user()->vendor_id;
        }

        $this->validate();

        try {
            // $this->isDemo();
            DB::beginTransaction();

            $model = new Property;
            $model->name = $this->name;
            $model->slug = Str::slug($this->name);
            $model->description = $this->description;
            $model->vendor_id = $this->vendor_id;
            $model->property_type_id = $this->property_type_id;
            $model->base_price = $this->base_price;
            $model->discount_price = $this->discount_price;
            $model->address = $this->address;
            $model->latitude = $this->latitude;
            $model->longitude = $this->longitude;
            $model->city = $this->city;
            $model->state = $this->state;
            $model->country = $this->country;
            $model->postal_code = $this->postal_code;
            $model->check_in_time_start = $this->check_in_time_start;
            $model->check_in_time_end = $this->check_in_time_end;
            $model->check_out_time = $this->check_out_time;
            $model->max_guests = $this->max_guests;
            $model->bedrooms = $this->bedrooms;
            $model->bathrooms = $this->bathrooms;
            $model->beds = $this->beds;
            $model->square_meters = $this->square_meters;
            $model->is_active = $this->is_active ?? false;
            $model->featured = $this->featured ?? false;
            $model->is_instant_booking = $this->is_instant_booking ?? false;
            $model->is_host_residing = $this->is_host_residing ?? false;
            $model->minimum_nights = $this->minimum_nights;
            $model->maximum_nights = $this->maximum_nights;
            $model->preparation_time = $this->preparation_time;
            $model->cancellation_policy_id = $this->cancellation_policy_id;
            $model->house_rules = $this->house_rules;
            $model->save();

            // Attach amenities
            if (! empty($this->selectedAmenities)) {
                $model->amenities()->sync($this->selectedAmenities);
            }

            // Handle photo uploads
            if ($this->photos) {
                $model->clearMediaCollection();
                foreach ($this->photos as $photo) {
                    $model->addMedia($photo)
                        ->usingFileName(genFileName($photo))
                        ->toMediaCollection();
                }
                $this->photos = null;
            }

            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__('Property').' '.__('created successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __('Property').' '.__('creation failed!'));
        }
    }

    public function initiateEdit($id)
    {
        $this->selectedModel = $this->model::with('amenities')->find($id);

        $this->name = $this->selectedModel->name;
        $this->description = $this->selectedModel->description;
        $this->vendor_id = $this->selectedModel->vendor_id;
        $this->vendor_id_selected = $this->selectedModel->vendor_id;
        $this->property_type_id = $this->selectedModel->property_type_id;
        $this->property_type_id_selected = $this->selectedModel->property_type_id;
        $this->base_price = $this->selectedModel->base_price;
        $this->discount_price = $this->selectedModel->discount_price;
        $this->address = $this->selectedModel->address;
        $this->latitude = $this->selectedModel->latitude;
        $this->longitude = $this->selectedModel->longitude;
        $this->city = $this->selectedModel->city;
        $this->state = $this->selectedModel->state;
        $this->country = $this->selectedModel->country;
        $this->postal_code = $this->selectedModel->postal_code;
        $this->check_in_time_start = $this->selectedModel->check_in_time_start;
        $this->check_in_time_end = $this->selectedModel->check_in_time_end;
        $this->check_out_time = $this->selectedModel->check_out_time;
        $this->max_guests = $this->selectedModel->max_guests;
        $this->bedrooms = $this->selectedModel->bedrooms;
        $this->bathrooms = $this->selectedModel->bathrooms;
        $this->beds = $this->selectedModel->beds;
        $this->square_meters = $this->selectedModel->square_meters;
        $this->is_active = $this->selectedModel->is_active;
        $this->featured = $this->selectedModel->featured;
        $this->is_instant_booking = $this->selectedModel->is_instant_booking;
        $this->is_host_residing = $this->selectedModel->is_host_residing;
        $this->minimum_nights = $this->selectedModel->minimum_nights;
        $this->maximum_nights = $this->selectedModel->maximum_nights;
        $this->preparation_time = $this->selectedModel->preparation_time;
        $this->cancellation_policy_id = $this->selectedModel->cancellation_policy_id;
        $this->cancellation_policy_id_selected = $this->selectedModel->cancellation_policy_id;
        $this->house_rules = $this->selectedModel->house_rules;
        $this->selectedAmenities = $this->selectedModel->amenities->pluck('id')->toArray();
        $this->selectedAmenities_ids = $this->selectedModel->amenities->pluck('id')->toArray();

        // Reset photos

        $this->emit('showEditModal');
        $this->emit('initialAddressSelected', $this->address);
        parent::showEditModal();
        $this->loadSelects();
    }

    public function update()
    {
        $this->validatePhotos();
        if (empty($this->vendor_id)) {
            $this->vendor_id = \Auth::user()->vendor_id;
        }

        $this->validate();

        try {
            // $this->isDemo();
            DB::beginTransaction();

            $model = $this->selectedModel;
            $model->name = $this->name;
            $model->slug = Str::slug($this->name);
            $model->description = $this->description;
            $model->vendor_id = $this->vendor_id;
            $model->property_type_id = $this->property_type_id;
            $model->base_price = $this->base_price;
            $model->discount_price = $this->discount_price;
            $model->address = $this->address;
            $model->latitude = $this->latitude;
            $model->longitude = $this->longitude;
            $model->city = $this->city;
            $model->state = $this->state;
            $model->country = $this->country;
            $model->postal_code = $this->postal_code;
            $model->check_in_time_start = $this->check_in_time_start;
            $model->check_in_time_end = $this->check_in_time_end;
            $model->check_out_time = $this->check_out_time;
            $model->max_guests = $this->max_guests;
            $model->bedrooms = $this->bedrooms;
            $model->bathrooms = $this->bathrooms;
            $model->beds = $this->beds;
            $model->square_meters = $this->square_meters;
            $model->is_active = $this->is_active ?? false;
            $model->featured = $this->featured ?? false;
            $model->is_instant_booking = $this->is_instant_booking ?? false;
            $model->is_host_residing = $this->is_host_residing ?? false;
            $model->minimum_nights = $this->minimum_nights;
            $model->maximum_nights = $this->maximum_nights;
            $model->preparation_time = $this->preparation_time;
            $model->cancellation_policy_id = $this->cancellation_policy_id;
            $model->house_rules = $this->house_rules;
            $model->save();

            // Update amenities
            if (! empty($this->selectedAmenities)) {
                $model->amenities()->sync($this->selectedAmenities);
            } else {
                $model->amenities()->sync([]);
            }

            // Handle photo deletions
            if ($this->photos) {
                $model->clearMediaCollection();
                foreach ($this->photos as $photo) {
                    $model->addMedia($photo)
                        ->usingFileName(genFileName($photo))
                        ->toMediaCollection();
                }
                $this->photos = null;
            }

            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__('Property').' '.__('updated successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __('Property').' '.__('update failed!'));
        }
    }

    // misc.
    public function validatePhotos()
    {
        // check the length of the selected photos
        $maxPhotoCount = (int) setting('filelimit.max_product_images', 3);
        if ($this->photos != null && count($this->photos) > $maxPhotoCount) {
            $errorMsg = __('You can only select').' '.$maxPhotoCount.' '.__('photos');
            $this->addError('photos', $errorMsg);

            return;
        }
    }

    public function autocompleteAddressSelected($data)
    {
        $this->address = $data['address'];
        $this->latitude = $data['latitude'];
        $this->longitude = $data['longitude'];
        $this->city = $data['city'] ?? null;
        $this->state = $data['state'] ?? null;
        $this->country = $data['country'] ?? null;
        $this->postal_code = $data['postal_code'] ?? null;
    }
}
