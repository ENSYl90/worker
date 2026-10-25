<div class="bg-white border border-gray-200 rounded-lg shadow-sm">
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-6">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('Exchange Rates Manager') }}</h3>

                <!-- Modern Enable/Disable Toggle -->
                <div class="flex items-center gap-3 px-4 py-2 bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 {{ $exchangeRateEnabled ? 'text-green-500' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">
                            {{ __('Exchange Rate') }}
                        </span>
                    </div>

                    <div wire:click="toggleExchangeRate"
                         class="relative inline-flex items-center cursor-pointer group">
                        <div class="relative">
                            <!-- Toggle Track -->
                            <div class="w-14 h-7 rounded-full transition-all duration-300 ease-in-out {{ $exchangeRateEnabled ? 'bg-gradient-to-r from-green-400 to-green-500 shadow-lg shadow-green-500/50' : 'bg-gray-300' }}"></div>

                            <!-- Toggle Circle -->
                            <div class="absolute top-0.5 left-0.5 w-6 h-6 bg-white rounded-full shadow-md transition-all duration-300 ease-in-out transform {{ $exchangeRateEnabled ? 'translate-x-7' : 'translate-x-0' }}">
                                <!-- Icon inside circle -->
                                @if($exchangeRateEnabled)
                                    <svg class="w-6 h-6 text-green-500 p-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-gray-400 p-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold transition-all duration-300 {{ $exchangeRateEnabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $exchangeRateEnabled ? 'bg-green-500 animate-pulse' : 'bg-gray-400' }}"></span>
                        {{ $exchangeRateEnabled ? __('Active') : __('Inactive') }}
                    </div>
                </div>
            </div>

            @if($exchangeRateEnabled)
            <button wire:click="toggleForm"
                class="px-4 py-2 transition-colors rounded-md bg-primary-500 text-theme hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                {{ $showForm ? __('Hide Form') : __('Update Rates') }}
            </button>
            @endif
        </div>
    </div>

    <div class="p-6 {{ !$exchangeRateEnabled ? 'opacity-50 pointer-events-none' : '' }}">
        @if(!$exchangeRateEnabled)
        <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>{{ __('Exchange Rate Disabled') }}</strong> - {{ __('Enable the exchange rate feature above to manage currency exchange rates.') }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Date Selector -->
        <div class="mb-6">
            <label for="effectiveDate" class="block mb-2 text-sm font-medium text-gray-700">
                {{ __('Effective Date') }}
            </label>
            <div class="flex items-center gap-2">
                <input type="date" id="effectiveDate" wire:model="effectiveDate"
                    class="block w-48 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                <button wire:click="loadRatesForDate"
                    class="px-4 py-2 text-white transition-colors bg-gray-600 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    {{ __('Load Rates') }}
                </button>
            </div>
            @error('effectiveDate')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Exchange Rates Table -->
        <div class="overflow-hidden border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            {{ __('Currency') }}
                        </th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            {{ __('Code') }}
                        </th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            {{ __('Symbol') }}
                        </th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            {{ __('Exchange Rate') }}
                        </th>
                        @if ($showForm)
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                {{ __('Actions') }}
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($exchangeRates as $index => $rate)
                        <tr class="{{ $rate['is_base'] ? 'bg-primary-100' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $rate['currency_name'] }}
                                        @if ($rate['is_base'])
                                            <span
                                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-200 text-primary-800">
                                                {{ __('Base') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                {{ $rate['currency_code'] }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                {{ $rate['currency_symbol'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($showForm && $editingRateId === $index)
                                    <div class="flex items-center gap-2">
                                        <input type="number" step="0.00000001"
                                            wire:model.defer="exchangeRates.{{ $index }}.rate"
                                            onkeydown="if(event.key === 'Enter') { @this.updateRate({{ $index }}, this.value); }"
                                            class="block w-32 px-3 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 {{ $rate['is_base'] ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                            {{ $rate['is_base'] ? 'readonly' : '' }}>
                                        <button
                                            onclick="@this.updateRate({{ $index }}, this.previousElementSibling.value)"
                                            class="p-1 text-green-600 hover:text-green-800"
                                            title="{{ __('Save') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    @error("exchangeRates.{$index}.rate")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @else
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="text-sm font-mono {{ $rate['is_base'] ? 'text-primary-600 font-semibold' : 'text-gray-900' }}">
                                            {{ $rate['rate'] ? number_format($rate['rate'], 8) : __('Not Set') }}
                                        </span>
                                        @if ($showForm && !$rate['is_base'])
                                            <button wire:click="editRate({{ $index }})"
                                                class="p-1 ml-2 text-primary-600 hover:text-primary-800"
                                                title="{{ __('Edit') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            @if ($showForm)
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                    @if (!$rate['is_base'] && $rate['id'])
                                        <button wire:click="deleteRate({{ $rate['currency_id'] }})"
                                            class="p-1 text-red-600 hover:text-red-800" title="{{ __('Delete') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Action Buttons -->
        @if ($showForm)
            <div class="flex items-center justify-end gap-3 mt-6">
                <button wire:click="toggleForm"
                    class="px-4 py-2 text-gray-700 transition-colors border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    {{ __('Cancel') }}
                </button>
                <button wire:click="saveRates"
                    class="px-4 py-2 text-white transition-colors bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    {{ __('Save All Rates') }}
                </button>
            </div>
        @endif

        <!-- Info Note -->
        <div class="p-4 mt-4 border rounded-md bg-primary-100 border-primary-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-primary-700">
                        <strong>{{ __('Note') }}:</strong>
                        {{ __('The base currency (highlighted in blue) always has a rate of 1.0 and cannot be modified. All other rates represent the conversion rate from the base currency to that currency.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div wire:loading.flex class="absolute inset-0 items-center justify-center bg-white bg-opacity-75 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3 -ml-1 animate-spin text-primary" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span class="text-primary">{{ __('Loading...') }}</span>
        </div>
    </div>
</div>
