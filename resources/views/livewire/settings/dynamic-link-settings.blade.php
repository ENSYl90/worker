@section('title', __('Dynamic Link Settings'))
<div>

    <x-baseview title="{{ __('Dynamic Link Settings') }}">

        <div class="w-full md:w-8/12 lg:w-6/12">
        <x-form action="saveSettings">

            <div class="">
               <div class='grid grid-cols-1 gap-4 lg:grid-cols-2'>
                    <x-input title="{{ __('Dynamic link prefix') }}" placeholder="{{ __('e.g glover.page.link') }}"
                        name="prefix" />
                    <x-input title="{{ __('Dynamic link scheme') }}" placeholder="{{ __('glover') }}"
                        name="scheme" />
                </div>
                <hr class="my-2" />
                <div class='space-y-2'>
                    <div class='grid grid-cols-1 gap-4 lg:grid-cols-2'>
                        <x-input title="{{ __('Customer App - Android Package Name') }}"
                            placeholder="{{ __('e.g com.glover.customer') }}" name="android" />
                            <div></div>
                            
                            <x-input title="{{ __('Customer App - SHA256 Key') }}"
                            placeholder="AA:BB:CC:DD:EE:FF:00:11:22:33:44:55:66:77:88:99:AA:BB:CC:DD:EE:FF:00:11:22:33:44:55:66:77:88:99" name="android_sha256_key" />
                            <x-input title="{{ __('Customer App - SHA256 Debug Key') }}"
                            placeholder="AA:BB:CC:DD:EE:FF:00:11:22:33:44:55:66:77:88:99:AA:BB:CC:DD:EE:FF:00:11:22:33:44:55:66:77:88:99" name="android_sha256_debug_key" />
                            <x-input title="{{ __('Customer App - SHA256 Release Key') }}"
                            placeholder="AA:BB:CC:DD:EE:FF:00:11:22:33:44:55:66:77:88:99:AA:BB:CC:DD:EE:FF:00:11:22:33:44:55:66:77:88:99" name="android_sha256_release_key" />
                    </div>
                    <hr class="my-2" />
                    <div class='grid grid-cols-1 gap-4 lg:grid-cols-2'>
                    <x-input title="{{ __('Customer App - iOS Bundle ID') }}"
                        placeholder="{{ __('e.g com.glover.customer') }}" name="ios" />
                    <x-input title="{{ __('Customer App - iOS APP ID') }}" placeholder="{{ __('e.g 1234567890') }}"
                        name="ios_id" />
                    </div>

                </div>
                <x-buttons.primary title="{{ __('Save Changes') }}" />
            </div>
        </x-form>
        </div>

    </x-baseview>

</div>
