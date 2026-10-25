@section('title', __('API Security Settings'))
<div>
    <x-baseview title="{{ __('API Security Settings') }}">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-1">
                <x-form action="saveSettings">
                    <x-checkbox title="{{ __('Enforce secure API key check') }}" name="enforceApiKey"
                        description="{{ __('When enabled, every API request must include a valid key in the X-API-Key header or Authorization Bearer token.') }}" />

                    <x-input title="{{ __('API key cache duration') }}" name="keyCacheMinutes" type="number" min="1"
                        max="1440">
                        <x-slot name="hint">
                            <span class="text-sm text-gray-500">
                                {{ __('Minutes to cache each validated API key before checking the database again.') }}
                            </span>
                        </x-slot>
                    </x-input>

                    <x-buttons.primary title="{{ __('Save Security Setting') }}" />
                </x-form>
            </div>

            <div class="lg:col-span-2">
                <x-form action="generateKey">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="md:col-span-2">
                            <x-input title="{{ __('Key Name') }}" name="keyName"
                                placeholder="{{ __('Optional name for this key') }}" />

                        </div>
                        <div class="flex items-end">
                            <x-buttons.primary title="{{ __('Generate Secure Key') }}" :noMargin="true" />
                        </div>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ __('New keys will start with') }}
                        <span class="font-mono">{{ $this->generatedKeyPrefix }}</span>
                    </p>
                </x-form>

                @if ($plainApiKey)
                    <div class="p-4 my-5 bg-yellow-50 border border-yellow-200 rounded shadow">
                        <div class="flex items-start gap-3">
                            <x-heroicon-o-exclamation class="flex-shrink-0 w-6 h-6 text-yellow-600" />
                            <div class="w-full">
                                <p class="font-semibold text-yellow-800">{{ __('Copy this key now') }}</p>
                                <p class="mt-1 text-sm text-yellow-700">
                                    {{ __('This raw API key is shown only once. After you leave or hide it, it cannot be viewed again.') }}
                                </p>
                                <div class="flex flex-col gap-3 mt-3 md:flex-row">
                                    <input readonly value="{{ $plainApiKey }}"
                                        class="w-full px-3 py-2 font-mono text-sm text-gray-800 bg-white border border-yellow-300 rounded" />
                                    <button type="button"
                                        class="px-4 py-2 text-sm font-medium text-yellow-800 bg-yellow-100 border border-yellow-300 rounded hover:bg-yellow-200"
                                        wire:click="clearPlainApiKey">
                                        {{ __('Hide') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="my-5 overflow-hidden bg-white rounded shadow">
                    <div class="px-4 py-3 border-b">
                        <p class="font-semibold text-gray-700">{{ __('Generated API Keys') }}</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3">{{ __('Name') }}</th>
                                    <th class="px-4 py-3">{{ __('Key') }}</th>
                                    <th class="px-4 py-3">{{ __('Status') }}</th>
                                    <th class="px-4 py-3">{{ __('Last Used') }}</th>
                                    <th class="px-4 py-3">{{ __('Created') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($apiKeys as $apiKey)
                                    <tr class="border-t">
                                        <td class="px-4 py-3">{{ $apiKey->name ?? __('Untitled key') }}</td>
                                        <td class="px-4 py-3 font-mono">{{ $apiKey->key_prefix }}...{{ $apiKey->last_four }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="px-2 py-1 text-xs rounded {{ $apiKey->is_active ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100' }}">
                                                {{ $apiKey->is_active ? __('Active') : __('Disabled') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $apiKey->last_used_at ? $apiKey->last_used_at->format('d M Y h:i a') : __('Never') }}
                                        </td>
                                        <td class="px-4 py-3">{{ $apiKey->created_at->format('d M Y') }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-end gap-2">
                                                <button type="button"
                                                    class="px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded hover:bg-gray-200"
                                                    wire:click="toggleKeyStatus({{ $apiKey->id }})">
                                                    {{ $apiKey->is_active ? __('Disable') : __('Enable') }}
                                                </button>
                                                <button type="button"
                                                    class="px-3 py-2 text-xs font-medium text-red-700 bg-red-100 rounded hover:bg-red-200"
                                                    onclick="confirm('{{ __('Are you sure you want to revoke this API key? This cannot be undone.') }}') || event.stopImmediatePropagation()"
                                                    wire:click="deleteKey({{ $apiKey->id }})">
                                                    {{ __('Revoke') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                            {{ __('No API keys have been generated yet.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </x-baseview>
</div>