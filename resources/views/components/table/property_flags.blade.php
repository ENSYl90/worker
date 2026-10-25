@if (!empty($model))
    <div class="flex flex-col space-y-2 p-1">
        @if ($model->is_instant_booking)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xxs font-bold bg-blue-100 text-blue-700 w-max border border-blue-200">
                <svg class="w-2.5 h-2.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                </svg>
                {{ __('Instant') }}
            </span>
        @endif
        @if ($model->featured)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xxs font-bold bg-amber-100 text-amber-700 w-max border border-amber-200">
                <svg class="w-2.5 h-2.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                {{ __('Featured') }}
            </span>
        @endif
    </div>
@endif
