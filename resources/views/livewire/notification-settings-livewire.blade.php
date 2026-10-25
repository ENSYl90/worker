@section('title', __('Notification Settings'))
<div>
    <x-baseview title="{{ __('Event Notification Settings') }}">
        <div class="px-6 py-4">
            <div class="mb-6">
                <p class="text-sm text-gray-600">{{ __('Configure which model actions should trigger real-time notifications') }}</p>
            </div>

            <!-- Sound Settings -->
            <div class="p-6 mb-6 bg-white rounded-lg shadow">
                <h2 class="mb-4 text-lg font-bold text-gray-800">{{ __('Notification Sound Settings') }}</h2>

                <div class="space-y-6">
                    <!-- Enable Sound Toggle -->
                    <div class="flex items-center justify-between pb-4 border-b">
                        <div>
                            <label class="font-medium text-gray-700">{{ __('Enable Notification Sound') }}</label>
                            <p class="text-sm text-gray-500">{{ __('Play a sound when notifications are received') }}</p>
                        </div>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" wire:model="soundEnabled">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-black dark:peer-focus:ring-black rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-black"></div>
                        </label>
                    </div>

                    <!-- Sound Selection -->
                    <div class="@if(!$soundEnabled) opacity-50 pointer-events-none @endif">
                        <label class="block mb-2 font-medium text-gray-700">{{ __('Sound') }}</label>
                        <div class="flex gap-2">
                            <select wire:model="soundFile" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                @foreach($availableSounds as $file => $name)
                                    <option value="{{ $file }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @if(strpos($soundFile, 'custom-') === 0)
                                <button type="button"
                                    wire:click="deleteCustomSound('{{ $soundFile }}')"
                                    wire:confirm="Are you sure you want to delete this custom sound?"
                                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300"
                                    title="{{ __('Delete custom sound') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Upload Custom Sound -->
                    <div class="@if(!$soundEnabled) opacity-50 pointer-events-none @endif">
                        <label class="block mb-2 font-medium text-gray-700">{{ __('Upload Custom Sound') }}</label>
                        @production
                        <div class="flex items-center gap-3">
                            <input type="file"
                                wire:model="customSoundUpload"
                                accept=".mp3,.wav,.ogg"
                                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                            <button type="button"
                                wire:click="uploadCustomSound"
                                wire:loading.attr="disabled"
                                wire:target="customSoundUpload"
                                class="px-4 py-2 text-sm font-medium text-white rounded-lg whitespace-nowrap bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 disabled:opacity-50">
                                <span wire:loading.remove wire:target="customSoundUpload,uploadCustomSound">{{ __('Upload') }}</span>
                                <span wire:loading wire:target="customSoundUpload,uploadCustomSound">{{ __('Uploading...') }}</span>
                            </button>
                        </div>
                            @else
                            <div class="p-4 text-center">
                                {{ __("Only allowed in Production") }}
                            </div>
                        @endproduction
                        <p class="mt-1 text-xs text-gray-500">{{ __('Supported formats: MP3, WAV, OGG (Max 1MB)') }}</p>
                        @error('customSoundUpload')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Volume Slider -->
                    <div class="@if(!$soundEnabled) opacity-50 pointer-events-none @endif">
                        <label class="block mb-2 font-medium text-gray-700">
                            {{ __('Volume') }}: <span class="text-primary-600">{{ $soundVolume }}%</span>
                        </label>
                        <input type="range" wire:model="soundVolume" min="0" max="100" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-primary-500">
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-4">
                        <button type="button" wire:click="testSound" class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 @if(!$soundEnabled || $soundFile === 'none') opacity-50 cursor-not-allowed @endif" @if(!$soundEnabled || $soundFile === 'none') disabled @endif>
                            {{ __('Test Sound') }}
                        </button>
                        <button type="button" wire:click="saveSoundSettings" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:ring-primary-300">
                            {{ __('Save Sound Settings') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Model Notification Settings -->
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                {{ __('Model') }}
                            </th>
                            @foreach($actions as $action)
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">
                                {{ __(ucfirst($action)) }}
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($models as $modelName => $modelClass)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                {{ $modelName }}
                            </td>
                            @foreach($actions as $action)
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox"
                                        class="sr-only peer"
                                        wire:click="toggleSetting('{{ $modelName }}', '{{ $action }}')"
                                        @if($settings[$modelName . '_' . $action]) checked @endif>
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-black rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                                </label>
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 mt-6 border rounded-lg bg-primary-50 border-primary-200">
                <h3 class="mb-2 text-sm font-bold text-primary-900">{{ __('Information') }}</h3>
                <ul class="space-y-1 text-sm list-disc list-inside text-primary-800">
                    <li>{{ __('Toggle switches to enable or disable notifications for specific model actions') }}</li>
                    <li>{{ __('Created: Triggered when a new record is created') }}</li>
                    <li>{{ __('Updated: Triggered when a record is modified') }}</li>
                    <li>{{ __('Deleted: Triggered when a record is deleted') }}</li>
                    <li>{{ __('Notifications will be broadcast via WebSocket to connected users') }}</li>
                </ul>
            </div>
        </div>
    </x-baseview>

    <x-loading />

    @push('scripts')
    <script>
        // Listen for test sound event
        window.livewire.on('playTestSound', (data) => {
            if (data.file && data.file !== 'none') {
                const audio = new Audio('/sounds/' + data.file);
                audio.volume = data.volume / 100;
                audio.play().catch(err => {
                    console.log('Could not play sound:', err);
                });
            }
        });
    </script>
    @endpush
</div>
