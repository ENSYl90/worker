@section('title', __('MarketPlace'))
<div>

    <x-baseview title="{{ __('MarketPlace') }}">
        <div class="bg-white rounded p-6 shadow-sm space-y-4">
            @foreach ($groupedMenus as $group => $items)
                <div>
                    <p class="font-semibold text-lg mb-2">{{ $group ?? "" }}</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 md:gap-4">
                        @foreach ($items as $navItem)
                            @if (Route::has($navItem->route))
                                @if (empty($navItem->roles ?? '') && empty($navItem->permissions ?? ''))
                                    <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:shadow border rounded shadow-sm p-4"
                                        href="{{ route($navItem->route) ?? '#' }}" target="_blank">
                                        {{ svg($navItem->icon)->class('w-5 h-5') }}
                                        <span class="{{ isRTL() ? 'mr-4' : 'ml-4' }}">{{ $navItem->name }}</span>
                                    </a>
                                @else
                                    @if ($navItem->permissions == null || empty($navItem->permissions))
                                        @hasanyrole($navItem->roles)
                                        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:shadow border rounded shadow-sm p-4"
                                            href="{{ route($navItem->route) ?? '#' }}" target="_blank">
                                            {{ svg($navItem->icon)->class('w-5 h-5') }}
                                            <span class="{{ isRTL() ? 'mr-4' : 'ml-4' }}">{{ $navItem->name }}</span>
                                        </a>
                                        @endhasanyrole
                                    @elseif($navItem->permissions != null && !empty($navItem->permissions))
                                        @can($navItem->permissions)
                                            <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:shadow border rounded shadow-sm p-4"
                                                href="{{ route($navItem->route) ?? '#' }}" target="_blank">
                                                {{ svg($navItem->icon)->class('w-5 h-5') }}
                                                <span class="{{ isRTL() ? 'mr-4' : 'ml-4' }}">{{ $navItem->name }}</span>
                                            </a>
                                        @endcan
                                    @endif
                                @endif
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

    </x-baseview>


</div>