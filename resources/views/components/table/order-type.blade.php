<p class="flex text-xs font-light">
    @php
        $icons = [
            'parcel' => 'feathericon-package',
            'service' => 'heroicon-o-briefcase',
            'food' => 'fluentui-food-16-o',
            'pharmacy' => 'tabler-building-hospital',
            'grocery' => 'heroicon-o-shopping-cart',
            'taxi' => 'fluentui-vehicle-car-28-o',
        ];
        $icon = $icons[$model->order_type] ?? null;
    @endphp

    @if($icon)
        <x-dynamic-component :component="$icon" class="w-4 h-4" />
    @endif

    {{-- <span class="mx-1 text-xs">{{ ucfirst($model->order_type) }}</span> --}}
</p>