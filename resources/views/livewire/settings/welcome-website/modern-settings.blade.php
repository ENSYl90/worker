<div wire:init="loadContents">
    <x-form action="saveAppSettings" :noClass="true">
         <x-checkbox title="{{ __('Show Backend Button') }}" name="enableBackendButton" :defer="true">
            <p>{{ __("Show the admin/store login button on the welcome page.") }}</p>
         </x-checkbox>
            <hr class="my-4" />
            <x-input name="websiteHeaderTitle" title="{{ __('Website Header Title') }}" />
            <x-textarea name="websiteHeaderSubtitle" title="{{ __('Website Header Subtitle') }}" />
            <x-textarea name="websiteFooterBrief" title="{{ __('Website Footer Brief') }}" />
            <x-input name="websitePhoneSubtitle" title="{{ __('Website Phone Subtitle') }}" />

            <hr class="my-4" />


            {{-- social links --}}
            <div>
                <p class="font-semibold">{{ __('Social Media Links') }}</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <x-input name="fbLink" title="{{ __('Facebook Link') }}" />
                    <x-input name="igLink" title="{{ __('Instagram Link') }}" />
                    <x-input name="twLink" title="{{ __('Twitter Link') }}" />
                    <x-input name="yuLink" title="{{ __('Youtube Link') }}" />
                    <x-input name="liLink" title="{{ __('LinkedIn Link') }}" />
                </div>
            </div>
        </div>


        <x-buttons.primary title="{{ __('Save Changes') }}" />

    </x-form>

</div>
