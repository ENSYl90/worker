<div class="block {{ empty($title ?? '') ? '' : 'mt-4' }}">
    @if (!empty($title ?? ''))
        <label class="block text-sm text-gray-700 mb-2">
            {{ $title }}
        </label>
    @endif

    <input
        {{ $attributes->merge(['class' => 'w-full px-2 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors input-focus']) }}
        placeholder="{{ $placeholder ?? '' }}" type="{{ $type ?? 'text' }}" id='{{ $name ?? '' }}'
        @if ($defer ?? true) wire:model.defer='{{ $name ?? '' }}' @else wire:model='{{ $name ?? '' }}' @endif
        @if ($disable ?? false) disabled @endif {{-- accept type --}}
        @isset($capture)
        capture
        @endisset
        @isset($acceptType)
        accept="{{ $acceptType }}"
        @endisset />
    @if (!empty($hint))
        <span class="text-xs italic text-gray-700">{{ $hint ?? '' }}</span>
    @endif
    @error($name ?? '')
        <span class="mt-1 text-xs text-red-700">{{ $message }}</span>
    @enderror
</div>
