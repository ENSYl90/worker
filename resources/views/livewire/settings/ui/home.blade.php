<div class=" w-full md:w-10/12 lg:w-6/12">
    <x-form noClass="true" action="saveHomeSettings">
        <div class='grid grid-cols-1 gap-4 lg:grid-cols-2'>
            <x-checkbox title="{{ __('Wallet') }}" description="{{ __('Show Wallet On Home Screen') }}"
                name="showWalletOnHomeScreen" />
            <x-select title="{{ __('Home Screen Style') }}" :options="$homeViewStyles" name="homeViewStyle" />
        </div>
        <hr class="my-2 mt-4" />
        <div class='grid grid-cols-1 gap-4 lg:grid-cols-2'>
            <x-checkbox title="{{ __('Banner') }}" description="{{ __('Show Banner On Home Screen') }}"
                name="showBannerOnHomeScreen" />
            <x-select title="{{ __('Banner Position') }}" :options="['Top', 'Bottom']" name="bannerPosition" />
            <x-input title="{{ __('Banner Height') }}" name="bannerHeight" type="number" />
        </div>
        <hr class="my-2 mt-4" />
        <div class="border rounded p-4 m-4">
            <p class="text-xl font-bold">{{ __('Vendor Type') }}</p>
            <div class='grid grid-cols-1 gap-4 lg:grid-cols-2'>
                <x-checkbox title="{{ __('Image Only') }}" name="showVendorTypeImageOnly" :defer="false">
                    <p>{{ __('Show only image on modules section on app') }}</p>
                </x-checkbox>
                <div class="{{ $showVendorTypeImageOnly ? 'hidden' : 'visible' }}">
                    <x-checkbox title="{{ __('Title Only') }}" name="vendortypeTitleOnly" :defer="true">
                        <p>{{ __('Show only title, description will be hidden in the module listing on app') }}</p>
                    </x-checkbox>
                </div>
                <div>
                    <p class="mb-1 text-gray-700 text-sm {{ isRTL() ? 'text-right' : 'text-left' }}">
                        {{ __('Module Style') }}</p>
                    <x-select :options="['normal', 'circle']" name="vendortypeItemStyle" />
                </div>
            </div>
            <hr class="my-2 mt-4" />
            <div class='grid grid-cols-1 gap-4 lg:grid-cols-2'>
                <x-select title="{{ __('Vendor Type Listing Style') }}" :options="['Both', 'GridView', 'ListView']" name="vendortypeListStyle" />
                <x-input title="{{ __('Vendor Type Per Row') }}" name="vendortypePerRow" type="number" />
                <div>
                    <p class="mb-1 text-gray-700 text-sm {{ isRTL() ? 'text-right' : 'text-left' }}">
                        {{ __('Vendor Type Image Style') }}</p>
                    <x-select :options="['fill', 'cover', 'center']" name="vendortypeImageStyle" />
                </div>



            </div>
            <div class='grid grid-cols-1 gap-4 lg:grid-cols-2'>
                <x-input title="{{ __('Vendor Type Image Height') }}" name="vendortypeHeight" type="number" />
                <x-input title="{{ __('Vendor Type Image Width') }}" name="vendortypeWidth" type="number" />

            </div>
            <hr class="my-2 mt-4" />
            <div>
                <p class="text-xs text-gray-400">
                    {{ __('New Sizing Style. The size is in percentage of the device screen width and height') }}</p>
                <div class='grid grid-cols-1 gap-4 lg:grid-cols-2'>
                    <x-input title="{{ __('Vendor Type Image Height') }}" name="vendortypePercentageHeight"
                        type="number" min="10" max="100" />
                    <x-input title="{{ __('Vendor Type Image Width') }}" name="vendortypePercentageWidth"
                        type="number" min="10" max="100" />
                </div>
            </div>
        </div>
        <x-buttons.primary title="{{ __('Save') }}" />
    </x-form>
</div>
