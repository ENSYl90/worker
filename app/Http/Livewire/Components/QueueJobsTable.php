<?php

namespace App\Http\Livewire\Components;

use Exception;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class QueueJobsTable extends Component
{
    use WithPagination, LivewireAlert;

    protected $paginationTheme = 'tailwind';

    public $showHeader = true;
    public $showBorder = true;
    public $showDeleteAll = true;
    public $perPage = 15;

    protected function getListeners()
    {
        return [
            //
        ];
    }

    public function mount($showHeader = true, $showBorder = true, $showDeleteAll = true, $perPage = 15)
    {
        $this->showHeader = $showHeader;
        $this->showBorder = $showBorder;
        $this->showDeleteAll = $showDeleteAll;
        $this->perPage = $perPage;
    }

    public function deleteAllJobs()
    {
        try {
            $this->isDemo();

            if (config('queue.default') == 'redis') {
                $deletedCount = $this->deleteAllRedisJobs();
            } else {
                $deletedCount = DB::table('jobs')->delete();
            }

            if ($deletedCount > 0) {
                $this->alert('success', __(':count jobs deleted successfully!', ['count' => $deletedCount]), [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
                $this->resetPage();
            } else {
                $this->alert('info', __('No jobs found to delete.'), [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
            }
        } catch (Exception $e) {
            $this->alert('error', __('Error deleting jobs: :error', ['error' => $e->getMessage()]), [
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

            if (config('queue.default') == 'redis') {
                $deleted = $this->deleteRedisJob($jobId);
            } else {
                $deleted = DB::table('jobs')->where('id', $jobId)->delete();
            }

            if ($deleted) {
                $this->alert('success', __('Job deleted successfully!'), [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
                $this->resetPage();
            } else {
                $this->alert('error', __('Job not found or already processed.'), [
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

    public function render()
    {
        if (config('queue.default') == 'redis') {
            $jobs = $this->getRedisJobs();
        } else {
            $jobs = DB::table('jobs')
                ->select([
                    'id',
                    'queue',
                    'payload',
                    'attempts',
                    'reserved_at',
                    'available_at',
                    'created_at'
                ])
                ->orderBy('created_at', 'desc')
                ->paginate($this->perPage);
        }

        return view('livewire.components.queue-jobs-table', compact('jobs'));
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

    public function getRedisJobs()
    {
        $perPage = $this->perPage;
        $page = Paginator::resolveCurrentPage() ?: 1;
        $queue = config('queue.connections.redis.queue', 'default');
        $connection = config('queue.connections.redis.connection', 'default');
        $queueKey = 'queues:' . $queue;

        try {
            $redis = Redis::connection($connection);
            $total = $redis->llen($queueKey);

            $start = ($page - 1) * $perPage;
            $end = $start + $perPage - 1;

            $rawJobs = $redis->lrange($queueKey, $start, $end);

            $mappedJobs = collect($rawJobs)->map(function ($rawJob) use ($queue) {
                $decoded = json_decode($rawJob, true);

                return (object) [
                    'id' => $decoded['uuid'] ?? $decoded['id'] ?? null,
                    'queue' => $queue,
                    'payload' => $rawJob,
                    'attempts' => $decoded['attempts'] ?? 0,
                    'reserved_at' => null,
                    'available_at' => $decoded['pushedAt'] ?? null,
                    'created_at' => $decoded['pushedAt'] ?? null,
                ];
            });

            return new LengthAwarePaginator(
                $mappedJobs,
                $total,
                $perPage,
                $page,
                ['path' => Paginator::resolveCurrentPath()]
            );
        } catch (Exception $e) {
            return new LengthAwarePaginator([], 0, $perPage, $page);
        }
    }

    public function deleteAllRedisJobs()
    {
        $queue = config('queue.connections.redis.queue', 'default');
        $connection = config('queue.connections.redis.connection', 'default');
        $queueKey = 'queues:' . $queue;
        $redis = Redis::connection($connection);

        $deletedCount = $redis->llen($queueKey);
        $redis->del($queueKey);
        $redis->del($queueKey . ':delayed');
        $redis->del($queueKey . ':reserved');

        return $deletedCount;
    }

    public function deleteRedisJob($jobId)
    {
        $queue = config('queue.connections.redis.queue', 'default');
        $connection = config('queue.connections.redis.connection', 'default');
        $queueKey = 'queues:' . $queue;
        $redis = Redis::connection($connection);

        $allJobs = $redis->lrange($queueKey, 0, -1);

        foreach ($allJobs as $rawJob) {
            $decoded = json_decode($rawJob, true);
            if (($decoded['uuid'] ?? $decoded['id'] ?? '') == $jobId) {
                return $redis->lrem($queueKey, 1, $rawJob) > 0;
            }
        }

        return false;
    }
}