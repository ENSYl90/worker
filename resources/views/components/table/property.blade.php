@if (!empty($model))
    <div class="flex items-center space-x-4 p-1">
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-gray-900 truncate mb-0.5">
                {{ $model->name }}
            </p>
            <div class="flex items-center text-xs text-gray-500 space-x-1">
                <span
                    class="bg-primary-100 text-primary-700 px-1.5 py-0.5 rounded text-xxs font-semibold uppercase tracking-wider">
                    {{ $model->propertyType->name ?? 'Property' }}
                </span>
                <span>•</span>
                <span class="truncate">{{ $model->city ?? 'N/A' }}</span>
            </div>
        </div>
    </div>
@endif