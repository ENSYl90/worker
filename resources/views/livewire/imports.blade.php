@section('title', __('Data Import'))
    <div>

        <x-baseview title="{{ __('Data Import') }}">

            <div class="grid grid-cols-1 gap-6 mt-10 md:grid-cols-2 lg:grid-cols-3">

                <div>
                    <x-settings-item title="{{ __('Categories') }}" wireClick="showImportDialog(1, 'Categories')">
                        <x-heroicon-o-folder class="w-5 h-5 mr-4" />
                    </x-settings-item>
                    <a href="{{ asset('xlxs/categories.xlsx') }}" download class="text-sm text-gray-500 underline">{{ __('Download sample') }}</a>
                </div>
                <div>
                    <x-settings-item title="{{ __('Subcategories') }}" wireClick="showImportDialog(5, 'Subcategories')">
                        <x-heroicon-o-folder class="w-5 h-5 mr-4" />
                    </x-settings-item>
                    <a href="{{ asset('xlxs/subcategories.xlsx') }}" download class="text-sm text-gray-500 underline">{{ __('Download sample') }}</a>
                </div>

                <div>
                    <x-settings-item title="{{ __('Vendors') }}" wireClick="showImportDialog(2, 'Vendors')">
                        <x-heroicon-o-shopping-cart class="w-5 h-5 mr-4" />
                    </x-settings-item>
                    <a href="{{ asset('xlxs/vendors.xlsx') }}" download class="text-sm text-gray-500 underline">{{ __('Download sample') }}</a>
                </div>
                <div>
                    <x-settings-item title="{{ __('Menus') }}" wireClick="showImportDialog(3, 'Menus')">
                        <x-heroicon-o-book-open class="w-5 h-5 mr-4" />
                    </x-settings-item>
                    <a href="{{ asset('xlxs/menus.xlsx') }}" download class="text-sm text-gray-500 underline">{{ __('Download sample') }}</a>
                </div>
                <div>
                    <x-settings-item title="{{ __('Products') }}" wireClick="showImportDialog(4, 'Products')">
                        <x-heroicon-o-archive class="w-5 h-5 mr-4" />
                    </x-settings-item>
                    <a href="{{ asset('xlxs/products.xlsx') }}" download class="text-sm text-gray-500 underline">{{ __('Download sample') }}</a>
                </div>

                <div>
                    <x-settings-item title="{{ __('Services') }}" wireClick="showImportDialog(6, 'Services')">
                        <x-heroicon-o-archive class="w-5 h-5 mr-4" />
                    </x-settings-item>
                    {{-- TODO: Service demo file --}}
                    <a href="{{ asset('xlxs/services.xlsx') }}" download class="text-sm text-gray-500 underline">{{ __('Download sample') }}</a>
                </div>

            </div>

            <div class="mt-10" wire:poll.3s>
                <p class="mb-4 text-lg font-semibold">{{ __('Recent Import Jobs') }}</p>
                <div class="overflow-x-auto bg-white border rounded">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase bg-gray-100">
                            <tr>
                                <th class="px-4 py-3">{{ __('Data') }}</th>
                                <th class="px-4 py-3">{{ __('Status') }}</th>
                                <th class="px-4 py-3">{{ __('Progress') }}</th>
                                <th class="px-4 py-3">{{ __('Message') }}</th>
                                <th class="px-4 py-3">{{ __('Created') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($importJobs as $job)
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
                                    <td class="px-4 py-3">{{ $job->created_at ? $job->created_at->format('d M Y h:i a') : '' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">{{ __('No import jobs yet') }}</td>
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
