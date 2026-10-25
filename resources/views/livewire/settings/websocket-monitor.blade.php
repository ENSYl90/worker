@section('title', __('WebSocket Monitor'))
<div x-data="{ showConfirmClearModal: false, showDelayModal: false }" class="p-4">
    @php $refreshMs = 5000; @endphp
    <div wire:poll.{{ $refreshMs }}ms>

        <x-baseview title="{{ __('WebSocket Monitor') }}" :showLoading="false">

            <x-slot:newBtn>
                @if(inProduction())
                <x-buttons.plain title="" bgColor="bg-red-500" onClick="showConfirmClearModal = true">
                    <x-heroicon-o-trash class="w-5 h-5 mr-1" />
                    <p>{{ __('Clear Monitor Data') }}</p>
                </x-buttons.plain>
                @endif
            </x-slot:newBtn>

            {{-- ── Stats bar ── --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                @php
                    $statCards = [
                        ['label' => __('Active Connections'), 'value' => $stats['active_connections'], 'color' => 'text-green-600'],
                        ['label' => __('Total Ever'), 'value' => $stats['total_ever'], 'color' => 'text-blue-600'],
                        ['label' => __('Active Channels'), 'value' => $stats['active_channels'], 'color' => 'text-purple-600'],
                        ['label' => __('Messages In'), 'value' => $stats['messages_in'], 'color' => 'text-indigo-600'],
                        ['label' => __('Messages Out'), 'value' => $stats['messages_out'], 'color' => 'text-orange-600'],
                        ['label' => __('Auth Failures'), 'value' => $stats['auth_failures'], 'color' => 'text-red-600'],
                    ];
                @endphp
                @foreach($statCards as $card)
                    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100 text-center">
                        <p class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $card['label'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- ── Tabbed panel ── --}}
            <div x-data="{ tab: 'connections' }" class="bg-white rounded-lg shadow-sm border border-gray-100">
                {{-- Tab bar --}}
                <div class="flex border-b border-gray-100">
                    <button @click="tab = 'connections'" :class="tab === 'connections'
                            ? 'border-b-2 border-primary-500 text-primary-600 font-semibold'
                            : 'text-gray-500 hover:text-gray-700'"
                        class="px-5 py-3 text-sm transition-colors focus:outline-none flex items-center gap-2">
                        <x-heroicon-o-wifi class="w-4 h-4" />
                        {{ __('Connections') }}
                        <span class="ml-1 bg-gray-100 text-gray-600 text-xs px-1.5 py-0.5 rounded-full">
                            {{ $totalConnections }}
                        </span>
                    </button>
                    <button @click="tab = 'channels'" :class="tab === 'channels'
                            ? 'border-b-2 border-primary-500 text-primary-600 font-semibold'
                            : 'text-gray-500 hover:text-gray-700'"
                        class="px-5 py-3 text-sm transition-colors focus:outline-none flex items-center gap-2">
                        <x-heroicon-o-collection class="w-4 h-4" />
                        {{ __('Channels') }}
                        <span class="ml-1 bg-gray-100 text-gray-600 text-xs px-1.5 py-0.5 rounded-full">
                            {{ $channels->count() }}
                        </span>
                    </button>
                </div>

                {{-- ── Tab: Connections ── --}}
                <div x-show="tab === 'connections'" x-cloak>
                    <div class="flex flex-col lg:flex-row">

                        {{-- Connection table --}}
                        <div class="flex-1 min-w-0">

                            {{-- Toolbar --}}
                            <div class="flex flex-col sm:flex-row gap-3 p-4 border-b border-gray-100">
                                <input wire:model.debounce.300ms="search" type="text"
                                    placeholder="{{ __('Search socket ID, user, IP…') }}"
                                    class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500" />
                                <select wire:model="role"
                                    class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none">
                                    <option value="">{{ __('All roles') }}</option>
                                    <option value="admin">{{ __('Admin') }}</option>
                                    <option value="driver">{{ __('Driver') }}</option>
                                    <option value="vendor">{{ __('Vendor') }}</option>
                                    <option value="customer">{{ __('Customer') }}</option>
                                </select>
                                @if($search || $role)
                                    <button wire:click="clearFilter" class="text-sm text-gray-500 hover:text-red-500 px-2">
                                        {{ __('Clear') }}
                                    </button>
                                @endif
                            </div>

                            {{-- Table --}}
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                                        <tr>
                                            <th class="px-4 py-3 text-left cursor-pointer select-none"
                                                wire:click="sortBy('socket_id')">
                                                {{ __('Socket ID') }}
                                                @if($sort === 'socket_id') <span>{{ $dir === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </th>
                                            <th class="px-4 py-3 text-left cursor-pointer select-none"
                                                wire:click="sortBy('user_name')">
                                                {{ __('User') }}
                                                @if($sort === 'user_name') <span>{{ $dir === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </th>
                                            <th class="px-4 py-3 text-left hidden md:table-cell">{{ __('Role') }}</th>
                                            <th class="px-4 py-3 text-left hidden md:table-cell">{{ __('IP') }}</th>
                                            <th class="px-4 py-3 text-left hidden lg:table-cell">{{ __('Channels') }}
                                            </th>
                                            <th class="px-4 py-3 text-left cursor-pointer select-none"
                                                wire:click="sortBy('connected_at')">
                                                {{ __('Connected') }}
                                                @if($sort === 'connected_at')
                                                <span>{{ $dir === 'asc' ? '↑' : '↓' }}</span> @endif
                                            </th>
                                            <th class="px-4 py-3 text-left cursor-pointer select-none hidden lg:table-cell"
                                                wire:click="sortBy('last_seen_at')">
                                                {{ __('Last Seen') }}
                                                @if($sort === 'last_seen_at')
                                                <span>{{ $dir === 'asc' ? '↑' : '↓' }}</span> @endif
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        @forelse($connections as $conn)
                                            @php
                                                $isSelected = $selectedSocketId === $conn['socket_id'];
                                                $roleColor = match ($conn['user_role'] ?? '') {
                                                    'admin' => 'bg-red-100 text-red-700',
                                                    'driver' => 'bg-blue-100 text-blue-700',
                                                    'vendor' => 'bg-purple-100 text-purple-700',
                                                    'customer' => 'bg-green-100 text-green-700',
                                                    default => 'bg-gray-100 text-gray-600',
                                                };
                                            @endphp
                                            <tr wire:click="selectConnection('{{ $conn['socket_id'] }}')"
                                                class="cursor-pointer transition-colors {{ $isSelected ? 'bg-primary-50 border-l-2 border-primary-500' : 'hover:bg-gray-50' }}">
                                                <td
                                                    class="px-4 py-3 font-mono text-xs text-gray-600 max-w-[140px] truncate">
                                                    {{ $conn['socket_id'] ?? '—' }}
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="font-medium text-gray-800">
                                                        {{ $conn['user_name'] ?? __('Guest') }}
                                                    </div>
                                                    @if($conn['user_id'] ?? null)
                                                        <div class="text-xs text-gray-400">#{{ $conn['user_id'] }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 hidden md:table-cell">
                                                    <span
                                                        class="text-xs px-2 py-1 rounded-full font-medium {{ $roleColor }}">
                                                        {{ $conn['user_role'] ?? __('guest') }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 hidden md:table-cell text-gray-500 text-xs font-mono">
                                                    {{ $conn['ip'] ?? '—' }}
                                                </td>
                                                <td class="px-4 py-3 hidden lg:table-cell">
                                                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">
                                                        {{ count($conn['channels'] ?? []) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-xs text-gray-500">
                                                    {{ isset($conn['connected_at']) ? \Carbon\Carbon::parse($conn['connected_at'])->diffForHumans() : '—' }}
                                                </td>
                                                <td class="px-4 py-3 text-xs text-gray-500 hidden lg:table-cell">
                                                    {{ isset($conn['last_seen_at']) ? \Carbon\Carbon::parse($conn['last_seen_at'])->diffForHumans() : '—' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                                                    <div class="flex flex-col items-center gap-2">
                                                        <x-heroicon-o-wifi class="w-10 h-10 opacity-30" />
                                                        <p>{{ __('No active WebSocket connections') }}</p>
                                                        <p class="text-xs">
                                                            {{ __('Connections will appear here once clients connect via Reverb') }}
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination footer --}}
                            <div class="px-4 py-3 border-t border-gray-100 space-y-3">
                                {{ $connections->links() }}
                            </div>
                        </div>

                        {{-- ── Detail panel (slides in when a row is selected) ── --}}
                        @if($selectedConnection)
                            <div class="w-full lg:w-72 flex-shrink-0 border-t lg:border-t-0 lg:border-l border-gray-100">
                                <div class="sticky top-4 p-4 space-y-4 text-sm">

                                    <div class="flex items-center justify-between">
                                        <p class="font-semibold text-gray-700">{{ __('Connection Detail') }}</p>
                                        <button wire:click="selectConnection(null)"
                                            class="text-gray-400 hover:text-gray-600">
                                            <x-heroicon-o-x class="w-4 h-4" />
                                        </button>
                                    </div>

                                    {{-- Identity --}}
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase font-semibold mb-2">{{ __('Identity') }}
                                        </p>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-gray-500">{{ __('User') }}</span>
                                                <span
                                                    class="font-medium">{{ $selectedConnection['user_name'] ?? __('Guest') }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-500">{{ __('User ID') }}</span>
                                                <span
                                                    class="font-mono text-xs">{{ $selectedConnection['user_id'] ?? '—' }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-500">{{ __('Role') }}</span>
                                                <span>{{ $selectedConnection['user_role'] ?? __('guest') }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-500">{{ __('IP') }}</span>
                                                <span
                                                    class="font-mono text-xs">{{ $selectedConnection['ip'] ?? '—' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="border-gray-100" />

                                    {{-- Timing --}}
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase font-semibold mb-2">{{ __('Timing') }}</p>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-gray-500">{{ __('Connected') }}</span>
                                                <span class="text-xs">
                                                    {{ isset($selectedConnection['connected_at']) ? \Carbon\Carbon::parse($selectedConnection['connected_at'])->diffForHumans() : '—' }}
                                                </span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-500">{{ __('Last Seen') }}</span>
                                                <span class="text-xs">
                                                    {{ isset($selectedConnection['last_seen_at']) ? \Carbon\Carbon::parse($selectedConnection['last_seen_at'])->diffForHumans() : '—' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="border-gray-100" />

                                    {{-- Socket ID --}}
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase font-semibold mb-2">{{ __('Socket ID') }}
                                        </p>
                                        <p class="font-mono text-xs bg-gray-50 rounded px-2 py-1 break-all text-gray-600">
                                            {{ $selectedConnection['socket_id'] }}
                                        </p>
                                    </div>

                                    <hr class="border-gray-100" />

                                    {{-- Subscribed channels --}}
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase font-semibold mb-2">
                                            {{ __('Channels') }} ({{ count($selectedConnection['channels'] ?? []) }})
                                        </p>
                                        @if(!empty($selectedConnection['channels']))
                                            <div class="space-y-1 max-h-48 overflow-y-auto">
                                                @foreach($selectedConnection['channels'] as $ch)
                                                    <div class="font-mono text-xs bg-gray-50 rounded px-2 py-1 text-gray-600 truncate"
                                                        title="{{ $ch }}">
                                                        {{ $ch }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-xs text-gray-400">{{ __('No channel subscriptions') }}</p>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- ── Tab: Channels ── --}}
                <div x-show="tab === 'channels'" x-cloak>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                                <tr>
                                    <th class="px-4 py-3 text-left">{{ __('Channel') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Type') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Subscribers') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($channels as $ch)
                                    @php
                                        $typeColor = match ($ch['type']) {
                                            'private' => 'bg-yellow-100 text-yellow-700',
                                            'presence' => 'bg-purple-100 text-purple-700',
                                            default => 'bg-green-100 text-green-700',
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-mono text-xs text-gray-700 max-w-xs truncate">
                                            {{ $ch['channel'] }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-xs px-2 py-1 rounded-full font-medium {{ $typeColor }}">
                                                {{ $ch['type'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="w-20 bg-gray-100 rounded-full h-1.5">
                                                    <div class="bg-primary-500 h-1.5 rounded-full"
                                                        style="width: {{ min(100, ($ch['count'] / max(1, $stats['active_connections'])) * 100) }}%">
                                                    </div>
                                                </div>
                                                <span class="text-xs text-gray-600">{{ $ch['count'] }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-10 text-center text-gray-400">
                                            <div class="flex flex-col items-center gap-2">
                                                <x-heroicon-o-collection class="w-10 h-10 opacity-30" />
                                                <p>{{ __('No active channels') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-2 border-t border-gray-100 text-xs text-gray-400">
                        {{ $channels->count() }} {{ __('channel(s) active') }}
                    </div>
                </div>

            </div>

            {{-- ── Auto Cleanup Scheduler ── --}}
            <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-100">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-refresh class="w-5 h-5 text-gray-400" />
                        <div>
                            <p class="text-sm font-semibold text-gray-700">{{ __('Auto Cleanup') }}</p>
                            <p class="text-xs text-gray-400">
                                {{ __('Removes stale connection records automatically on a recurring schedule.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @if($isCleanupRunning)
                            <span
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700 bg-green-100 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                {{ __('Running') }} &middot; {{ __('every') }} {{ $cleanupDelayStored }} {{ __('min') }}
                            </span>
                            @if(inProduction())
                            <button id="stopJobButton" wire:click="stopAutoCleanup" wire:loading.attr="disabled"
                                class="px-4 py-2 text-sm rounded border border-red-300 text-red-600 hover:bg-red-50 font-medium transition-colors">
                                {{ __('Stop') }}
                            </button>
                            @endif
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                {{ __('Stopped') }}
                            </span>
                            @if(inProduction())
                            <button id="startjobCleaner" @click="showDelayModal = true"
                                class="px-4 py-2 text-sm rounded bg-primary-500 text-white hover:bg-primary-600 font-medium transition-colors">{{ __('Start Auto Cleanup') }}</button>
                            @endif
                        @endif
                    </div>
                </div>


            </div>

        </x-baseview>
    </div>

    {{-- ── Confirm clear modal ── --}}
    <div x-show="showConfirmClearModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" wire:ignore>
        <x-modal-bg>
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm mx-4"
                @click.outside="showConfirmClearModal = false">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex-shrink-0 bg-red-100 rounded-full p-2">
                        <x-heroicon-o-exclamation class="w-6 h-6 text-red-600" />
                    </div>
                    <h3 class="font-semibold text-gray-800">{{ __('Clear Monitor Data') }}</h3>
                </div>
                <p class="text-sm text-gray-600 mb-6">
                    {{ __('This will remove all connection records, channel data, and counters from the monitor store.') }} 
                    {{ __("Active WebSocket connections are not affected — they will re-register on their next message.") }}
                </p>
                <div class="flex gap-3 justify-end">
                    <button @click="showConfirmClearModal = false"
                        class="px-4 py-2 text-sm rounded border border-gray-300 text-gray-600 hover:bg-gray-50">{{ __('Cancel') }}</button>
                    <button wire:click="clearMonitorData" @click="showConfirmClearModal = false"
                        class="px-4 py-2 text-sm rounded bg-red-500 text-white hover:bg-red-600 font-medium">{{ __('Yes, Clear') }}</button>
                </div>
            </div>
        </x-modal-bg>
    </div>
    {{-- Delay config modal --}}
    <div x-show="showDelayModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" wire:ignore>
        <x-modal-bg>
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm mx-4" @click.outside="showDelayModal = false">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex-shrink-0 bg-blue-100 rounded-full p-2">
                        <x-heroicon-o-clock class="w-5 h-5 text-blue-600" />
                    </div>
                    <h3 class="font-semibold text-gray-800">{{ __('Configure Auto Cleanup') }}</h3>
                </div>

                <p class="text-sm text-gray-500 mb-4">
                    {{ __('Set how often the cleanup job should run.') }}
                    {{ __("Stale connections (inactive for 5+ minutes) will be removed each time.") }}
                </p>

                <div class="mb-5">
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        {{ __('Delay (minutes)') }}
                    </label>
                    <input wire:model="cleanupDelay" type="number" min="1" max="1440"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500" />
                    @error('cleanupDelay')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">{{ __('Between 1 and 1440 minutes (24 hours).') }}</p>
                </div>

                <div class="flex gap-3 justify-end">
                    <button @click="showDelayModal = false"
                        class="px-4 py-2 text-sm rounded border border-gray-300 text-gray-600 hover:bg-gray-50">{{ __('Cancel') }}</button>
                    @if(inProduction())
                    <button wire:click="startAutoCleanup" @click="showDelayModal = false"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm rounded bg-primary-500 text-white hover:bg-primary-600 font-medium">{{ __('Start') }}</button>
                    @endif
                </div>
            </div>
        </x-modal-bg>
    </div>

</div>