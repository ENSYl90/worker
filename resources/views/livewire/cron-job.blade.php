@section('title', __('CRON Job'))
<div>

    <x-baseview title="{{ __('CRON Job') }} ">
        <div class="p-5 border shadow">
            <p>
                {{ __('How to setup cron job on your server:') }} <a href="https://bit.ly/3DoNGBm" target="_blank"
                    class="font-bold underline text-primary-500">https://bit.ly/3DoNGBm</a>
            </p>
            <hr class="my-6" />
            <p class="mb-2 text-2xl font-bold">{{ __('Cron job on Server') }}</p>
            @production
                <p class="mb-2">
                    {{ __('For server cron job setup, copy the text below and paste into your cron job tab on your server') }}
                </p>
                <div class="space-y-2">
                    <div class="w-full p-2 bg-gray-200 rounded broder">
                        {{ PHP_BINARY }} {{ base_path() }}/artisan schedule:run >> /dev/null 2>&1
                    </div>
                    <p class="mt-4 mb-2 text-lg font-semibold">{{ __('For queued jobs/tasks') }}</p>
                    <div class="p-3 mb-3 bg-yellow-100 border-l-4 border-yellow-500 rounded">
                        <p class="text-sm text-yellow-800">
                            <span class="font-semibold">{{ __('Warning:') }}</span>
                            {{ __('These queue worker commands should be run in supervisor on a VPS, not added to regular cron jobs. They are meant to run continuously as daemon processes.') }}
                        </p>
                    </div>
                    <div class="w-full p-2 bg-gray-200 rounded broder">
                        {{ PHP_BINARY }} {{ base_path() }}/artisan queue:work
                    </div>
                </div>
                <p class="mx-4 my-2 text-sm font-light"><span class="font-semibold text-red-500">{{ __('Note:') }}</span>
                    {{ __('This command uses the same PHP binary that your web application is running on.') }}
                </p>
            @endproduction
            <hr class="my-6" />
            <p class="my-2 text-sm font-medium">
                {{ __('Last Run Time') }}:
                <span class="text-xl font-bold">{{ setting('cronJobLastRun', __('Never')) }}</span>
            </p>
            <hr class="my-6" />
            @production

                <p class="mb-2 text-2xl font-bold">{{ __('External cron job managers') }}</p>
                <p class="mb-2">{{ __('For external cron job managers like') }}(e.g https://cron-job.org).
                    {{ __('Copy the url below:') }}</p>
                <div class="items-center block w-full space-y-2 md:space-x-2 md:flex">
                    <div class="w-full p-2 bg-gray-200 rounded md:w-9/12 broder">
                        {{ route('cron.job') }}?key={{ $cronJobKey ?? '' }}
                    </div>
                    <div class="w-full md:w-3/12">
                        <x-buttons.primary title="{{ __('Generate New Key') }}" wireClick="genNewKey" noMargin="true" />
                    </div>
                </div>
            @endproduction


        </div>
    </x-baseview>


</div>
