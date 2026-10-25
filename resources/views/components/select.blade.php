<div @if ($ignore ?? false) wire:ignore @endif>
    @isset($title)
        <x-label :title="$title" />
    @endisset
    <div class="relative inline-block w-full">
        <select
            class="block w-full px-2 border rounded appearance-none bg-grey-lighter text-grey-darker border-grey-lighter"
            {{-- on change --}} wire:change="{{ $onchange ?? '' }}" name="{{ $name ?? '' }}" @if ($multiple ?? false)
            multiple="multiple" @endif id="{{ $id ?? ($name ?? '') }}" @if ($defer ?? true)
            wire:model.defer='{{ $name ?? '' }}' @else wire:model='{{ $name ?? '' }}' @endif @if ($width ?? false)
            style="width: {{ $width }}%" @endif>

            @if ($noPreSelect ?? true)
                <option value=""> {{ $hint ?? '-- Select --' }}</option>
            @endif
            {{-- @foreach ($options as $option)
            @php
            $optionId = $option->id ?? ($option['id'] ?? $option);
            @endphp
            <option value="{{ $optionId }}" {{ $selected ?? ''==$optionId ? 'selected' : '' }}>
                {{ Str::ucfirst(__($option->name ?? ($option['name'] ?? $option))) }}</option>
            @endforeach --}}
            @foreach ($options as $key => $option)
                @php
                    // Handle different option formats
                    if (is_array($option)) {
                        // Array format: ["id"=>"credit","name"=>"Credit"]
                        $optionId = $option['id'];
                        $optionName = $option['name'];
                    } elseif (is_object($option)) {
                        // Object format: $option->id, $option->name
                        $optionId = $option->id;
                        $optionName = $option->name;
                    } else {
                        // Simple key-value format: "credit" => "Credit"
                        $optionId = \Str::lower($option);
                        $optionName = $option;
                    }
                @endphp
                <option value="{{ $optionId }}" {{ ($selected ?? '') == $optionId ? 'selected' : '' }}>
                    {{ Str::ucfirst(__($optionName)) }}
                </option>
            @endforeach
        </select>
    </div>
    @error($name ?? '')
        <span class="mt-1 text-xs text-red-700">{{ $message }}</span>
    @enderror
</div>