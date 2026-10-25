@section('title', __('Currency Settings'))
<div>
    <x-baseview title="{{ __('Currencies') }}">

        <x-tab.tabview class="pb-10 shadow">

            <x-slot name="header">
                <x-tab.header tab="1" title="{{ __('Currencies') }}" />
                <x-tab.header tab="2" title="{{ __('Exchange Rates') }}" />
            </x-slot>

            <x-slot name="body">
                <x-tab.body tab="1">
                    <x-baseview title="{{ __('Currencies') }}" :showNew="true">
                        <livewire:tables.currency-table />
                    </x-baseview>
                </x-tab.body>
                <x-tab.body tab="2">
                    <livewire:exchange-rates-manager />
                </x-tab.body>

            </x-slot>

        </x-tab.tabview>

    </x-baseview>

    <div x-data="{ open: @entangle('showCreate') }">
        <x-modal confirmText="{{ __('Save') }}" action="save">
            <p class="text-xl font-semibold">{{ __('New Currency') }}</p>
            <x-input title="{{ __('Name') }}" name="name" placeholder="United States Dollar" />
            <x-input title="{{ __('Country Code') }} (2 ISO code)" name="country_code" placeholder="US" />
            <a href="https://www.iban.com/country-codes" target="_blank"
                class="text-sm underline text-primary-500">https://www.iban.com/country-codes</a>
            <div class='grid grid-cols-2 gap-4'>
                <x-input title="{{ __('Code') }}" name="code" placeholder="USD" />
                <x-input title="{{ __('Symbol') }}" name="symbol" placeholder="$" />
            </div>
            <a href="https://thefactfile.org/countries-currencies-symbols/" target="_blank"
                class="text-sm underline text-primary-500">https://thefactfile.org/countries-currencies-symbols/</a>
        </x-modal>
    </div>

    <div x-data="{ open: @entangle('showEdit') }">
        <x-modal confirmText="Update" action="update">
            <p class="text-xl font-semibold">{{ __('Edit Currency') }}</p>
            <x-input title="{{ __('Name') }}" name="name" placeholder="United States Dollar" />
            <x-input title="{{ __('Country Code') }} (2 ISO code)" name="country_code" placeholder="US" />
            <a href="https://www.iban.com/country-codes" target="_blank"
                class="text-sm underline text-primary-500">https://www.iban.com/country-codes</a>
            <div class='grid grid-cols-2 gap-4'>
                <x-input title="{{ __('Code') }}" name="code" placeholder="USD" />
                <x-input title="{{ __('Symbol') }}" name="symbol" placeholder="$" />
            </div>
            <a href="https://thefactfile.org/countries-currencies-symbols/" target="_blank"
                class="text-sm underline text-primary-500">https://thefactfile.org/countries-currencies-symbols/</a>
        </x-modal>
    </div>

</div>
