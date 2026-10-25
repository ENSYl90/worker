@section('title', __('Email Templates'))
<div>

    <x-baseview title="{{ __('Email Templates') }}">
        <div class="space-y-4">
            @foreach ($templates as $mailable => $rows)
                <div class="p-4 bg-white border border-gray-200 rounded-lg">
                    <p class="mb-3 text-sm font-semibold text-gray-500 uppercase">{{ str_replace('_', ' ', $mailable) }}</p>
                    <div class="divide-y divide-gray-100">
                        @foreach ($rows as $row)
                            <div class="flex items-center justify-between py-3">
                                <div>
                                    <p class="font-medium">{{ $row['label'] }}</p>
                                    @if (!empty($row['subject']) || !empty($row['body']))
                                        <p class="text-xs text-primary-600">{{ __('Customized') }}</p>
                                    @else
                                        <p class="text-xs text-gray-400">{{ __('Using default content') }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('email.template.preview', ['type' => $row['key']]) }}"
                                        target="_blank" class="text-sm text-blue-600 underline">
                                        {{ __('Preview') }}
                                    </a>
                                    <a href="{{ route('settings.email.templates.edit', ['key' => $row['key']]) }}"
                                        target="_blank" class="text-sm text-primary-600 underline">
                                        {{ __('Edit') }}
                                    </a>
                                    @if (!empty($row['subject']) || !empty($row['body']))
                                        <button type="button" wire:click="resetTemplate('{{ $row['key'] }}')"
                                            onclick="return confirm('{{ __('Revert this email to its default content?') }}')"
                                            class="text-sm text-red-600 underline">
                                            {{ __('Reset') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- order update: preview-only, structural content, not editable --}}
            <div class="p-4 bg-white border border-gray-200 rounded-lg">
                <p class="mb-3 text-sm font-semibold text-gray-500 uppercase">{{ __('Order Update') }}</p>
                <div class="flex items-center justify-between py-3">
                    <div>
                        <p class="font-medium">{{ __('Order Receipt / Update') }}</p>
                        <p class="text-xs text-gray-400">{{ __('Structural content, preview only') }}</p>
                    </div>
                    <a href="{{ route('email.template.preview', ['type' => 'order_update']) }}" target="_blank"
                        class="text-sm text-blue-600 underline">
                        {{ __('Preview') }}
                    </a>
                </div>
            </div>
        </div>
    </x-baseview>
</div>
