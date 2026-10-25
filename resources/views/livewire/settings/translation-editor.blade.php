@section('title', __('Translation Editor'))
<div>
    <x-baseview title="{{ __('Translation Editor') }}">

        <!-- Loading Indicator -->
        <div wire:loading class="fixed top-0 left-0 right-0 z-50 bg-primary-600 text-white px-4 py-2 text-center text-sm font-medium">
            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ __('Processing...') }}
        </div>

        <!-- Environment Warning -->
        @if(!$this->isProduction)
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                        {{ __('Production Environment Only') }}
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>{{ __('This Translation Editor is only functional in production environment. You are currently in') }} <strong>{{ app()->environment() }}</strong> {{ __('environment') }}.</p>
                        <p class="mt-2">{{ __('All editing functions are disabled. To enable this feature, switch to production environment.') }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm" @if(!$this->isProduction) style="opacity: 0.6; pointer-events: none;" @endif>
            <!-- Header Section -->
            <div class="p-6 border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Language Selector -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Language') }}</label>
                        <select wire:model="selectedLanguage" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            @foreach($languages as $lang)
                                <option value="{{ $lang['code'] }}">{{ $lang['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Search') }}</label>
                        <div class="flex gap-2">
                            <input type="text" wire:model.lazy="searchTerm"
                                   placeholder="{{ __('Search translations...') }}"
                                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                   wire:keydown.enter="$refresh">
                            <button wire:click="$refresh" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300" title="{{ __('Search') }}">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="flex items-end">
                        <div class="text-sm text-gray-600">
                            <div><strong>{{ count($filteredTranslations) }}</strong> {{ __('translations shown') }}</div>
                            <div><strong>{{ count($translations) }}</strong> {{ __('total translations') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4 flex flex-wrap gap-2">
                    <button wire:click="toggleAddForm" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        {{ __('Add New') }}
                    </button>

                    @if($selectedLanguage !== 'en')
                    <button wire:click="copyFromEnglish" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        {{ __('Copy Missing from English') }}
                    </button>
                    @endif

                    <button wire:click="exportTranslations" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        {{ __('Export') }}
                    </button>

                    <button wire:click="toggleCreateLanguageForm" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                        {{ __('Create New Language') }}
                    </button>
                </div>
            </div>

            <!-- Create New Language Form -->
            @if($showCreateLanguageForm)
            <div class="p-6 bg-purple-50 border-b border-purple-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Create New Language') }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ __('Create a new language by copying translations from an existing language. The new language will be automatically added to your backend configuration.') }}</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Language Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="newLanguageName"
                               placeholder="e.g., Yoruba"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        @error('newLanguageName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Language Code') }} <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="newLanguageCode"
                               placeholder="e.g., yo"
                               maxlength="5"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        <p class="text-xs text-gray-500 mt-1">{{ __('2-5 characters, letters only') }}</p>
                        @error('newLanguageCode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Copy From') }} <span class="text-red-500">*</span></label>
                        <select wire:model.defer="sourceLanguage" class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                            @foreach($languages as $lang)
                                <option value="{{ $lang['code'] }}">{{ $lang['name'] }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Select source language to copy from') }}</p>
                        @error('sourceLanguage') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-4 flex gap-2">
                    <button wire:click="createNewLanguage" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('Create Language') }}
                    </button>
                    <button wire:click="toggleCreateLanguageForm" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                        {{ __('Cancel') }}
                    </button>
                </div>

                <div class="mt-4 bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>{{ __('What happens when you create a new language?') }}</strong>
                            </p>
                            <ul class="text-xs text-blue-600 mt-2 list-disc list-inside space-y-1">
                                <li>{{ __('A new JSON file will be created in resources/lang/') }}</li>
                                <li>{{ __('All translations from the source language will be copied') }}</li>
                                <li>{{ __('Backend configuration will be automatically updated') }}</li>
                                <li>{{ __('A backup of the config file will be created') }}</li>
                                <li>{{ __('The new language will appear in the dropdown') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Add New Translation Form -->
            @if($showAddForm)
            <div class="p-6 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Add New Translation') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Key') }}</label>
                        <input type="text" wire:model.defer="newKey"
                               placeholder="Enter translation key"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Value') }}</label>
                        <input type="text" wire:model.defer="newValue"
                               placeholder="Enter translation value"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button wire:click="addNewTranslation" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700">
                        {{ __('Add') }}
                    </button>
                    <button wire:click="toggleAddForm" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
            @endif

            <!-- Translations Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 35%">
                                {{ __('Key') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 50%">
                                {{ __('Value') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 15%">
                                {{ __('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($filteredTranslations as $key => $value)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900 break-words">
                                {{ $key }}
                            </td>
                            <td class="px-6 py-4">
                                @if($editingKey === $key)
                                    <textarea wire:model.defer="editingValue"
                                              rows="3"
                                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                              dir="auto"></textarea>
                                @else
                                    <div class="text-sm text-gray-900 whitespace-pre-wrap break-words" dir="auto">{{ $value }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                @if($editingKey === $key)
                                    <button wire:click="saveEdit" class="text-green-600 hover:text-green-900 mr-3" title="{{ __('Save') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                    <button wire:click="cancelEdit" class="text-gray-600 hover:text-gray-900" title="{{ __('Cancel') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                @else
                                    <button wire:click="startEdit('{{ addslashes($key) }}')" class="text-primary-600 hover:text-primary-900 mr-3" title="{{ __('Edit') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button wire:click="deleteTranslation('{{ addslashes($key) }}')"
                                            onclick="return confirm('{{ __('Are you sure you want to delete this translation?') }}')"
                                            class="text-red-600 hover:text-red-900"
                                            title="{{ __('Delete') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="mt-2 text-sm">{{ __('No translations found') }}</p>
                                @if(!empty($searchTerm))
                                <p class="text-xs text-gray-400 mt-1">{{ __('Try a different search term') }}</p>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Info Footer -->
            <div class="px-6 py-4 bg-blue-50 border-t border-blue-100">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>{{ __('Note') }}:</strong> {{ __('Translation changes are automatically backed up before saving. Cache is cleared after each update to ensure changes take effect immediately.') }}
                        </p>
                        <p class="text-xs text-blue-600 mt-1">
                            {{ __('Backups are stored in') }} <code class="bg-blue-100 px-1 rounded">resources/lang/backups/</code>
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </x-baseview>
</div>

@push('scripts')
<script>
    // Auto-scroll to edited row
    window.addEventListener('scroll-to-edit', event => {
        setTimeout(() => {
            const editingRow = document.querySelector('textarea[wire\\:model="editingValue"]');
            if (editingRow) {
                editingRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                editingRow.focus();
            }
        }, 100);
    });
</script>
@endpush
