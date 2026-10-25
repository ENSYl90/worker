@section('title', __('Data Export'))
    <div>

        <x-baseview title="{{ __('Data Export') }}">

            <div class="grid grid-cols-1 gap-6 mt-10 md:grid-cols-2 lg:grid-cols-3">

                <div>
                    <x-settings-item title="{{ __('Categories') }}" wireClick="exportData(1, 'Categories')">
                        <x-heroicon-o-folder class="w-5 h-5 mr-4" />
                    </x-settings-item>
                </div>
                <div>
                    <x-settings-item title="{{ __('Subcategories') }}" wireClick="exportData(2, 'Subcategories')">
                        <x-heroicon-o-folder class="w-5 h-5 mr-4" />
                    </x-settings-item>
                </div>

                <div>
                    <x-settings-item title="{{ __('Vendors') }}" wireClick="exportData(3, 'Vendors')">
                        <x-heroicon-o-shopping-cart class="w-5 h-5 mr-4" />
                    </x-settings-item>
                </div>
                <div>
                    <x-settings-item title="{{ __('Menus') }}" wireClick="exportData(4, 'Menus')">
                        <x-heroicon-o-book-open class="w-5 h-5 mr-4" />
                    </x-settings-item>
                </div>
                <div>
                    <x-settings-item title="{{ __('Products') }}" wireClick="exportData(5, 'Products')">
                        <x-heroicon-o-archive class="w-5 h-5 mr-4" />
                    </x-settings-item>
                </div>
                <div>
                    <x-settings-item title="{{ __('Services') }}" wireClick="exportData(6, 'Services')">
                        <x-heroicon-o-archive class="w-5 h-5 mr-4" />
                    </x-settings-item>
                </div>

                <div>
                    <x-settings-item title="{{ __('Earnings') }}" wireClick="exportData(7, 'Earnings')">
                        <x-heroicon-o-archive class="w-5 h-5 mr-4" />
                    </x-settings-item>
                </div>
                <div>
                    <x-settings-item title="{{ __('Payouts') }}" wireClick="exportData(8, 'Payouts')">
                        <x-heroicon-o-archive class="w-5 h-5 mr-4" />
                    </x-settings-item>
                </div>

            </div>

            <div class="mt-10" wire:poll.3s>
                <p class="mb-4 text-lg font-semibold">{{ __('Recent Export Jobs') }}</p>
                <div class="overflow-x-auto bg-white border rounded">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase bg-gray-100">
                            <tr>
                                <th class="px-4 py-3">{{ __('Data') }}</th>
                                <th class="px-4 py-3">{{ __('Status') }}</th>
                                <th class="px-4 py-3">{{ __('Progress') }}</th>
                                <th class="px-4 py-3">{{ __('Message') }}</th>
                                <th class="px-4 py-3">{{ __('Download') }}</th>
                                <th class="px-4 py-3">{{ __('Created') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($exportJobs as $job)
                                <tr class="border-t">
                                    <td class="px-4 py-3">{{ $job->data_type_name }}</td>
                                    <td class="px-4 py-3 capitalize">{{ __($job->status) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="w-40 h-2 bg-gray-200 rounded">
                                            <div class="h-2 bg-primary-500 rounded" style="width: {{ $job->progress }}%"></div>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">{{ $job->progress }}%</p>
                                    </td>
                                    <td class="px-4 py-3">{{ $job->message }}</td>
                                    <td class="px-4 py-3">
                                        @if ($job->download_url)
                                            <a href="{{ $job->download_url }}" download class="text-primary-600 underline">
                                                {{ __('Download') }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $job->created_at ? $job->created_at->format('d M Y h:i a') : '' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">{{ __('No export jobs yet') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </x-baseview>

        {{-- import dialog --}}
        <div x-data="{ open: @entangle('showCreate') }">
            <x-modal confirmText="Import" action="processImport">
                <p class="text-xl font-semibold">Import {{ $dataTypeName ?? '' }}</p>
                <x-media-upload title="Data File" name="photo" :photo="$photo" :photoInfo="$photoInfo" :image="false"
                    types="xlsx" rules="xls" />
            </x-modal>
        </div>

    </div>
