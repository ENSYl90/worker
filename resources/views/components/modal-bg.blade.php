<div class="fixed inset-0 z-20 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block overflow-hidden text-left align-bottom transition-all transform rounded-lg sm:my-8 sm:align-middle " role="dialog" aria-modal="true" aria-labelledby="modal-headline">
            {{ $slot }}
        </div>
    </div>
</div>
