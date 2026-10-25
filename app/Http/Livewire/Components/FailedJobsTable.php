<?php

namespace App\Http\Livewire\Components;

use Exception;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class FailedJobsTable extends Component
{
    use WithPagination, LivewireAlert;

    protected $paginationTheme = 'tailwind';

    public $showHeader = true;
    public $showBorder = true;
    public $showRetryAll = true;
    public $showDeleteAll = true;
    public $perPage = 15;

    protected function getListeners()
    {
        return [
            //
        ];
    }

    public function mount($showHeader = true, $showBorder = true, $showRetryAll = true, $showDeleteAll = true, $perPage = 15)
    {
        $this->showHeader = $showHeader;
        $this->showBorder = $showBorder;
        $this->showRetryAll = $showRetryAll;
        $this->showDeleteAll = $showDeleteAll;
        $this->perPage = $perPage;
    }

    public function retryJob($jobId)
    {
        try {
            $this->isDemo();

            // Get the failed job
            $failedJob = DB::table('failed_jobs')->where('id', $jobId)->first();

            if (!$failedJob) {
                $this->alert('error', __('Failed job not found.'), [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
                return;
            }

            // Use Artisan command to retry the job
            Artisan::call('queue:retry', ['id' => $failedJob->uuid]);

            $this->alert('success', __('Job has been added back to the queue!'), [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
            $this->resetPage();
        } catch (Exception $e) {
            $this->alert('error', __('Error retrying job: :error', ['error' => $e->getMessage()]), [
                'position' => 'top-end',
                'timer' => 5000,
                'toast' => true,
            ]);
        }
    }

    public function deleteAllFailedJobs()
    {
        try {
            $this->isDemo();
            $deletedCount = DB::table('failed_jobs')->delete();

            if ($deletedCount > 0) {
                $this->alert('success', __(':count failed jobs deleted successfully!', ['count' => $deletedCount]), [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
                $this->resetPage();
            } else {
                $this->alert('info', __('No failed jobs found to delete.'), [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
            }
        } catch (Exception $e) {
            $this->alert('error', __('Error deleting failed jobs: :error', ['error' => $e->getMessage()]), [
                'position' => 'top-end',
                'timer' => 5000,
                'toast' => true,
            ]);
        }
    }

    public function deleteJob($jobId)
    {
        try {
            $this->isDemo();
            $deleted = DB::table('failed_jobs')->where('id', $jobId)->delete();

            if ($deleted) {
                $this->alert('success', __('Failed job deleted successfully!'), [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
                $this->resetPage();
            } else {
                $this->alert('error', __('Failed job not found.'), [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
            }
        } catch (Exception $e) {
            $this->alert('error', __('Error deleting job: :error', ['error' => $e->getMessage()]), [
                'position' => 'top-end',
                'timer' => 5000,
                'toast' => true,
            ]);
        }
    }

    public function retryAllFailed()
    {
        try {
            $this->isDemo();
            Artisan::call('queue:retry', ['id' => 'all']);

            $this->alert('success', __('All failed jobs have been added back to the queue!'), [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
            $this->resetPage();
        } catch (Exception $e) {
            $this->alert('error', __('Error retrying jobs: :error', ['error' => $e->getMessage()]), [
                'position' => 'top-end',
                'timer' => 5000,
                'toast' => true,
            ]);
        }
    }

    public function render()
    {
        $failedJobs = DB::table('failed_jobs')
            ->select([
                'id',
                'uuid',
                'connection',
                'queue',
                'payload',
                'exception',
                'failed_at'
            ])
            ->orderBy('failed_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.components.failed-jobs-table', compact('failedJobs'));
    }



    //misc
    public function isDemo($catchError = false)
    {
        if (!App::environment('production')) {
            $errorMessage = __("App is in demo version. Some changes can't be made");
            if ($catchError) {
                $this->showErrorAlert($errorMessage);
            } else {
                throw new Exception($errorMessage);
            }
        }
        ;
    }
}
