{{-- Minimal Livewire Boundary Generator Component - Use with boundary-generator.js --}}
<div x-data="{ open: false, loading: true }" x-cloak class="boundary-generator-livewire">
    <!-- Modal Backdrop -->
    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-60 z-40 backdrop-blur-sm"
        @click="open = false">
    </div>

    <!-- Modal -->
    <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-[90%] md:w-[70%] lg:w-[50%] overflow-hidden" @click.stop>
            <!-- Header -->
            <div class="bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-4 text-white">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold">{{ __('Boundary Generator') }}</h3>
                    <button @click="open = false" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="boundary-generator-content p-8" wire:ignore>
                <div class="text-center">
                    <!-- Loading Spinner -->
                    <div x-show="loading" class="mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-100 rounded-full mb-4">
                            <svg class="w-8 h-8 text-primary-500 animate-spin" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                        </div>
                        <h4 class="text-lg font-medium text-gray-900 mb-2">{{ __('Generating Boundaries') }}</h4>
                        <p class="text-gray-600">{{ __('Please wait while we fetch boundary data...') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/boundary-generator.js') }}"></script>
@endpush
