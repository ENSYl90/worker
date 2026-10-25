<?php

namespace App\Http\Livewire;

use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ActivityLogLivewire extends BaseLivewireComponent
{
    public $model = Activity::class;

    // Filter properties
    public $filterAction = '';
    public $filterUser = '';
    public $filterIP = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $filterSubjectType = '';

    // Statistics
    public $todayTotal = 0;
    public $weeklyTotal = 0;
    public $todayLogins = 0;
    public $todayFailedLogins = 0;

    protected $queryString = [
        'filterAction',
        'filterUser',
        'filterIP',
        'filterDateFrom',
        'filterDateTo',
        'filterSubjectType',
    ];


     public function getListeners()
    {
        return $this->listeners + [
            'deleteConfirmed' => 'deleteConfirmed',
        ];
    }

    public function mount()
    {
        $this->calculateStatistics();
    }

    public function calculateStatistics()
    {
        // Today's statistics
        $today = now()->startOfDay();
        $this->todayTotal = Activity::whereDate('created_at', $today)->count();
        $this->todayLogins = Activity::where('description', 'login_success')
            ->whereDate('created_at', $today)
            ->count();
        $this->todayFailedLogins = Activity::where('description', 'login_failed')
            ->whereDate('created_at', $today)
            ->count();

        // Weekly statistics
        $weekAgo = now()->subWeek();
        $this->weeklyTotal = Activity::where('created_at', '>=', $weekAgo)->count();
    }

    public function query()
    {
        $query = Activity::query()->orderBy('created_at', 'desc');

        // Apply filters
        if ($this->filterAction) {
            $query->where('description', 'like', '%' . $this->filterAction . '%');
        }

        if ($this->filterUser) {
            $query->where(function ($q) {
                $q->whereHas('causer', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->filterUser . '%')
                        ->orWhere('email', 'like', '%' . $this->filterUser . '%');
                })
                    ->orWhere('properties->email', 'like', '%' . $this->filterUser . '%');
            });
        }

        if ($this->filterIP) {
            $query->where('properties->ip_address', 'like', '%' . $this->filterIP . '%');
        }

        if ($this->filterSubjectType) {
            $query->where('subject_type', 'like', '%' . $this->filterSubjectType . '%');
        }

        if ($this->filterDateFrom) {
            $query->whereDate('created_at', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->whereDate('created_at', '<=', $this->filterDateTo);
        }

        return $query;
    }

    public function clearFilters()
    {
        $this->filterAction = '';
        $this->filterUser = '';
        $this->filterIP = '';
        $this->filterSubjectType = '';
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->emit('refreshData');
    }

    public function clearActivityLogs()
    {
        try {
            // Get the count of logs to be deleted
            $count = $this->query()->count();

            if ($count === 0) {
                $this->alert('warning', __('No logs found'), [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
                return;
            }

            // Check if filters are applied
            $hasFilters = $this->filterAction || $this->filterUser || $this->filterIP ||
                $this->filterSubjectType || $this->filterDateFrom || $this->filterDateTo;

            $message = $hasFilters
                ? __('Are you sure you want to delete :count filtered activity logs?', ['count' => $count])
                : __('Are you sure you want to delete ALL :count activity logs? This action cannot be undone!', ['count' => $count]);

            $title = __("Clear Logs");

            $this->confirm($title, [
                'icon' => 'question',
                "title" => $title,
                "text" => $message,
                'onConfirmed' => 'deleteConfirmed',
                'confirmButtonText' => __('Yes, Delete'),
                'cancelButtonText' => __('Cancel'),
            ]);
        } catch (\Exception $e) {
            $this->alert('error', __('Error: ') . $e->getMessage());
        }
    }

    public function deleteConfirmed()
    {
        try {
            // Delete the logs based on current filters
            $count = $this->query()->delete();

            // Recalculate statistics
            $this->calculateStatistics();

            $this->alert('success', __(':count activity logs deleted successfully', ['count' => $count]), [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            $this->alert('error', __('Error deleting logs: ') . $e->getMessage());
        }
    }

    public function exportCSV()
    {
        $activities = $this->query()->get();

        $filename = 'activity_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($activities) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Date/Time',
                'Action',
                'User',
                'Subject',
                'IP Address',
                'Properties'
            ]);

            // Add data rows
            foreach ($activities as $activity) {
                $properties = $activity->properties ?? [];
                fputcsv($file, [
                    $activity->created_at->format('Y-m-d H:i:s'),
                    $activity->description,
                    $activity->causer ? $activity->causer->name : ($properties['email'] ?? 'System'),
                    $activity->subject_type ? class_basename($activity->subject_type) . ' #' . $activity->subject_id : '-',
                    $properties['ip_address'] ?? '-',
                    json_encode($properties),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getCommonActionsProperty()
    {
        return Activity::select('description')
            ->groupBy('description')
            ->orderBy('description')
            ->pluck('description')
            ->toArray();
    }

    public function getSubjectTypesProperty()
    {
        return Activity::select('subject_type')
            ->whereNotNull('subject_type')
            ->groupBy('subject_type')
            ->orderBy('subject_type')
            ->pluck('subject_type')
            ->map(function ($type) {
                return class_basename($type);
            })
            ->unique()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.activity-log', [
            'activities' => $this->query()->paginate(50),
        ]);
    }
}
