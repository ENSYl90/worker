@section('title', __('Order Payment'))
<div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white p-6 rounded-xl shadow-lg border border-gray-100">
        @if ($error)
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100 mb-4">
                <x-heroicon-o-x class="h-12 w-12 text-red-600" />
            </div>
            <h2 class="mt-2 text-center text-2xl font-bold text-gray-900">
                {{__('Payment Failed')}}
            </h2>
            <p class="mt-1 text-center text-sm text-gray-600">
                {{ $errorMessage }}
            </p>
        @else
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-4">
                <x-heroicon-o-check class="h-12 w-12 text-green-600" />
            </div>
            <h2 class="mt-2 text-center text-2xl font-bold text-gray-900">
                {{__('Payment Successful')}}
            </h2>
            <p class="mt-1 text-center text-sm text-gray-600">
                {{__('Order Payment was successful')}}
            </p>
        @endif

        <div class="mt-6 text-center space-y-2">
            <p class="text-sm text-gray-500">{{__('You may close this window')}}</p>
            <div class="flex justify-center">
                <x-buttons.close />
            </div>
        </div>
    </div>
</div>