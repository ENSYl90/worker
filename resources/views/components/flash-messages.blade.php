<div class="fixed top-0 right-0 z-50 flex flex-col gap-4 p-4 pointer-events-none sm:p-6 sm:items-end w-full sm:w-auto sm:max-w-md lg:max-w-lg">
    @if(session()->has('success'))
        <x-flash-message 
            type="success" 
            :title="session('success_title')"
            :message="session('success')" />
    @endif

    @if(session()->has('error'))
        <x-flash-message 
            type="error" 
            :title="session('error_title')"
            :message="session('error')" />
    @endif

    @if(session()->has('warning'))
        <x-flash-message 
            type="warning" 
            :title="session('warning_title')"
            :message="session('warning')" />
    @endif

    @if(session()->has('info'))
        <x-flash-message 
            type="info" 
            :title="session('info_title')"
            :message="session('info')" />
    @endif

    @if($errors->any())
        @foreach($errors->all() as $error)
            <x-flash-message type="error" :message="$error" />
        @endforeach
    @endif
</div>