<div @if ($ignore ?? true) wire:ignore @endif>
    @isset($title)
        <x-label :title="$title" />
    @endisset
    <div class="relative inline-block w-full">
        <select
            class="choices-select block w-full px-2 border rounded appearance-none bg-grey-lighter text-grey-darker border-grey-lighter"
            {{-- on change --}}
            wire:change="{{ $onchange ?? '' }}"
            name="{{ $name ?? '' }}"
            @if ($multiple ?? false) multiple="multiple" @endif
            id="{{ $id ?? ($name ?? '') }}"
            @if ($defer ?? true) wire:model.defer='{{ $name ?? '' }}' @else wire:model='{{ $name ?? '' }}' @endif
            @if ($width ?? false) style="width: {{ $width }}%" @endif
            data-choices="true"
            @if ($search ?? true) data-search="true" @else data-search="false" @endif
            @if ($removeItemButton ?? false) data-remove-item-button="true" @endif
            @if ($placeholder ?? false) data-placeholder="{{ $placeholder }}" @endif>

            @if ($noPreSelect ?? true)
                <option value=""> {{ $hint ?? '-- Select --' }}</option>
            @endif
            @foreach ($options as $option)
                @php
                    $optionId = $option->id ?? ($option['id'] ?? $option);
                @endphp
                <option value="{{ $optionId }}" {{ $selected ?? '' == $optionId ? 'selected' : '' }}>
                    {{ Str::ucfirst(__($option->name ?? ($option['name'] ?? $option))) }}</option>
            @endforeach
        </select>
    </div>
    @error($name ?? '')
        <span class="mt-1 text-xs text-red-700">{{ $message }}</span>
    @enderror
</div>
