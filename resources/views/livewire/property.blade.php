@section('title', __('Properties'))
<div>

    <x-baseview title="{{ __('Properties') }}" :showNew="true">
        <livewire:tables.property-table />
    </x-baseview>

    {{-- Create Modal --}}
    <div x-data="{ open: @entangle('showCreate') }" x-show="open" style="display: none;">
        <x-modal-lg confirmText="{{ __('Save') }}" action="save">
            <p class="text-2xl font-semibold mb-4">{{ __('Create Property') }}</p>
            <div>
                {{-- Basic Information --}}
                <h3 class="text-lg font-semibold mb-2">{{ __('Basic Information') }}</h3>
                <x-input title="{{ __('Property Name') }}" name="name" placeholder="{{ __('Enter property name') }}" />
                <x-select2 title="{{ __('Vendor/Host') }}" :options='[]' name="vendor_id" id="newVendorSelect"
                    :width="100" :ignore="true" />
                <x-select2 title="{{ __('Property Type') }}" :options='[]' name="property_type_id"
                    id="newPropertyTypeSelect" :width="100" :ignore="true" />
                <div class="grid grid-cols-2 gap-4">
                    <x-input title="{{ __('Base Price') }}" name="base_price" type="number" step="0.01" />
                    <x-input title="{{ __('Discount Price') }}" name="discount_price" type="number" step="0.01" />
                </div>
                <div class="md:col-span-2">
                    <x-textarea title="{{ __('Description') }}" name="description"
                        placeholder="{{ __('Enter property description') }}" />
                </div>
                <div class="mt-4">
                    {{-- Location --}}
                    <h3 class="text-lg font-semibold mb-2">{{ __('Location') }}</h3>

                    <div class="p-2 bg-gray-100 border border-gray-300 rounded mb-4">
                        <livewire:component.autocomplete-address title="{{ __('Address') }}" name="address" />
                        <x-input-error message="{{ $errors->first('address') }}" />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            <x-input title="{{ __('City') }}" name="city" placeholder="{{ __('Enter city') }}" />
                            <x-input title="{{ __('State/Province') }}" name="state"
                                placeholder="{{ __('Enter state') }}" />
                            <x-input title="{{ __('Country') }}" name="country"
                                placeholder="{{ __('Enter country') }}" />
                            <x-input title="{{ __('Postal Code') }}" name="postal_code"
                                placeholder="{{ __('Enter postal code') }}" />

                            <x-input title="{{ __('Latitude') }}" name="latitude" type="number" step="any"
                                placeholder="0.00" />
                            <x-input title="{{ __('Longitude') }}" name="longitude" type="number" step="any"
                                placeholder="0.00" />
                        </div>
                    </div>

                    {{-- Capacity & Details --}}
                    <div class="md:col-span-2 mt-4">
                        <h3 class="text-lg font-semibold mb-2">{{ __('Capacity & Details') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input title="{{ __('Max Guests') }}" name="max_guests" type="number" min="1" />
                        <x-input title="{{ __('Bedrooms') }}" name="bedrooms" type="number" min="0" />
                        <x-input title="{{ __('Bathrooms') }}" name="bathrooms" type="number" min="0" />
                        <x-input title="{{ __('Beds') }}" name="beds" type="number" min="0" />
                        <x-input title="{{ __('Square Meters') }}" name="square_meters" type="number" min="0" />
                    </div>

                    {{-- Check-in/out Times --}}
                    <div class="md:col-span-2 mt-4">
                        <h3 class="text-lg font-semibold mb-2">{{ __('Check-in/out Times') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input title="{{ __('Check-in Start') }}" name="check_in_time_start" type="time" />
                        <x-input title="{{ __('Check-in End') }}" name="check_in_time_end" type="time" />
                        <x-input title="{{ __('Check-out Time') }}" name="check_out_time" type="time" />
                    </div>

                    {{-- Booking Settings --}}
                    <div class="md:col-span-2 mt-4">
                        <h3 class="text-lg font-semibold mb-2">{{ __('Booking Settings') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input title="{{ __('Minimum Nights') }}" name="minimum_nights" type="number" min="1" />
                        <x-input title="{{ __('Maximum Nights') }}" name="maximum_nights" type="number" min="1" />
                        <x-input title="{{ __('Preparation Time (days)') }}" name="preparation_time" type="number"
                            min="0" />

                        <x-select2 title="{{ __('Cancellation Policy') }}" :options='[]' name="cancellation_policy_id"
                            id="newCancellationPolicySelect" :width="100" :ignore="true" />

                        <div>
                            <x-checkbox title="{{ __('Active') }}" name="is_active" :defer="true" />
                        </div>

                        <div>
                            <x-checkbox title="{{ __('Featured') }}" name="featured" :defer="true" />
                        </div>

                        <div>
                            <x-checkbox title="{{ __('Instant Booking') }}" name="is_instant_booking" :defer="true"
                                description="{{ __('Auto-confirm bookings without approval') }}" />
                        </div>

                        <div>
                            <x-checkbox title="{{ __('Host Resides Here') }}" name="is_host_residing" :defer="true"
                                description="{{ __('Check if the host lives in the same property') }}" />
                        </div>
                    </div>

                    {{-- Amenities --}}
                    <div class="md:col-span-2 mt-4">
                        <h3 class="text-lg font-semibold mb-2">{{ __('Amenities') }}</h3>
                        <x-select2 title="{{ __('Select Amenities') }}" :options='[]' name="selectedAmenities_ids"
                            id="newAmenitiesSelect" :width="100" :ignore="true" :multiple="true" />
                    </div>

                    {{-- House Rules --}}
                    <div class="md:col-span-2 mt-4">
                        <x-textarea title="{{ __('House Rules') }}" name="house_rules"
                            placeholder="{{ __('Enter house rules (optional)') }}" />
                    </div>


                </div>
            </div>
            {{-- Property Photos --}}
            <div>
                <x-input.filepond wire:model="photos" title="{{ __('Photo(s)') }}" id="newPropertyInput"
                    allowAddFileEvent="true" acceptedFileTypes="['image/png', 'image/jpeg', 'image/jpg']"
                    allowImagePreview multiple="true" allowFileSizeValidation imagePreviewMaxHeight="80"
                    maxFileSize="{{ setting('filelimit.product_image_size', 200) }}kb" />
            </div>
            <x-form-errors />
        </x-modal-lg>
    </div>

    {{-- Edit Modal --}}
    <div x-data="{ open: @entangle('showEdit') }" x-show="open" style="display: none;">
        <x-modal-lg confirmText="{{ __('Update') }}" action="update">
            <p class="text-2xl font-semibold mb-4">{{ __('Edit Property') }}</p>
            <div>
                {{-- Basic Information --}}
                <h3 class="text-lg font-semibold mb-2">{{ __('Basic Information') }}</h3>
                <x-input title="{{ __('Property Name') }}" name="name" placeholder="{{ __('Enter property name') }}" />
                <x-select2 title="{{ __('Vendor/Host') }}" :options='[]' name="vendor_id" id="editVendorSelect"
                    :width="100" :ignore="true" />
                <x-select2 title="{{ __('Property Type') }}" :options='[]' name="property_type_id"
                    id="editPropertyTypeSelect" :width="100" :ignore="true" />
                <div class="grid grid-cols-2 gap-4">
                    <x-input title="{{ __('Base Price') }}" name="base_price" type="number" step="0.01" />
                    <x-input title="{{ __('Discount Price') }}" name="discount_price" type="number" step="0.01" />
                </div>
                <div class="md:col-span-2">
                    <x-textarea title="{{ __('Description') }}" name="description"
                        placeholder="{{ __('Enter property description') }}" />
                </div>
                <div class="mt-4">
                    {{-- Location --}}
                    <h3 class="text-lg font-semibold mb-2">{{ __('Location') }}</h3>

                    <div class="p-2 bg-gray-100 border border-gray-300 rounded mb-4">
                        <livewire:component.autocomplete-address title="{{ __('Address') }}" name="address"
                            address="{{ $address ?? '' }}" />
                        <x-input-error message="{{ $errors->first('address') }}" />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            <x-input title="{{ __('City') }}" name="city" placeholder="{{ __('Enter city') }}" />
                            <x-input title="{{ __('State/Province') }}" name="state"
                                placeholder="{{ __('Enter state') }}" />
                            <x-input title="{{ __('Country') }}" name="country"
                                placeholder="{{ __('Enter country') }}" />
                            <x-input title="{{ __('Postal Code') }}" name="postal_code"
                                placeholder="{{ __('Enter postal code') }}" />

                            <x-input title="{{ __('Latitude') }}" name="latitude" type="number" step="any"
                                placeholder="0.00" />
                            <x-input title="{{ __('Longitude') }}" name="longitude" type="number" step="any"
                                placeholder="0.00" />
                        </div>
                    </div>


                    {{-- Capacity & Details --}}
                    <div class="md:col-span-2 mt-4">
                        <h3 class="text-lg font-semibold mb-2">{{ __('Capacity & Details') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input title="{{ __('Max Guests') }}" name="max_guests" type="number" min="1" />
                        <x-input title="{{ __('Bedrooms') }}" name="bedrooms" type="number" min="0" />
                        <x-input title="{{ __('Bathrooms') }}" name="bathrooms" type="number" min="0" />
                        <x-input title="{{ __('Beds') }}" name="beds" type="number" min="0" />
                        <x-input title="{{ __('Square Meters') }}" name="square_meters" type="number" min="0" />
                    </div>

                    {{-- Check-in/out Times --}}
                    <div class="md:col-span-2 mt-4">
                        <h3 class="text-lg font-semibold mb-2">{{ __('Check-in/out Times') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input title="{{ __('Check-in Start') }}" name="check_in_time_start" type="time" />
                        <x-input title="{{ __('Check-in End') }}" name="check_in_time_end" type="time" />
                        <x-input title="{{ __('Check-out Time') }}" name="check_out_time" type="time" />
                    </div>

                    {{-- Booking Settings --}}
                    <div class="md:col-span-2 mt-4">
                        <h3 class="text-lg font-semibold mb-2">{{ __('Booking Settings') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input title="{{ __('Minimum Nights') }}" name="minimum_nights" type="number" min="1" />
                        <x-input title="{{ __('Maximum Nights') }}" name="maximum_nights" type="number" min="1" />
                        <x-input title="{{ __('Preparation Time (days)') }}" name="preparation_time" type="number"
                            min="0" />

                        <x-select2 title="{{ __('Cancellation Policy') }}" :options='[]' name="cancellation_policy_id"
                            id="editCancellationPolicySelect" :width="100" :ignore="true" />

                        <div>
                            <x-checkbox title="{{ __('Active') }}" name="is_active" :defer="true" />
                        </div>

                        <div>
                            <x-checkbox title="{{ __('Featured') }}" name="featured" :defer="true" />
                        </div>

                        <div>
                            <x-checkbox title="{{ __('Instant Booking') }}" name="is_instant_booking" :defer="true"
                                description="{{ __('Auto-confirm bookings without approval') }}" />
                        </div>

                        <div>
                            <x-checkbox title="{{ __('Host Resides Here') }}" name="is_host_residing" :defer="true"
                                description="{{ __('Check if the host lives in the same property') }}" />
                        </div>
                    </div>

                    {{-- Amenities --}}
                    <div class="md:col-span-2 mt-4">
                        <h3 class="text-lg font-semibold mb-2">{{ __('Amenities') }}</h3>
                        <x-select2 title="{{ __('Select Amenities') }}" :options='[]' name="selectedAmenities_ids"
                            id="editAmenitiesSelect" :width="100" :ignore="true" :multiple="true" />
                    </div>

                    {{-- House Rules --}}
                    <div class="md:col-span-2 mt-4">
                        <x-textarea title="{{ __('House Rules') }}" name="house_rules"
                            placeholder="{{ __('Enter house rules (optional)') }}" />
                    </div>



                </div>

                {{-- Property Photos --}}
                <div>
                    <x-input.filepond wire:model="photos" title="{{ __('Photo(s)') }}" id="editPropertyInput"
                        allowAddFileEvent="true" acceptedFileTypes="['image/png', 'image/jpeg', 'image/jpg']"
                        allowImagePreview multiple="true" allowFileSizeValidation imagePreviewMaxHeight="80"
                        maxFileSize="{{ setting('filelimit.product_image_size', 200) }}kb" />
                </div>
            </div>

            <x-form-errors />

        </x-modal-lg>
    </div>


    {{-- details modal --}}
    <div x-data="{ open: @entangle('showDetails') }" x-show="open" style="display: none;">
        <x-modal-lg>

            <p class="text-2xl font-semibold mb-6">
                {{ $selectedModel != null ? $selectedModel->name : '' }}
            </p>

            @if($selectedModel)
                <div class="space-y-6">
                    {{-- Basic Information --}}
                    <div class="bg-white rounded-lg border p-4">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800">{{ __('Basic Information') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Vendor/Host') }}</label>
                                <p class="text-gray-900">{{ $selectedModel->vendor->name ?? 'N/A' }}</p>
                            </div>
                            @if($selectedModel->property_type_id)
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-600 mb-1">{{ __('Property Type') }}</label>
                                    <p class="text-gray-900">{{ $selectedModel->propertyType->name ?? 'N/A' }}</p>
                                </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Pricing') }}</label>
                                <p class="text-gray-900">
                                    {{ setting('currency', '$') }}{{ number_format($selectedModel->base_price, 2) }}
                                    @if($selectedModel->discount_price > 0)
                                        <span class="text-red-500 line-through text-xs ml-1">
                                            {{ setting('currency', '$') }}{{ number_format($selectedModel->base_price, 2) }}
                                        </span>
                                        <span class="text-green-600 ml-1">
                                            {{ setting('currency', '$') }}{{ number_format($selectedModel->discount_price, 2) }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Status') }}</label>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $selectedModel->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $selectedModel->is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </div>
                        </div>
                        @if($selectedModel->description)
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Description') }}</label>
                                <p class="text-gray-900 whitespace-pre-line">{!! $selectedModel->description !!}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Location --}}
                    <div class="bg-white rounded-lg border p-4">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800">{{ __('Location') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Address') }}</label>
                                <p class="text-gray-900">{{ $selectedModel->address }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('City') }}</label>
                                <p class="text-gray-900">{{ $selectedModel->city ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1">{{ __('State/Province') }}</label>
                                <p class="text-gray-900">{{ $selectedModel->state ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Country') }}</label>
                                <p class="text-gray-900">{{ $selectedModel->country ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Postal Code') }}</label>
                                <p class="text-gray-900">{{ $selectedModel->postal_code ?? 'N/A' }}</p>
                            </div>
                            @if($selectedModel->latitude && $selectedModel->longitude)
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Coordinates') }}</label>
                                    <p class="text-gray-900">{{ $selectedModel->latitude }}, {{ $selectedModel->longitude }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Capacity & Details --}}
                    <div class="bg-white rounded-lg border p-4">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800">{{ __('Capacity & Details') }}</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <p class="text-2xl font-bold text-blue-900">{{ $selectedModel->max_guests }}</p>
                                <p class="text-xs text-blue-600 uppercase font-medium">{{ __('Max Guests') }}</p>
                            </div>
                            <div class="text-center p-3 bg-purple-50 rounded-lg">
                                <p class="text-2xl font-bold text-purple-900">{{ $selectedModel->bedrooms }}</p>
                                <p class="text-xs text-purple-600 uppercase font-medium">{{ __('Bedrooms') }}</p>
                            </div>
                            <div class="text-center p-3 bg-indigo-50 rounded-lg">
                                <p class="text-2xl font-bold text-indigo-900">{{ $selectedModel->bathrooms }}</p>
                                <p class="text-xs text-indigo-600 uppercase font-medium">{{ __('Bathrooms') }}</p>
                            </div>
                            <div class="text-center p-3 bg-pink-50 rounded-lg">
                                <p class="text-2xl font-bold text-pink-900">{{ $selectedModel->beds }}</p>
                                <p class="text-xs text-pink-600 uppercase font-medium">{{ __('Beds') }}</p>
                            </div>
                        </div>
                        @if($selectedModel->square_meters)
                            <div class="mt-3 text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600">
                                    <span class="font-semibold text-gray-900">{{ $selectedModel->square_meters }}</span>
                                    {{ __('square meters') }}
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Check-in/out Times --}}
                    <div class="bg-white rounded-lg border p-4">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800">{{ __('Check-in/out Times') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1">{{ __('Check-in Start') }}</label>
                                <p class="text-gray-900 font-semibold">
                                    {{ \Carbon\Carbon::parse($selectedModel->check_in_time_start)->format('g:i A') }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Check-in End') }}</label>
                                <p class="text-gray-900 font-semibold">
                                    {{ \Carbon\Carbon::parse($selectedModel->check_in_time_end)->format('g:i A') }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1">{{ __('Check-out Time') }}</label>
                                <p class="text-gray-900 font-semibold">
                                    {{ \Carbon\Carbon::parse($selectedModel->check_out_time)->format('g:i A') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Booking Settings --}}
                    <div class="bg-white rounded-lg border p-4">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800">{{ __('Booking Settings') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1">{{ __('Minimum Nights') }}</label>
                                <p class="text-gray-900">{{ $selectedModel->minimum_nights }} {{ __('nights') }}</p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1">{{ __('Maximum Nights') }}</label>
                                <p class="text-gray-900">{{ $selectedModel->maximum_nights ?? __('No limit') }}</p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1">{{ __('Preparation Time') }}</label>
                                <p class="text-gray-900">{{ $selectedModel->preparation_time }} {{ __('days') }}</p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1">{{ __('Instant Booking') }}</label>
                                <span {{ $selectedModel->is_instant_booking ? __('Yes') : __('No') }} </span>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1">{{ __('Host Resides Here') }}</label>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $selectedModel->is_host_residing ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $selectedModel->is_host_residing ? __('Yes') : __('No') }}
                                </span>
                            </div>
                            @if($selectedModel->cancellation_policy_id)
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-sm font-medium text-gray-600 mb-1">{{ __('Cancellation Policy') }}</label>
                                    <p class="text-gray-900">{{ $selectedModel->cancellationPolicy->name ?? 'N/A' }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Amenities --}}
                    @if($selectedModel->amenities && $selectedModel->amenities->count() > 0)
                        <div class="bg-white rounded-lg border p-4">
                            <h3 class="text-lg font-semibold mb-3 text-gray-800">{{ __('Amenities') }}</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                @foreach($selectedModel->amenities as $amenity)
                                    <div class="flex items-center space-x-2 text-sm">
                                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-gray-900">{{ $amenity->name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- House Rules --}}
                    @if($selectedModel->house_rules)
                        <div class="bg-white rounded-lg border p-4">
                            <h3 class="text-lg font-semibold mb-3 text-gray-800">{{ __('House Rules') }}</h3>
                            <div class="prose prose-sm max-w-none">
                                <p class="text-gray-900 whitespace-pre-line">{{ $selectedModel->house_rules }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Statistics --}}
                    <div class="bg-white rounded-lg border p-4">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800">{{ __('Statistics') }}</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <p class="text-2xl font-bold text-green-900">{{ $selectedModel->bookingOrders()->count() }}
                                </p>
                                <p class="text-xs text-green-600 uppercase font-medium">{{ __('Total Bookings') }}</p>
                            </div>
                            <div class="text-center p-3 bg-yellow-50 rounded-lg">
                                <p class="text-2xl font-bold text-yellow-900">{{ $selectedModel->fees()->count() }}</p>
                                <p class="text-xs text-yellow-600 uppercase font-medium">{{ __('Fees Configured') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Property Photos --}}
                    @if($selectedModel->photos && count($selectedModel->photos) > 0)
                        <div class="bg-white rounded-lg border p-4">
                            <h3 class="text-lg font-semibold mb-3 text-gray-800">{{ __('Property Photos') }}</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach($selectedModel->photos as $photo)
                                    <a href="{{ $photo }}" target="_blank">
                                        <img src="{{ $photo }}" class="w-full h-24 border rounded-sm object-contain" />
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-lg border p-4">
                            <h3 class="text-lg font-semibold mb-3 text-gray-800">{{ __('Property Photos') }}</h3>
                            <div class="bg-gray-100 rounded-lg p-8 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">{{ __('No photos uploaded yet') }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Metadata --}}
                    <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <p><span class="font-semibold">{{ __('Created:') }}</span>
                                {{ $selectedModel->created_at->format('M d, Y g:i A') }}</p>
                            <p><span class="font-semibold">{{ __('Last Updated:') }}</span>
                                {{ $selectedModel->updated_at->format('M d, Y g:i A') }}</p>
                        </div>
                    </div>
                </div>
            @endif

        </x-modal-lg>
    </div>
</div>