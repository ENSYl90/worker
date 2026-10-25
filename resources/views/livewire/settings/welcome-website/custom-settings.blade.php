<div wire:init="loadContents">
    @production
        <x-form action="saveAppSettings" :noClass="true">
            <div class="space-y-8">
                <x-textarea name="websiteCustomCode" title="{{ __('Website Custom Source Code') }}" />
                <x-buttons.primary title="{{ __('Save Changes') }}" />
            </div>
        </x-form>
    @else
        <div class="p-8 text-center bg-white shadow-sm border rounded">
            {{ __('Only available in production.') }}
        </div>
    @endproduction

</div>
