<div {{ isset($ignore) ? "wire:ignore" : "" }}>
    <x-label :title="$title" />
    <div class="relative inline-block w-full">
        <select
            class="block w-full px-4 py-3 border rounded appearance-none bg-grey-lighter text-grey-darker border-grey-lighter"
            name="{{ $name ?? '' }}" 
            id="{{ $id ?? $name ?? '' }}"
            {!! ($multiple ?? false) ? "multiple" : "" !!}
            {!! isset($width) ? "style='width: {$width}%;'" : '' !!}
             @if ($defer ?? true) wire:model.defer='{{ $name ?? '' }}' @else
            wire:model='{{ $name ?? '' }}' @endif >
            {{--  @foreach ($options as $option)
                @php
                    $optionId = $option->id ?? $option['id'];
                @endphp
                <option value="{{ $optionId }}" {{ $selected ?? '' == $optionId ? 'selected' : '' }}>
                    {{  Str::ucfirst($option->name ?? $option['name']) }}
                </option>
            @endforeach  --}}
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
        <div class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 pointer-events-none">
            <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
            </svg>
        </div>
    </div>

</div>