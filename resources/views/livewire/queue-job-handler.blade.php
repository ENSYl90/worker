@section('title', __('Jobs Monitor'))
<div>

    <x-baseview title="{{ __('Jobs Monitor') }}">

        <x-tab.tabview class="shadow pb-10">

            <x-slot name="header">
                <x-tab.header tab="1" title="{{ __('Jobs') }}">
                    <x-tabler-align-justified class="w-4 h-4 mx-2" />
                </x-tab.header>
                <x-tab.header tab="2" title="{{ __('Failed Jobs') }}">
                    <x-tabler-file-x class="w-4 h-4 mx-2" />
                </x-tab.header>
            </x-slot>

            <x-slot name="body">
                <x-tab.body tab="1">
                    @livewire('components.queue-jobs-table', [
                        'showBorder' => false,
                    ])
                </x-tab.body>
                <x-tab.body tab="2">
                    @livewire('components.failed-jobs-table', [
                        'showBorder' => false,
                    ])
                </x-tab.body>

            </x-slot>

        </x-tab.tabview>

    </x-baseview>


</div>
