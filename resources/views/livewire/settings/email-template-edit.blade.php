@section('title', __('Edit Email Template'))
<div>
    <x-baseview title="{{ $editingLabel ?? __('Edit Email Template') }}">
        @if ($notFound)
            <p class="text-sm text-red-600">{{ __('Email template not found.') }}</p>
        @else
            <div class="flex flex-col gap-6 lg:flex-row">
                {{-- editor --}}
                <form wire:submit.prevent="save" class="w-full space-y-2 lg:w-1/2">
                    <x-input name="subject" title="{{ __('Subject') }}"
                        placeholder="{{ __('Leave blank to use the default subject') }}" />
                    <x-input.summernote name="body" title="{{ __('Body') }}" id="emailTemplateBody" />
                    <x-input-error message="{{ $errors->first('body') }}" />
                    <p class="text-xs italic text-gray-500">
                        {{ __('Available placeholders') }}:
                        @if (!empty($editingPlaceholders))
                            <span class="font-mono">{{ implode(', ', $editingPlaceholders) }}</span>
                        @endif
                        <br>
                        {{ __('Leave the body empty to use the default full email design instead of a custom one.') }}
                    </p>

                    <div class="flex flex-wrap items-center pt-4 gap-3">
                        <button type="submit"
                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            {{ __('Save') }}
                        </button>
                        <button type="button" wire:click="resetTemplate"
                            onclick="return confirm('{{ __('Revert this email to its default content?') }}')"
                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-red-600 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                            {{ __('Reset to default') }}
                        </button>
                        <button type="button" wire:click="refreshPreview"
                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 lg:hidden">
                            {{ __('Refresh preview') }}
                        </button>
                        <a href="{{ route('email.template.preview', ['type' => $editingKey]) }}" target="_blank"
                            class="text-sm text-blue-600 underline">
                            {{ __('Open preview in new tab') }}
                        </a>
                    </div>
                </form>

                {{-- live preview: hidden on small screens --}}
                <div class="hidden w-1/2 lg:block">
                    <div class="sticky top-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-semibold text-gray-500 uppercase">{{ __('Live Preview') }}</p>
                            <button type="button" wire:click="refreshPreview"
                                wire:loading.attr="disabled"
                                class="text-xs text-primary-600 underline disabled:opacity-50">
                                {{ __('Refresh preview') }}
                            </button>
                        </div>
                        <div class="overflow-hidden bg-white border border-gray-200 rounded-lg" style="height: 75vh;">
                            <iframe wire:key="preview-frame" srcdoc="{{ $previewHtml }}"
                                class="w-full h-full" style="border: 0;"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-baseview>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:load', function () {
            Livewire.on('confirmMissingOtpPlaceholder', function (message) {
                if (confirm(message)) {
                    @this.save(true);
                }
            });

            @if (!$notFound)
                (function waitForEditorThenLoad(attemptsLeft) {
                    if (window.emailTemplateBody) {
                        Livewire.emit('loadSummerNote', 'emailTemplateBody', @json($body ?? ''));
                    } else if (attemptsLeft > 0) {
                        setTimeout(function () {
                            waitForEditorThenLoad(attemptsLeft - 1);
                        }, 150);
                    }
                })(20);
            @endif
        });
    </script>
@endpush
