@section('title', __('Website Settings'))
<div>

    <x-baseview title="{{ __('Website Settings') }}">

        <x-tab.tabview class="shadow pb-10">

            <x-slot name="header">
                <x-tab.header tab="1" title="{{ __('General Settings') }}" />
                <x-tab.header tab="2" title="{{ __('Base') }}" />
                <x-tab.header tab="3" title="{{ __('Modern') }}" />
                <x-tab.header tab="4" title="{{ __('Custom') }}" />
            </x-slot>

            <x-slot name="body">
                <x-tab.body tab="1">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <!-- Base Website -->
                        <label
                            class="{{ $website_style == 'base' ? 'border border-primary-500' : '' }} block rounded-xl p-4 cursor-pointer transition border-2 border-gray-200">
                            <input type="radio" name="website_style" value="base" class="peer hidden"
                                wire:model="website_style">
                            <div class="flex flex-col space-y-2">
                                <h3 class="font-semibold text-lg">{{ __('Base Website') }}</h3>
                                <p class="text-sm text-gray-500">
                                    {{ __('The default layout with familiar styling for a classic experience.') }}
                                </p>
                            </div>
                        </label>

                        <!-- Modern Website -->
                        <label
                            class="{{ $website_style == 'modern' ? 'border border-primary-500' : '' }} block rounded-xl p-4 cursor-pointer transition border-2 border-gray-200">
                            <input type="radio" name="website_style" value="modern" class="peer hidden"
                                wire:model="website_style">
                            <div class="flex flex-col space-y-2">
                                <h3 class="font-semibold text-lg">{{ __('Modern Website') }}</h3>
                                <p class="text-sm text-gray-500">
                                    {{ __('A refreshed layout featuring a clean, minimal, and user-friendly design.') }}
                                </p>
                            </div>
                        </label>

                        {{-- clean --}}
                        <label
                            class="{{ $website_style == 'clean' ? 'border border-primary-500' : '' }} block rounded-xl p-4 cursor-pointer transition border-2 border-gray-200">
                            <input type="radio" name="website_style" value="clean" class="peer hidden"
                                wire:model="website_style">
                            <div class="flex flex-col space-y-2">
                                <h3 class="font-semibold text-lg">{{ __('Clean Website') }}</h3>
                                <p class="text-sm text-gray-500">
                                    {{ __('A clean layout featuring a clean, minimal, and user-friendly design.') }}
                                </p>
                            </div>
                        </label>

                        <!-- Custom Website -->
                        <label
                            class="{{ $website_style == 'custom' ? 'border border-primary-500' : '' }} block rounded-xl p-4 cursor-pointer transition border-2 border-gray-200">
                            <input type="radio" name="website_style" value="custom" class="peer hidden"
                                wire:model="website_style">
                            <div class="flex flex-col space-y-2">
                                <h3 class="font-semibold text-lg">{{ __('Custom Website') }}</h3>
                                <p class="text-sm text-gray-500">
                                    {{ __('Upload your own website files for a fully tailored experience. Note: You are responsible for verifying the code to prevent potential security risks.') }}
                                </p>
                            </div>
                        </label>
                    </div>


                    <x-buttons.primary title="{{ __('Save') }}" wireClick="saveSettings" />
                </x-tab.body>
                <x-tab.body tab="2">
                    <livewire:settings.welcome-website.base-settings />
                </x-tab.body>
                <x-tab.body tab="3">
                    <livewire:settings.welcome-website.modern-settings />
                </x-tab.body>
                <x-tab.body tab="4">
                    <livewire:settings.welcome-website.custom-settings />
                </x-tab.body>

            </x-slot>

        </x-tab.tabview>
    </x-baseview>

</div>