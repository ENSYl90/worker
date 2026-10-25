<div>
    <hr />
    @php
        $myMenuList = [];
        foreach ($menu ?? [] as $navItem) {
            if (Route::has($navItem->route)) {
                if (empty($navItem->roles ?? '') && empty($navItem->permissions ?? '')) {
                    $myMenuList[] = $navItem;
                } else {
                    if ($navItem->permissions == null || empty($navItem->permissions)) {
                        if (auth()->user()->hasAnyRole($navItem->roles)) {
                            $myMenuList[] = $navItem;
                        }
                    } elseif ($navItem->permissions != null && !empty($navItem->permissions)) {
                        if (auth()->user()->can($navItem->permissions)) {
                            $myMenuList[] = $navItem;
                        }
                    }
                }
            }
        }
    @endphp

    @if (count($myMenuList ?? []) > 10)
        <x-menu-item title="{{ __('Installed Extensions') }}" route="marketplace">
            <x-heroicon-o-collection class="w-5 h-5" />
        </x-menu-item>
    @else
        @foreach ($myMenuList as $navItem)
            <x-menu-item title="{{ $navItem->name }}" route="{{ $navItem->route }}">
                {{ svg($navItem->icon)->class('w-5 h-5') }}
            </x-menu-item>
        @endforeach
    @endif
</div>