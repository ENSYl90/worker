@php
    $toggleActive = false;
    if (isset($active)) {
        $toggleActive = $active;
    } else if (isset($model)) {
        $toggleActive = $model->is_active ?? false;
    }
@endphp
@if ($toggleActive)
    {{-- <span class="px-2 py-1 text-sm font-medium text-center text-white bg-green-500 rounded-xl">{{ __('Active')
        }}</span> --}}
    <span title="{{ __('Active') }}">
        <x-table.check />
    </span>
@else
    {{-- <span class="px-2 py-1 text-sm font-medium text-center text-white bg-red-500 rounded-xl">{{ __('Inactive')
        }}</span> --}}
    <span title="{{ __('Inactive') }}">
        <x-table.close />
    </span>
@endif