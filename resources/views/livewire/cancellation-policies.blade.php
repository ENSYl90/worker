@section('title', __('Cancellation Policies'))
<div>
    <x-baseview title="{{ __('Cancellation Policies') }}" :showNew="true">
        <livewire:tables.cancellation-policy-table />
    </x-baseview>
    <div x-data="{ open: @entangle('showCreate') }">
        <x-modal-lg confirmText="{{ __('Save') }}" action="save" size="lg">
            <p class="text-xl font-semibold">{{ __('New Cancellation Policy') }}</p>


            <x-input title="{{ __('Name') }}" name="name" placeholder="{{ __('Flexible, Moderate, Strict') }}" />



            <div class="col-span-full">
                <x-textarea title="{{ __('Description') }}" name="description"
                    placeholder="{{ __('Policy description') }}" />
            </div>

            <div class="col-span-full">
                <x-checkbox title="{{ __('Is Active') }}" name="is_active" />
            </div>


            <!-- Refund Rules Section -->
            <div class="mt-6 border-t pt-4">
                <h3 class="text-lg font-semibold mb-4">{{ __('Refund Rules') }}</h3>

                <!-- Add Rule Form -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <x-input title="{{ __('Days Before Check-in') }}" name="rule_days_before" type="number" min="0"
                        placeholder="7" />
                    <x-input title="{{ __('Refund Percentage') }}" name="rule_refund_percentage" type="number" min="0"
                        max="100" step="0.01" placeholder="100" />
                    <div class="flex items-end">
                        <button type="button" wire:click="addRule"
                            class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                            {{ __('Add Rule') }}
                        </button>
                    </div>
                </div>

                <!-- Display Rules -->
                @if(!empty($refund_percentage_rules))
                    <div class="bg-gray-50 rounded-lg p-4">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('Days Before') }}
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('Refund %') }}
                                    </th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($refund_percentage_rules as $index => $rule)
                                    <tr>
                                        <td class="px-4 py-2 text-sm">{{ $rule['days_before'] }} {{ __('days') }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $rule['refund_percentage'] }}%</td>
                                        <td class="px-4 py-2 text-sm text-right">
                                            <button type="button" wire:click="removeRule({{ $index }})"
                                                class="text-red-600 hover:text-red-800">
                                                {{ __('Remove') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-sm">
                        {{ __('No rules added yet. Add rules to define refund percentages based on cancellation timing.') }}
                    </p>
                @endif

                <div class="mt-4 p-3 bg-primary-50 rounded-lg">
                    <p class="text-sm text-primary-800">
                        <strong>{{ __('Example:') }}</strong>
                        {{ __('7 days before = 100% refund, 3 days before = 50% refund, less than 3 days = 0% refund') }}
                    </p>
                </div>
            </div>
        </x-modal-lg>
    </div>

    <div x-data="{ open: @entangle('showEdit') }">
        <x-modal-lg confirmText="{{ __('Update') }}" action="save" size="lg">
            <p class="text-xl font-semibold">{{ __('Update Cancellation Policy') }}</p>


            <x-input title="{{ __('Name') }}" name="name" placeholder="{{ __('Flexible, Moderate, Strict') }}" />



            <div class="col-span-full">
                <x-textarea title="{{ __('Description') }}" name="description"
                    placeholder="{{ __('Policy description') }}" />
            </div>

            <div class="col-span-full">
                <x-checkbox title="{{ __('Is Active') }}" name="is_active" />
            </div>


            <!-- Refund Rules Section -->
            <div class="mt-6 border-t pt-4">
                <h3 class="text-lg font-semibold mb-4">{{ __('Refund Rules') }}</h3>

                <!-- Add Rule Form -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <x-input title="{{ __('Days Before Check-in') }}" name="rule_days_before" type="number" min="0"
                        placeholder="7" />
                    <x-input title="{{ __('Refund Percentage') }}" name="rule_refund_percentage" type="number" min="0"
                        max="100" step="0.01" placeholder="100" />
                    <div class="flex items-end">
                        <button type="button" wire:click="addRule"
                            class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                            {{ __('Add Rule') }}
                        </button>
                    </div>
                </div>

                <!-- Display Rules -->
                @if(!empty($refund_percentage_rules))
                    <div class="bg-gray-50 rounded-lg p-4">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('Days Before') }}
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('Refund %') }}
                                    </th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($refund_percentage_rules as $index => $rule)
                                    <tr>
                                        <td class="px-4 py-2 text-sm">{{ $rule['days_before'] }} {{ __('days') }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $rule['refund_percentage'] }}%</td>
                                        <td class="px-4 py-2 text-sm text-right">
                                            <button type="button" wire:click="removeRule({{ $index }})"
                                                class="text-red-600 hover:text-red-800">
                                                {{ __('Remove') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-sm">
                        {{ __('No rules added yet. Add rules to define refund percentages based on cancellation timing.') }}
                    </p>
                @endif

                <div class="mt-4 p-3 bg-primary-50 rounded-lg">
                    <p class="text-sm text-primary-800">
                        <strong>{{ __('Example:') }}</strong>
                        {{ __('7 days before = 100% refund, 3 days before = 50% refund, less than 3 days = 0% refund') }}
                    </p>
                </div>
            </div>
        </x-modal-lg>
    </div>



    {{-- details modal --}}
    <div x-data="{ open: @entangle('showDetails') }">
        <x-modal>

            <p class="text-xl font-semibold mb-4">
                {{ $selectedModel != null ? $selectedModel->name : '' }}
                {{ __('Details') }}
            </p>

            @if($selectedModel)
                <!-- Policy Name -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }}</label>
                    <p class="text-gray-900">{{ $selectedModel->name }}</p>
                </div>

                <!-- Description -->
                @if($selectedModel->description)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                        <p class="text-gray-900 whitespace-pre-line">{{ $selectedModel->description }}</p>
                    </div>
                @endif

                <!-- Status -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }}</label>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $selectedModel->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $selectedModel->is_active ? __('Active') : __('Inactive') }}
                    </span>
                </div>

                <!-- Refund Rules -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Refund Rules') }}</label>
                    @if($selectedModel->refund_percentage_rules && count($selectedModel->refund_percentage_rules) > 0)
                        <div class="bg-gray-50 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Days Before Check-in') }}
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Refund Percentage') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($selectedModel->refund_percentage_rules as $rule)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                @if($rule['days_before'] == 0)
                                                    {{ __('Less than 1 day') }}
                                                @else
                                                    {{ $rule['days_before'] }}+ {{ __('days') }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                                                    {{ $rule['refund_percentage'] }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Description -->
                        <div class="mt-3 p-3 bg-primary-50 rounded-lg">
                            <p class="text-sm text-primary-800">
                                <strong>{{ __('Summary:') }}</strong> {{ $selectedModel->description_text }}
                            </p>
                        </div>
                    @else
                        <p class="text-gray-500 text-sm italic">{{ __('No refund rules defined for this policy.') }}</p>
                    @endif
                </div>

                <!-- Usage Information -->
                <div class="mt-6 pt-4 border-t">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Usage') }}</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-primary-50 p-3 rounded-lg">
                            <p class="text-xs text-primary-600 uppercase font-medium">{{ __('Properties') }}</p>
                            <p class="text-2xl font-bold text-primary-900">{{ $selectedModel->properties()->count() }}</p>
                        </div>
                        <div class="bg-primary-50 p-3 rounded-lg">
                            <p class="text-xs text-primary-600 uppercase font-medium">{{ __('Booking Orders') }}</p>
                            <p class="text-2xl font-bold text-primary-900">{{ $selectedModel->bookingOrders()->count() }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

        </x-modal>
    </div>
</div>