<div class="block {{ empty($title ?? '') ? '' : 'mt-4' }}">
    @if (!empty($title ?? ''))
        <label class="block text-sm text-gray-700 mb-2">
            {{ $title }}
        </label>
    @endif
    <div x-data="{ showPassword: false }" class="relative">
        <input
            {{ $attributes->merge(['class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors input-focus']) }}
            placeholder="{{ $placeholder ?? '********' }}" :type="showPassword ? 'text' : 'password'"
            id='{{ $name ?? '' }}'
            @if ($defer ?? true) wire:model.defer='{{ $name ?? '' }}' @else wire:model='{{ $name ?? '' }}' @endif
            @if ($disable ?? false) disabled @endif />

        <button type="button" @click="showPassword = !showPassword"
            class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <x-tabler-eye class="h-5 w-5 text-gray-400 hover:text-gray-600" x-show="showPassword" />
            <x-tabler-eye-off class="h-5 w-5 text-gray-400 hover:text-gray-600" x-show="!showPassword" />
        </button>
    </div>
    @if (!empty($hint))
        <span class="text-xs italic text-gray-700">{{ $hint ?? '' }}</span>
    @endif
    @error($name ?? '')
        <span class="mt-1 text-xs text-red-700">{{ $message }}</span>
    @enderror

    @isset($type)

        @if ($type != null && $type == 'password')
            @push('scripts')
                <script>
                    function togglePassword() {
                        const input = document.getElementById("{{ $name }}");
                        input.type = input.type === "password" ? "text" : "password";
                    }
                </script>
            @endpush
        @endif
    @endisset
</div>
