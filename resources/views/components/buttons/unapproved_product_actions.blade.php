<div class="flex flex-wrap items-center space-x-2 gap-y-2">
    <x-buttons.show :model="$model" />
    {{-- report --}}
    <a href="{{ route('product.details', ['id' => $model->id]) }}" target="_blank">
        <x-buttons.plain bgColor="bg-primary-500" title="{{ __('View product report') }}">
            <x-lineawesome-chart-pie-solid class="w-5 h-5" />
        </x-buttons.plain>
    </a>

    <div class="border rounded-lg px-2 py-1 bg-gray-200">
        <p style='font-size:12px; font-weight:thin; color:grey;'>{{ __("Awaiting Approval") }}</p>
    </div>
</div>