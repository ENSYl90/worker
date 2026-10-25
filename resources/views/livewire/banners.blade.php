@section('title', __('Banners'))
<div>


    <x-baseview title="{{ __('Banners') }}" :showNew="inProduction()">
        <livewire:tables.banner-table />
    </x-baseview>

    <div x-data="{ open: @entangle('showCreate') }">
        <x-modal confirmText="{{ __('Save') }}" action="save" :clickAway="false">
            <p class="text-xl font-semibold">{{ __('Create Banner') }}</p>
            {{-- title, description, cta --}}
            <x-input title="{{ __('Title') }}" name="title" placeholder="{{ __('Banner title') }}" />
            <x-textarea title="{{ __('Description') }}" name="description" placeholder="{{ __('Banner description') }}" h="h-24" />
            <x-input title="{{ __('CTA Button Title') }}" name="cta_button_title" placeholder="{{ __('e.g. Shop Now') }}" />
            <hr class="my-2">
            <x-input.filepond wire:model="photo" title="{{ __('Photo') }}"
                acceptedFileTypes="['image/png', 'image/jpeg', 'image/jpg']" allowImagePreview
                allowFileSizeValidation maxFileSize="{{ setting('filelimit.banner', 2048) }}kb" />
            <x-input-error message="{{ $errors->first('photo') }}" />
            <hr class="my-2">
            {{-- zones --}}
            <x-select2 title="{{ __('Zones') }}" :options='$this->zones' name="deliveryZonesIDs" id="deliveryZonesSelect2"
                :multiple="true" width="100" :ignore="true" />
            {{-- visibility --}}
            <x-select title="{{ __('Section/Page Visible') }}" :options='$this->vendorTypes' name="vendor_type_id" />
            {{-- type --}}
            <x-select title="{{ __('Type') }}" :options='$types' name="type" :noPreSelect="true"
                :defer="false" />

            {{-- link --}}
            <div class="{{ $type == 'link' ? 'block' : 'hidden' }}">
                <x-input title="{{ __('External Link') }}" name="link" placeholder="" />
            </div>
            {{-- vendors --}}
            <div class="{{ $type == 'vendor' ? 'block' : 'hidden' }}">
                <x-label for="vendor_id" title="{{ __('Vendor') }}">
                    <livewire:select.vendor-select name="vendor_id" placeholder="{{ __('Search vendor') }}"
                        :searchable="true" />
                </x-label>
                <x-input-error message="{{ $errors->first('vendor_id') }}" />
            </div>
            {{-- category --}}
            <div class="{{ $type == 'category' ? 'block' : 'hidden' }}">
                <x-label for="category_id" title="{{ __('Category') }}">
                    <livewire:select.category-select name="category_id" placeholder="{{ __('Search category') }}"
                        :searchable="true" />
                </x-label>
                <x-input-error message="{{ $errors->first('category_id') }}" />
            </div>
            {{-- product --}}
            <div class="{{ $type == 'product' ? 'block' : 'hidden' }}">
                <x-label for="product_id" title="{{ __('Product') }}">
                    <livewire:select.product-select name="product_id" placeholder="{{ __('Search product') }}"
                        :searchable="true" />
                </x-label>
                <x-input-error message="{{ $errors->first('product_id') }}" />
            </div>

            <hr class="my-2">
            <x-checkbox title="{{ __('Active') }}" name="isActive" />
            <x-checkbox title="{{ __('Featured') }}"
                description="{{ __('Can featured on home screen of customer app') }}" name="featured" />

            <x-form-errors />

        </x-modal>
    </div>

    <div x-data="{ open: @entangle('showEdit') }">
        <x-modal confirmText="{{ __('Update') }}" action="update" :clickAway="false">

            <p class="text-xl font-semibold">{{ __('Edit Banner') }}</p>
            {{-- title, description, cta --}}
            <x-input title="{{ __('Title') }}" name="title" placeholder="{{ __('Banner title') }}" />
            <x-textarea title="{{ __('Description') }}" name="description" placeholder="{{ __('Banner description') }}" h="h-24" />
            <x-input title="{{ __('CTA Button Title') }}" name="cta_button_title" placeholder="{{ __('e.g. Shop Now') }}" />
            <hr class="my-2">
            @if ($selectedModel->photo ?? false)
                <x-label title="{{ __('Current Photo') }}" />
                <img src="{{ $selectedModel->photo }}" class="h-20 rounded-lg mb-2" alt="{{ $selectedModel->title }}">
            @endif
            <x-input.filepond wire:model="photo" title="{{ __('Replace Photo') }}" id="editBannerInput"
                allowAddFileEvent="true" acceptedFileTypes="['image/png', 'image/jpeg', 'image/jpg']"
                allowImagePreview allowFileSizeValidation maxFileSize="{{ setting('filelimit.banner', 2048) }}kb" />
            <x-input-error message="{{ $errors->first('photo') }}" />
            <hr class="my-2">
            {{-- zones --}}
            <x-select2 title="{{ __('Zones') }}" :options='$this->zones' name="deliveryZonesIDs"
                id="editDeliveryZonesSelect2" :multiple="true" width="100" :ignore="true" />
            {{-- visibility --}}
            <x-select title="{{ __('Section/Page Visible') }}" :options='$this->vendorTypes' name="vendor_type_id" />
            {{-- type --}}
            <x-select title="{{ __('Type') }}" :options='$types' name="type" :noPreSelect="true"
                :defer="false" />

            {{-- link --}}
            <div class="{{ $type == 'link' ? 'block' : 'hidden' }}">
                <x-input title="{{ __('External Link') }}" name="link" placeholder="" />
            </div>
            {{-- vendors --}}
            <div class="{{ $type == 'vendor' ? 'block' : 'hidden' }}">
                <x-label for="vendor_id" title="{{ __('Vendor') }}">
                    <livewire:select.vendor-select name="vendor_id" placeholder="{{ __('Search vendor') }}"
                        :searchable="true" />
                </x-label>
                <x-input-error message="{{ $errors->first('vendor_id') }}" />
            </div>
            {{-- category --}}
            <div class="{{ $type == 'category' ? 'block' : 'hidden' }}">
                <x-label for="category_id" title="{{ __('Category') }}">
                    <livewire:select.category-select name="category_id" placeholder="{{ __('Search category') }}"
                        :searchable="true" />
                </x-label>
                <x-input-error message="{{ $errors->first('category_id') }}" />
            </div>
            {{-- product --}}
            <div class="{{ $type == 'product' ? 'block' : 'hidden' }}">
                <x-label for="product_id" title="{{ __('Product') }}">
                    <livewire:select.product-select name="product_id" placeholder="{{ __('Search product') }}"
                        :searchable="true" />
                </x-label>
                <x-input-error message="{{ $errors->first('product_id') }}" />
            </div>

            <hr class="my-2">
            <x-checkbox title="{{ __('Active') }}" name="isActive" />
            <x-checkbox title="{{ __('Featured') }}"
                description="{{ __('Can featured on home screen of customer app') }}" name="featured" />

            <x-form-errors />


        </x-modal>
    </div>

    <div x-data="{ open: @entangle('showDetails') }" x-show="open" style="display: none;">
        <x-modal-lg>

            <p class="text-2xl font-semibold mb-6">
                {{ $selectedModel->title ?? __('Banner Details') }}
            </p>

            @if($selectedModel)
                <div class="space-y-6">
                    <div class="bg-white rounded-lg border p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Title') }}</label>
                                <p class="text-gray-900">{{ $selectedModel->title ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('CTA Button Title') }}</label>
                                <p class="text-gray-900">{{ $selectedModel->cta_button_title ?? 'N/A' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Description') }}</label>
                                <p class="text-gray-900 whitespace-pre-line">{{ $selectedModel->description ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Type') }}</label>
                                <p class="text-gray-900">
                                    {{ ucfirst($selectedModel->category_id ? 'category' : ($selectedModel->vendor_id ? 'vendor' : ($selectedModel->product_id ? 'product' : 'link'))) }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Info') }}</label>
                                <p class="text-gray-900">
                                    {{ $selectedModel->category->name ?? $selectedModel->vendor->name ?? $selectedModel->product->name ?? $selectedModel->link ?? 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Section/Page Visible') }}</label>
                                <p class="text-gray-900">{{ $selectedModel->vendor_type->name ?? __('Home Page') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Zones') }}</label>
                                <p class="text-gray-900">
                                    {{ $selectedModel->delivery_zones->pluck('name')->join(', ') ?: __('All Zones') }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Active') }}</label>
                                <p class="text-gray-900">{{ $selectedModel->is_active ? __('Yes') : __('No') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Featured') }}</label>
                                <p class="text-gray-900">{{ $selectedModel->featured ? __('Yes') : __('No') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Created At') }}</label>
                                <p class="text-gray-900">{{ $selectedModel->formatted_date ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($selectedModel->photo)
                        <div class="bg-white rounded-lg border p-4">
                            <label class="block text-sm font-medium text-gray-600 mb-2">{{ __('Photo') }}</label>
                            <img src="{{ $selectedModel->photo }}" class="max-h-64 rounded-lg" alt="{{ $selectedModel->title }}">
                        </div>
                    @endif
                </div>
            @endif

        </x-modal-lg>
    </div>
</div>
