@if ($driverType == 'taxi')
    <hr class="my-4" />
    <p class="font-semibold text-2xl">{{ __('Vehicle Details') }}</p>
    <div class="grid grid-cols-2 gap-4">
        {{-- car make --}}
        <x-select title="{{ __('Vehicle Make') }}" name="car_make_id" :options="$this->car_makes ?? []" :defer="false"
            :noPreSelect="true" />

        {{-- car model --}}
        <x-select title="{{ __('Vehicle Model') }}" name="car_model_id" :options="$this->car_models ?? []" :noPreSelect="true" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-input title="{{ __('Registration Number') }}" name="reg_no" />
        <x-input title="{{ __('Color') }}" name="color" />
        {{-- vehicle type --}}
        <x-select title="{{ __('Vehicle Type') }}" :options='$vehicleTypes ?? []' name="vehicle_type_id" :noPreSelect="true" />
    </div>
@endif
