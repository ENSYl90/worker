<div class="@if ($showBorder) border border-gray-200 rounded-lg @endif" x-data="failedJobsTable()">
    @if ($showHeader)
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 bg-white @if ($showBorder) rounded-t-lg @endif">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Failed Jobs') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Jobs that have failed and need attention') }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        {{ $failedJobs->total() }} {{ trans_choice('job|jobs', $failedJobs->total()) }}
                        {{ __('found') }}
                    </div>
                    @if ($showRetryAll && $failedJobs->count() > 0)
                        <button x-on:click="confirmRetryAll()"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            {{ __('Retry All') }}
                        </button>
                    @endif
                    @if ($showDeleteAll && $failedJobs->count() > 0)
                        <button x-on:click="confirmDeleteAllFailed()"
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
        @if ($failedJobs->count() > 0)
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
                                {{ __('Error') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Failed At') }}
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">{{ __('Actions') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($failedJobs as $job)
                            @php
                                $payload = json_decode($job->payload, true);
                                $jobClass = $payload['displayName'] ?? 'Unknown Job';
                                // Extract just the class name without namespace
                                $shortJobClass = last(explode('\\', $jobClass));

                                $exception = $job->exception;
                                // Extract just the error message from the exception
                                $errorMessage = __('Unknown error');
                                if ($exception) {
                                    $lines = explode("\n", $exception);
                                    $errorMessage = trim($lines[0] ?? __('Unknown error'));
                                    if (strlen($errorMessage) > 80) {
                                        $errorMessage = substr($errorMessage, 0, 77) . '...';
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ $job->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        {{ $job->queue ?? 'default' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" title="{{ $jobClass }}">
                                    {{ $shortJobClass }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                                    <div class="truncate" title="{{ $exception }}">
                                        {{ $errorMessage }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($job->failed_at)->format('M d, H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex space-x-2 justify-end">
                                        <button x-on:click="confirmRetry('{{ $job->id }}')"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                                            title="{{ __('Retry Job') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                </path>
                                            </svg>
                                        </button>
                                        <button x-on:click="confirmDelete('{{ $job->id }}')"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                                            title="{{ __('Delete Job') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($failedJobs->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $failedJobs->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12 bg-white">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No failed jobs') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Great! There are no failed jobs in the system.') }}</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
    <script>
        function failedJobsTable() {
            return {
                confirmRetry(jobId) {
                    Swal.fire({
                        title: "{{ __('Retry this job?') }}",
                        text: "{{ __('This job will be added back to the queue for processing.') }}",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#d33',
                        confirmButtonText: "{{ __('Yes, retry it!') }}",
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.retryJob(jobId);
                        }
                    })
                },
                confirmDelete(jobId) {
                    Swal.fire({
                        title: "{{ __('Are you sure?') }}",
                        text: "{{ __('This failed job will be permanently deleted and cannot be recovered.') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#d33',
                        confirmButtonText: "{{ __('Yes, delete it!') }}",
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.deleteJob(jobId);
                        }
                    })
                },
                confirmRetryAll() {
                    Swal.fire({
                        title: "{{ __('Retry all failed jobs?') }}",
                        text: "{{ __('All failed jobs will be added back to the queue for processing.') }}",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#d33',
                        confirmButtonText: "{{ __('Yes, retry all!') }}",
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.retryAllFailed();
                        }
                    })
                },
                confirmDeleteAllFailed() {
                    Swal.fire({
                        title: "{{ __('Delete all failed jobs?') }}",
                        text: "{{ __('All failed jobs in the queue will be permanently deleted and cannot be recovered.') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#d33',
                        confirmButtonText: "{{ __('Yes, delete all!') }}",
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.deleteAllFailedJobs();
                        }
                    })
                }
            }
        }
    </script>
@endpush