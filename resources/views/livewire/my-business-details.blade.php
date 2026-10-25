
@section('title', __('Business Details'))
<div>
    <x-baseview title="{{ __('Business Details') }}">
        
        <div class="p-5 bg-white border rounded shadow-sm">
            {{-- Unified Header --}}
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <p class="text-xl font-bold text-gray-800 w-full">{{ $isEditing ? __('Edit Details') : __('Business Info') }}</p>
                <div class="flex items-center gap-2">
                    @if(!$isEditing)
                        <x-buttons.primary wireClick="toggleEdit" title="{{ __('Edit') }}" />
                    @else
                        <x-buttons.plain wireClick="toggleEdit" >
                            <div class="flex items-center gap-2">
                                <x-tabler-x class="w-4 h-4" />
                                {{ __('Cancel') }}
                            </div>
                        </x-buttons.plain>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- Left Column: Details / Edit Form --}}
                <div class="lg:col-span-8">
                     @if($isEditing)
                         <x-form action="update">
                            <x-input title="{{ __('Name') }}" name="name" />
                            
                            <x-input.summernote name="description" title="{{ __('Description') }}" id="editContent" />

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-phoneselector inputId="phoneEdit" />
                                <x-input title="{{ __('Email') }}" name="email" />
                            </div>

                            <div class="p-4 mt-4 bg-gray-50 border rounded">
                                <livewire:component.autocomplete-address title="{{ __('Address') }}" name="address"
                                    address="{{ $address ?? '' }}" />
                                <x-input-error message="{{ $errors->first('address') }}" />
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                    <x-input title="{{ __('Latitude') }}" name="latitude" />
                                    <x-input title="{{ __('Longitude') }}" name="longitude" />
                                </div>
                            </div>

                             <div class="{{ (!$isPackageVendor && !$isBookingVendor) ? 'block' : 'hidden' }} mt-4">
                                <hr class="my-4" />
                                <p class="mb-2 font-medium">{{ __('Order Settings') }}</p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 content-start">
                                    <div class="{{ !$isServiceVendor ? 'block' : 'hidden' }} col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <x-input title="{{ __('Minimum Order Amount') }}" name="min_order" />
                                        <x-input title="{{ __('Maximum Order Amount') }}" name="max_order" />
                                    </div>

                                    <x-input title="{{ __('Base Delivery Fee') }}" name="base_delivery_fee" />
                                    <x-input title="{{ __('Delivery Fee') }}" name="delivery_fee" />

                                    <x-input title="{{ __('Delivery Range(KM)') }}" name="delivery_range" />
                                    <div class="flex items-center">
                                        <x-checkbox title="{{ __('Charge per KM') }}" name="charge_per_km" description="{{ __('Delivery fee will be per KM') }}" />
                                    </div>
                                    
                                    <div class="{{ !$isServiceVendor ? 'block' : 'hidden' }} col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <x-checkbox title="{{ __('Pickup') }}" name="pickup" description="{{ __('Allows pickup orders') }}" />
                                        <x-checkbox title="{{ __('Delivery') }}" name="delivery" description="{{ __('Allows delivery orders') }}" />
                                    </div>
                                </div>
                            </div>

                            <div class="{{ !$isBookingVendor ? 'block' : 'hidden' }} mt-4">
                                 <hr class="my-4" />
                                 <p class="mb-2 font-medium">{{ __('Scheduling & Timing') }}</p>
                                 
                                 <div class="grid items-center grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-checkbox title="{{ __('Schedule Order') }}" name="allow_schedule_order" description="{{ __('Allows customer to schedule orders') }}" />
                                    <x-checkbox title="{{ __('Auto Accept Order') }}" name="auto_accept" description="{{ __('System will automatic change pending order to preparing') }}" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <x-input-group>
                                        <x-slot name="label"><x-label title="{{ __('Prepare Time') }}" /></x-slot>
                                        <x-slot name="input"><x-input name="prepare_time" placeholder="{{ __('10') }}" /></x-slot>
                                        <x-slot name="append"><x-select-plain name="prepare_time_unit" :options="$timeUnits ?? []" /></x-slot>
                                    </x-input-group>

                                    <x-input-group>
                                        <x-slot name="label"><x-label title="{{ __('Delivery Time') }}" /></x-slot>
                                        <x-slot name="input"><x-input name="delivery_time" placeholder="{{ __('15') }}" /></x-slot>
                                        <x-slot name="append"><x-select-plain name="delivery_time_unit" :options="$timeUnits ?? []" /></x-slot>
                                    </x-input-group>
                                </div>
                            </div>

                            <div class="mt-6 border-t pt-4">
                                <x-buttons.primary title="{{ __('Update Details') }}" />
                            </div>
                         </x-form>
                     @else
                        {{-- Read Only View --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <x-details.item title="{{ __('Name') }}" text="{{ $name }}" />
                            </div>
                            <div class="md:col-span-2">
                                <x-details.item title="{{ __('Description') }}">
                                    <div class="prose max-w-none">{!! $description !!}</div>
                                </x-details.item>
                            </div>
                            
                            <x-details.item title="{{ __('Phone') }}" text="{{ $phone }}" />
                            <x-details.item title="{{ __('Email') }}" text="{{ $email }}" />
                            
                            <div class="md:col-span-2">
                                <x-details.item title="{{ __('Address') }}" text="{{ $address }}" />
                            </div>
                            
                            {{-- Order Settings Summary --}}
                            @if(!$isPackageVendor && !$isBookingVendor)
                                <div class="md:col-span-2 mt-2">
                                    <hr class="mb-4" />
                                    <p class="mb-2 font-medium">{{ __('Order Settings') }}</p>
                                </div>
                                
                                @if(!$isServiceVendor)
                                    <x-details.item title="{{ __('Min Order') }}" text="{{ currencyFormat($min_order) }}" />
                                    <x-details.item title="{{ __('Max Order') }}" text="{{ currencyFormat($max_order) }}" />
                                @endif
                                <x-details.item title="{{ __('Delivery Fee') }}" text="{{ currencyFormat($delivery_fee) }}" />
                                <x-details.item title="{{ __('Delivery Range') }}" text="{{ $delivery_range }} KM" />
                                
                                <x-details.item title="{{ __('Pickup') }}" text="{{ $pickup ? __('Yes') : __('No') }}" />
                                <x-details.item title="{{ __('Delivery') }}" text="{{ $delivery ? __('Yes') : __('No') }}" />
                            @endif

                            @if(!$isBookingVendor)
                                <div class="md:col-span-2 mt-2">
                                    <hr class="mb-4" />
                                    <p class="mb-2 font-medium">{{ __('Scheduling & Timing') }}</p>
                                </div>
                                
                                <x-details.item title="{{ __('Schedule Orders') }}" text="{{ $allow_schedule_order ? __('Allowed') : __('Not Allowed') }}" />
                                <x-details.item title="{{ __('Auto Accept') }}" text="{{ $auto_accept ? __('Enabled') : __('Disabled') }}" />
                                <x-details.item title="{{ __('Prepare Time') }}" text="{{ $prepare_time }} {{ $prepare_time_unit }}" />
                                <x-details.item title="{{ __('Delivery Time') }}" text="{{ $delivery_time }} {{ $delivery_time_unit }}" />
                            @endif
                        </div>
                     @endif
                </div>

                {{-- Right Column: Status & Images --}}
                <div class="lg:col-span-4 border-l pl-0 lg:pl-8 space-y-8">
                    
                    {{-- Status Section --}}
                    <div>
                        <p class="mb-4 text-lg font-semibold text-gray-700">{{ __('Status & Info') }}</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-4">
                            <x-details.item title="{{ __('Vendor Type') }}" text="{{ $vendor_type->name ?? '' }}" />
                            <x-details.item title="{{ __('Tax') }}" text="{{ $tax }}%" />
                            <x-details.item title="{{ __('Commission') }}" text="{{ $commission }}%" />
                            <div>
                                <x-label title="{{ __('Active Status') }}" />
                                <span class="px-2 py-1 text-sm rounded {{ $is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <hr />

                    {{-- Images Section --}}
                    <div>
                         <p class="mb-4 text-lg font-semibold text-gray-700">{{ __('Images') }}</p>
                         @if($isEditing)
                             <div class="space-y-4">
                                <x-media-upload title="{{ __('Logo') }}" name="photo" 
                                    preview="{{ $selectedModel->logo ?? '' }}" :photo="$photo" 
                                    :photoInfo="$photoInfo" types="PNG or JPEG" rules="image/*" />

                                <x-media-upload title="{{ __('Featured Image') }}" name="secondPhoto" 
                                    preview="{{ $selectedModel->feature_image ?? '' }}" :photo="$secondPhoto" 
                                    :photoInfo="$secondPhotoInfo" types="PNG or JPEG" rules="image/*" />
                             </div>
                        @else
                            <div class="space-y-4">
                                <div>
                                    <x-label title="{{ __('Logo') }}" />
                                    <img src="{{ $selectedModel->logo ?? '' }}" class="w-full h-40 object-cover rounded border" />
                                </div>
                                <div>
                                    <x-label title="{{ __('Featured Image') }}" />
                                    <img src="{{ $selectedModel->feature_image ?? '' }}" class="w-full h-40 object-cover rounded border" />
                                </div>
                            </div>
                        @endif
                     </div>

                </div>
            </div>
        </div>
    </x-baseview>
</div>
@include('layouts.partials.phoneselector')

