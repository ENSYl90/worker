@section('title', __('My Earning'))
<div>

    <x-baseview title="{{ __('Earnings Overview') }}">

        {{-- Statistics Row --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 mb-10">

            {{-- Balance Hero Card --}}
            <div class="bg-primary-600 rounded-xl p-6 shadow-md relative overflow-hidden flex flex-col justify-between">
                <div class="relative z-10 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-theme opacity-80 text-[10px] font-bold uppercase tracking-widest">
                            {{ __('Available Balance') }}
                        </p>
                        <div class="p-2 bg-theme bg-opacity-20 rounded-lg backdrop-blur-sm">
                            <x-tabler-wallet class="w-6 h-6 text-primary-600" />
                        </div>
                    </div>
                    <div class="mb-6">
                        <h2 class="text-3xl font-black text-theme tracking-tight">
                            {{ currencyFormat($earning->amount ?? 0.00) }}
                        </h2>
                    </div>
                    <button wire:click="initiatePayout('{{ $earning?->id }}')"
                        class="mt-auto w-full py-2.5 bg-theme text-primary-600 font-bold rounded-lg shadow-sm hover:opacity-95 transition-all flex items-center justify-center text-sm">
                        <x-heroicon-o-plus-circle class="w-4 h-4 mr-2" />
                        {{ __('Request Withdrawal') }}
                    </button>
                </div>
            </div>

            {{-- Vendor Identity Card --}}
            <div
                class="bg-white border border-gray-100 rounded-xl p-6 shadow-sm flex items-center space-x-4 transition-all hover:shadow-md">

                @php
                    $vendor = $earning?->vendor;
                    $vendor ??= \Auth::user()->vendor;
                @endphp

                <div
                    class="w-16 h-16 bg-gray-50 rounded-lg flex items-center justify-center p-2 border border-gray-50 shadow-sm overflow-hidden flex-shrink-0">
                    <img src="{{ $vendor->logo ?? '' }}" alt="{{ $vendor->name ?? '' }}"
                        class="w-full h-full object-contain" />
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">{{ __('Account') }}
                    </h4>
                    <p class="text-lg font-bold text-gray-900 leading-tight truncate">{{ $vendor->name ?? '' }}
                    </p>
                    <div class="flex items-center mt-1">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                        <span
                            class="text-[10px] font-bold text-green-600 uppercase tracking-tighter">{{ __('Active Partner') }}</span>
                    </div>
                </div>
            </div>

            {{-- Activity/Sync Card --}}
            <div
                class="bg-white border border-gray-100 rounded-xl p-6 shadow-sm flex items-center space-x-4 transition-all hover:shadow-md">
                <div
                    class="w-16 h-16 bg-blue-50 rounded-lg flex items-center justify-center border border-blue-50 flex-shrink-0">
                    <x-heroicon-o-refresh class="w-8 h-8 text-blue-500" />
                </div>
                <div class="flex-1">
                    <h4 class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">
                        {{ __('Last Updated') }}
                    </h4>
                    <p class="text-lg font-bold text-gray-900 leading-tight">
                        {{ $earning?->updated_at ? $earning->updated_at->format('d M, Y') : __('N/A') }}
                    </p>
                    <p class="text-[11px] text-gray-400 mt-0.5 font-medium tracking-tight">
                        {{ $earning?->updated_at ? $earning->updated_at->diffForHumans() : '' }}
                    </p>
                </div>
            </div>

        </div>

        {{-- Table Section --}}
        <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden mt-10">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/30 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ __('Transaction History') }}</h3>
                </div>
                <div class="hidden sm:block">
                    <x-heroicon-o-document-report class="w-6 h-6 text-primary-600 opacity-20" />
                </div>
            </div>
            <div class="p-2">
                <livewire:tables.my-payout-table />
            </div>
        </div>

    </x-baseview>

    {{-- Withdrawal Modal --}}
    <div x-data="{ open: @entangle('showCreate') }">
        <x-modal confirmText="{{ __('Process Payout') }}" action="payout" title="{{ __('Withdraw Funds') }}">

            <div class="p-6">
                <div class="mb-10 text-center">
                    <div
                        class="inline-flex items-center px-5 py-2 bg-primary-50 rounded-lg mb-4 border border-primary-100">
                        <span class="text-primary-600 font-bold text-xl">
                            {{ __('Balance') }}: {{ currencyFormat($earning->amount ?? 0) }}
                        </span>
                    </div>
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight">{{ __('Submit Your Request') }}</h2>
                    <p class="text-gray-400 text-sm mt-1">
                        {{ __('Your funds will be processed within the standard timeframe') }}
                    </p>
                </div>

                <div class="">
                    <div class="space-y-6">
                        <x-input title="{{ __('Withdrawal Amount') }}" name="amount" placeholder="0.00" type="number" />
                        <x-select title="{{ __('Method') }}" :options="$paymentMethods" name="payment_method_id" />
                    </div>
                    <div>
                        <x-textarea title="{{ __('Note') }}" name="note"
                            placeholder="{{ __('Add a reason or reference...') }}" h="h-40" />
                    </div>
                </div>

                <div class="mt-10 flex items-start p-5 bg-amber-50 rounded-xl border border-amber-100">
                    <x-heroicon-o-information-circle class="w-6 h-6 text-amber-500 mr-4 mt-0.5 flex-shrink-0" />
                    <div class="flex-1">
                        <h4 class="text-xs font-bold text-amber-900 uppercase tracking-widest mb-1">
                            {{ __('Important Notice') }}
                        </h4>
                        <p class="text-[11px] text-amber-700 leading-relaxed font-bold">
                            {{ __('Verification stage: All payouts are subject to administrative review.') }}
                            {{ __("This usually takes 1 to 3 business days for final approval and transfer.") }}
                        </p>
                    </div>
                </div>
            </div>

        </x-modal>
    </div>

</div>