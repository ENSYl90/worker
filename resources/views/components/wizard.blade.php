@props(['steps', 'currentStep', 'finishLabel' => 'Finish', 'finishAction' => 'finish'])

<div x-data="{ step: @entangle('currentStep') }" class="space-y-6 p-6">
    <!-- Step Indicator -->
    <div class="flex justify-between mb-4">
        @foreach ($steps as $index => $label)
            <div
                class="flex-1 text-center text-sm {{ $index + 1 == $currentStep ? 'font-bold text-blue-600' : 'text-gray-400' }}">
                Step {{ $index + 1 }}: {{ $label }}
            </div>
        @endforeach
    </div>

    <!-- Steps -->
    <div>
        {{ $slot }}
    </div>

    <!-- Navigation Buttons -->
    <div class="flex justify-between mt-4">
        <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded disabled:opacity-50"
            wire:click="previousStep" x-bind:disabled="step === 1">
            {{ __('Previous') }}
        </button>

        <div>
            <button type="button" class="bg-primary-500 text-theme px-4 py-2 rounded mr-2" wire:click="nextStep"
                x-show="step < {{ count($steps) }}">
                {{ __('Next') }}
            </button>

            <button type="button" class="bg-primary-600 text-theme px-4 py-2 rounded" wire:click="{{ $finishAction }}"
                x-show="step === {{ count($steps) }}">{{ __($finishLabel) }}</button>

        </div>
    </div>
</div>
