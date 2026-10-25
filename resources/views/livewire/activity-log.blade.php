@section('title', __('Activity Logs'))
<div>

    <x-baseview title="{{ __('Activity Logs') }}">

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl font-bold text-blue-500">{{ $todayTotal }}</span>
                    </div>
                    <div class="flex-1 w-0 ml-5">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                                {{ __('Today Total') }}
                            </dt>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl font-bold text-purple-500">{{ $weeklyTotal }}</span>
                    </div>
                    <div class="flex-1 w-0 ml-5">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                                {{ __('Weekly Total') }}
                            </dt>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl font-bold text-green-500">{{ $todayLogins }}</span>
                    </div>
                    <div class="flex-1 w-0 ml-5">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                                {{ __('Today Logins') }}
                            </dt>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl font-bold text-red-500">{{ $todayFailedLogins }}</span>
                    </div>
                    <div class="flex-1 w-0 ml-5">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                                {{ __('Today Failed Logins') }}
                            </dt>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="p-4 mb-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold">{{ __('Filters') }}</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-4">
                <div>
                    <x-label for="filterAction" title="{{ __('Action') }}" />
                    <input type="text" wire:model.debounce.500ms="filterAction" id="filterAction"
                        class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"
                        placeholder="{{ __('Search by action...') }}">
                </div>

                <div>
                    <x-label for="filterUser" title="{{ __('User') }}" />
                    <input type="text" wire:model.debounce.500ms="filterUser" id="filterUser"
                        class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"
                        placeholder="{{ __('Search by name/email...') }}">
                </div>

                <div>
                    <x-label for="filterIP" title="{{ __('IP Address') }}" />
                    <input type="text" wire:model.debounce.500ms="filterIP" id="filterIP"
                        class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"
                        placeholder="{{ __('Search by IP') }}">
                </div>

                <div>
                    <x-label for="filterSubjectType" title="{{ __('Subject Type') }}" />
                    <select wire:model="filterSubjectType" id="filterSubjectType"
                        class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <option value="">{{ __('All Types') }}</option>
                        @foreach($this->subjectTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label for="filterDateFrom" title="{{ __('Date From') }}" />
                    <input type="date" wire:model="filterDateFrom" id="filterDateFrom"
                        class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>

                <div>
                    <x-label for="filterDateTo" title="{{ __('Date To') }}" />
                    <input type="date" wire:model="filterDateTo" id="filterDateTo"
                        class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>

                <div class="flex items-end space-x-2">
                    <button wire:click="clearFilters"
                        class="flex-1 px-4 py-2 text-white bg-gray-500 rounded-lg hover:bg-gray-600">
                        {{ __('Clear Filters') }}
                    </button>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-end mt-4 space-x-3">
                <button wire:click="clearActivityLogs"
                    class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <svg class="inline-block w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    {{ __('Clear All Logs') }}
                </button>
            </div>
        </div>

        {{-- Activity Logs Table --}}
        <div class="overflow-x-auto bg-white rounded-lg shadow dark:bg-gray-800">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                            {{ __('Date/Time') }}
                        </th>
                        <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                            {{ __('Action') }}
                        </th>
                        <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                            {{ __('User') }}
                        </th>
                        <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                            {{ __('Subject') }}
                        </th>
                        <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                            {{ __('IP / Device') }}
                        </th>
                        <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                            {{ __('Details') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($activities as $activity)
                        @php
                            $properties = $activity->properties ?? [];
                            $isLoginFailed = $activity->description === 'login_failed';
                        @endphp
                        <tr class="{{ $isLoginFailed ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                            {{-- Date/Time --}}
                            <td class="px-4 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-300">
                                {{ $activity->created_at->format('Y-m-d H:i:s') }}
                                <br>
                                <span class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</span>
                            </td>

                            {{-- Action --}}
                            <td class="px-4 py-4 text-sm whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $activity->description === 'login_success' ? 'text-green-800 bg-green-100 dark:bg-green-900 dark:text-green-200' : '' }}
                                    {{ $activity->description === 'login_failed' ? 'text-red-800 bg-red-100 dark:bg-red-900 dark:text-red-200' : '' }}
                                    {{ !in_array($activity->description, ['login_success', 'login_failed']) ? 'text-blue-800 bg-blue-100 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                ">
                                    {{ str_replace('_', ' ', ucwords($activity->description, '_')) }}
                                </span>
                            </td>

                            {{-- User --}}
                            <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-300">
                                @if($activity->causer)
                                    <div class="font-medium">{{ $activity->causer->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $activity->causer->email }}</div>
                                @elseif(isset($properties['email']))
                                    <div class="text-gray-500">{{ $properties['email'] }}</div>
                                @else
                                    <span class="text-xs text-gray-400">{{ __('System') }}</span>
                                @endif
                            </td>

                            {{-- Subject --}}
                            <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-300">
                                @if($activity->subject_type)
                                    <div class="font-medium">{{ class_basename($activity->subject_type) }}</div>
                                    <div class="text-xs text-gray-500">ID: {{ $activity->subject_id }}</div>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>

                            {{-- IP / Device --}}
                            <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-300">
                                @if(isset($properties['ip_address']))
                                    <div class="font-medium">{{ $properties['ip_address'] }}</div>
                                    @if(isset($properties['device_info']))
                                        <div class="text-xs text-gray-500">
                                            {{ $properties['device_info']['platform'] ?? '-' }} |
                                            {{ $properties['device_info']['browser'] ?? '-' }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>

                            {{-- Details --}}
                            <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-300">
                                @if($isLoginFailed && isset($properties['failure_reason']))
                                    <span class="text-xs text-red-600 dark:text-red-400">
                                        {{ ucwords(str_replace('_', ' ', $properties['failure_reason'])) }}
                                    </span>
                                @elseif(count($properties) > 0)
                                    <button
                                        onclick="alert('{{ addslashes(json_encode($properties, JSON_PRETTY_PRINT)) }}')"
                                        class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                        {{ __('View Properties') }}
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-sm text-center text-gray-500">
                                {{ __('No activity logs found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="px-4 py-3">
                {{ $activities->links() }}
            </div>
        </div>

    </x-baseview>

</div>
