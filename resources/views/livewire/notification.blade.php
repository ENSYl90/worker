@section('title', __('Notification'))
<div>

    <x-baseview title="{{ __('Send Notification') }}" showNew="true">
        <livewire:tables.push-notification-table />
    </x-baseview>


    {{-- details modal --}}
    <div x-data="{ open: @entangle('showDetails') }">
        <x-modal-xl>
            @if($selectedModel)
                <p class="text-xl font-semibold mb-4">{{ __('Notification Details') }}</p>

                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('Title') }}</p>
                        <p class="mt-1 text-gray-900 font-medium">{{ $selectedModel->title }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('Body') }}</p>
                        <p class="mt-1 text-gray-900 whitespace-pre-line">{!! nl2br($selectedModel->body) !!}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('Target') }}</p>
                            <p class="mt-1 text-gray-900">{{ $selectedModel->role ?? __('All') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('Sent By') }}</p>
                            <p class="mt-1 text-gray-900">{{ $selectedModel->user->name ?? '—' }}</p>
                        </div>
                    </div>

                    @if($selectedModel->photo)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">{{ __('Image') }}</p>
                            <img src="{{ $selectedModel->photo }}" class="h-32 rounded-lg object-cover" alt="">
                        </div>
                    @endif

                    @if($selectedModel->vendor)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('Vendor') }}</p>
                            <p class="mt-1 text-gray-900">{{ $selectedModel->vendor->name }}</p>
                        </div>
                    @endif

                    @if($selectedModel->product)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('Product') }}</p>
                            <p class="mt-1 text-gray-900">{{ $selectedModel->product->name }}</p>
                        </div>
                    @endif

                    @if($selectedModel->service)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('Service') }}</p>
                            <p class="mt-1 text-gray-900">{{ $selectedModel->service->name }}</p>
                        </div>
                    @endif

                    <div class="pt-2 border-t">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('Sent At') }}</p>
                        <p class="mt-1 text-gray-900 text-sm">{{ $selectedModel->created_at?->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            @endif
        </x-modal-xl>
    </div>

    {{-- new form --}}
    <div x-data="{ open: @entangle('showCreate') }">
        <x-modal confirmText="{{ __('Send') }}" action="sendNotification">
            <p class="text-xl font-semibold">{{ __('Send Notification') }}</p>

            <x-input title="{{ __('Title') }}" name="headings" />
            <x-label title="{{ __('Message') }}" />
            <textarea wire:model.defer="message" class="w-full h-40 p-2 mt-1 border rounded"></textarea>
            @error('message')
                <span class="mt-1 text-xs text-red-700">{{ $message }}</span>
            @enderror
            {{-- receiver --}}
            <div class="grid grid-cols-2 gap-2">
                <x-checkbox title="{{ __('All') }}" name="allReceiver" description="Send to all users"
                    :defer="false" />
                <x-checkbox title="{{ __('Custom') }}" name="customReceiver" description="Send Directly to roles"
                    :defer="false" />
            </div>
            <div class="flex flex-wrap mt-2 mb-6 space-x-5" x-data="{ open: @entangle('customReceiver') }" x-show="open">
                @foreach ($roles as $key => $role)
                    <x-checkbox title="{{ $role->name }}" name="customReceiverRoles.{{ $key }}"
                        value="{{ $role->name }}" :defer="false" />
                @endforeach
            </div>
            <x-media-upload title="{{ __('Image ( Ratio 3:1 )') }}" name="photo" :photo="$photo" :photoInfo="$photoInfo"
                types="PNG or JPEG" rules="image/*" />

            <hr class="my-2" />
            {{-- product/vendor attachment --}}
            <div class="grid grid-cols-2 gap-2">
                <x-checkbox title="{{ __('Product') }}" name="useProduct"
                    description="{{ __('Attach product to notification') }}" :defer="false" />
                <x-checkbox title="{{ __('Vendor') }}" name="useVendor"
                    description="{{ __('Attach vendor to notification') }}" :defer="false" />
                <x-checkbox title="{{ __('Service') }}" name="useService"
                    description="{{ __('Attach service to notification') }}" :defer="false" />
            </div>
            {{-- products --}}
            <div class="{{ $useProduct ? 'block' : 'hidden' }}">
                <livewire:component.autocomplete-input title="{{ __('Product') }}" column="name" model="Product"
                    errorMessage="{{ $errors->first('product_id') }}" emitFunction="autocompleteProductSelected" />
            </div>
            {{-- vendors --}}
            <div class="{{ $useVendor ? 'block' : 'hidden' }}">
                <livewire:component.autocomplete-input title="{{ __('Vendor') }}" column="name" model="Vendor"
                    errorMessage="{{ $errors->first('vendor_id') }}" emitFunction="autocompleteVendorSelected" />
            </div>
            {{-- service --}}
            <div class="{{ $useService ? 'block' : 'hidden' }}">
                <livewire:component.autocomplete-input title="{{ __('Service') }}" column="name" model="Service"
                    errorMessage="{{ $errors->first('service_id') }}" emitFunction="autocompleteServiceSelected" />
            </div>

        </x-modal>
    </div>


</div>
