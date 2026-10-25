<div class="@if ($showBorder) border border-gray-200 rounded-lg @endif" x-data="queueJobsTable()">
    @if ($showHeader)
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 bg-white @if ($showBorder) rounded-t-lg @endif">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Queue Jobs') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Pending jobs in the queue') }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        {{ $jobs->total() }} {{ trans_choice('job|jobs', $jobs->total()) }} {{ __('found') }}
                    </div>
                    @if ($showDeleteAll && $jobs->count() > 0)
                        <button x-on:click="confirmDeleteAll()"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                            {{ __('Delete All') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Table -->
    <div class="overflow-hidden @if (!$showHeader && $showBorder) rounded-lg @elseif($showBorder) rounded-b-lg @endif">
        @if ($jobs->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('ID') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Queue') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Job Class') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Attempts') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Available At') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Created At') }}
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">{{ __('Actions') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($jobs as $job)
                            @php
                                $payload = json_decode($job->payload, true);
                                $jobClass = $payload['displayName'] ?? 'Unknown Job';
                                // Extract just the class name without namespace
                                $shortJobClass = last(explode('\\', $jobClass));
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ $job->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $job->queue ?? 'default' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" title="{{ $jobClass }}">
                                    {{ $shortJobClass }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                        {{ $job->attempts === 0 ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $job->attempts }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $job->available_at ? \Carbon\Carbon::createFromTimestamp($job->available_at)->format('M d, H:i') : __('Now') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::createFromTimestamp($job->created_at)->format('M d, H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button x-on:click="confirmDelete('{{ $job->id }}')"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                                        title="{{ __('Delete Job') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($jobs->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $jobs->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12 bg-white">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                    </path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No jobs found') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('There are no pending jobs in the queue.') }}</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
    <script>
        function queueJobsTable() {
            return {
                confirmDelete(jobId) {
                    Swal.fire({
                        title: "{{ __('Are you sure?') }}",
                        text: "{{ __('This job will be permanently deleted and cannot be recovered.') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: "{{ __('Yes, delete it!') }}",
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.deleteJob(jobId);
                        }
                    })
                },
                confirmDeleteAll() {
                    Swal.fire({
                        title: "{{ __('Delete all jobs?') }}",
                        text: "{{ __('All jobs in the queue will be permanently deleted and cannot be recovered.') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: "{{ __('Yes, delete all!') }}",
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.deleteAllJobs();
                        }
                    })
                }
            }
        }
    </script>
@endpush