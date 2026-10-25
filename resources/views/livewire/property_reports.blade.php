@section('title', __('Property Reports'))
<div>

    <x-baseview title="{{ __('Property Reports') }}" :showNew="false">
        <livewire:tables.property-report-table />
    </x-baseview>

    {{-- Details/Action Modal --}}
    <div x-data="{ open: @entangle('showDetails') }">
        <x-modal-lg confirmText="{{ __('Resolve') }}" action="resolve">
            <p class="text-xl font-semibold mb-4">{{ __('Report Details') }}</p>
            @if($selectedModel)
                <div class="space-y-4">
                    <div>
                        <x-label title="{{ __('Reported By') }}" />
                        <p class="text-sm">{{ $selectedModel->user->name ?? '' }}</p>
                    </div>
                    <div>
                        <x-label title="{{ __('Property') }}" />
                        <p class="text-sm font-semibold">{{ $selectedModel->property->name ?? '' }}</p>
                    </div>
                    <div>
                        <x-label title="{{ __('Reason') }}" />
                        <p class="text-sm text-red-600">{{ $selectedModel->reason->reason ?? '' }}</p>
                    </div>
                    <div>
                        <x-label title="{{ __('Details') }}" />
                        <p class="text-sm bg-gray-50 p-4 rounded">{{ $selectedModel->details }}</p>
                    </div>
                    <div>
                        <x-label title="{{ __('Status') }}" />
                        <span
                            class="px-2 py-1 text-xs rounded-full {{ $selectedModel->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : ($selectedModel->status == 'resolved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                            {{ ucfirst($selectedModel->status) }}
                        </span>
                    </div>

                    @if($selectedModel->status == 'pending')
                        <div class="flex justify-end space-x-2 mt-6 pt-4 border-t">
                            <x-buttons.plain type="button" wire:click="reject" wire:loading.attr="disabled">
                                {{ __('Reject Report') }}
                            </x-buttons.plain>
                        </div>
                    @endif
                </div>
            @endif
        </x-modal-lg>
    </div>
</div>