<?php

namespace App\Http\Livewire;

use App\Jobs\WsMonitorCleanupJob;
use App\Services\WsConnectionStore;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Redis;

/**
 * @property \Illuminate\Support\Collection              $allConnections
 * @property \Illuminate\Pagination\LengthAwarePaginator $connections
 * @property int                                         $totalConnections
 * @property \Illuminate\Support\Collection              $channels
 * @property array                                       $stats
 * @property array|null                                  $selectedConnection
 */
class WebsocketMonitorLivewire extends BaseLivewireComponent
{
    // Filters & sorting
    public string $search  = '';
    public string $role    = '';
    public string $sort    = 'connected_at';
    public string $dir     = 'desc';
    public $perPage = 15;

    // Selected connection for detail panel
    public ?string $selectedSocketId = null;

    // Auto-cleanup scheduler
    public int $cleanupDelay = 30;

    protected $queryString = ['search', 'role', 'sort', 'dir'];

    public function mount(): void
    {
        abort_if(
            config('queue.default') !== 'redis',
            403,
            'WebSocket Monitor requires the Redis queue driver.'
        );

        $stored = Redis::get('ws:monitor:cleanup_delay');
        if ($stored) {
            $this->cleanupDelay = (int) $stored;
        }
    }

    /**
     * All connections filtered and sorted — Livewire v2 computed property.
     * Accessed in views/other methods as $this->allConnections.
     */
    public function getAllConnectionsProperty()
    {
        $store = app(WsConnectionStore::class);
        $connections = collect($store->allConnections());

        if ($this->search) {
            $connections = $connections->filter(
                fn($c) =>
                str_contains(strtolower($c['socket_id'] ?? ''), strtolower($this->search))
                    || str_contains(strtolower($c['user_name'] ?? ''), strtolower($this->search))
                    || str_contains(strtolower($c['ip'] ?? ''), strtolower($this->search))
            );
        }

        if ($this->role) {
            $connections = $connections->filter(fn($c) => ($c['user_role'] ?? '') === $this->role);
        }

        return $this->dir === 'asc'
            ? $connections->sortBy($this->sort)
            : $connections->sortByDesc($this->sort);
    }

    /**
     * Paginated connections — Livewire v2 computed property.
     * Accessed in views as $connections.
     */
    public function getConnectionsProperty()
    {
        $all  = $this->allConnections;
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $all->forPage($page, $this->perPage)->values(),
            $all->count(),
            $this->perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
    }

    /**
     * Total count of filtered connections (for tab badge).
     */
    public function getTotalConnectionsProperty()
    {
        return $this->allConnections->count();
    }

    /**
     * All channels sorted by subscriber count.
     */
    public function getChannelsProperty()
    {
        $store = app(WsConnectionStore::class);
        return collect($store->allChannels())->sortByDesc('count');
    }

    /**
     * Live stats counters.
     */
    public function getStatsProperty()
    {
        $store = app(WsConnectionStore::class);
        return $store->getStats();
    }

    /**
     * Detail data for the selected connection.
     */
    public function getSelectedConnectionProperty()
    {
        if (!$this->selectedSocketId) {
            return null;
        }
        $store = app(WsConnectionStore::class);
        return $store->getConnection($this->selectedSocketId);
    }

    public function startAutoCleanup(): void
    {
        $this->validate(['cleanupDelay' => 'required|integer|min:1|max:1440']);

        Redis::set('ws:monitor:cleanup_running', 1);
        Redis::set('ws:monitor:cleanup_delay', $this->cleanupDelay);

        WsMonitorCleanupJob::dispatch($this->cleanupDelay)
            ->onQueue('default')
            ->delay(now()->addMinutes($this->cleanupDelay));

        $this->showSuccessAlert(__('Auto cleanup started. First run in :delay minute(s).', ['delay' => $this->cleanupDelay]));
    }

    public function stopAutoCleanup(): void
    {
        Redis::del('ws:monitor:cleanup_running');
        $this->showSuccessAlert(__('Auto cleanup stopped.'));
    }

    public function render()
    {
        return view('livewire.settings.websocket-monitor', [
            'connections'        => $this->connections,
            'totalConnections'   => $this->totalConnections,
            'channels'           => $this->channels,
            'stats'              => $this->stats,
            'selectedConnection' => $this->selectedConnection,
            // Read directly from Redis — not via computed property — so the
            // value is always fresh after stopAutoCleanup() / startAutoCleanup().
            'isCleanupRunning'   => (bool) Redis::exists('ws:monitor:cleanup_running'),
            'cleanupDelayStored' => (int) (Redis::get('ws:monitor:cleanup_delay') ?: $this->cleanupDelay),
        ]);
    }

    public function sortBy(string $column): void
    {
        if ($this->sort === $column) {
            $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->dir  = 'desc';
        }
        $this->reset('page');
    }

    public function selectConnection(?string $socketId): void
    {
        $this->selectedSocketId = $this->selectedSocketId === $socketId ? null : $socketId;
    }

    public function clearFilter(): void
    {
        $this->search = '';
        $this->role   = '';
        $this->sort   = 'connected_at';
        $this->dir    = 'desc';
        $this->reset('page');
    }

    public function clearMonitorData(): void
    {
        app(WsConnectionStore::class)->clearAll();
        $this->selectedSocketId = null;
        $this->reset('page');
        $this->showSuccessAlert(__('Monitor data cleared successfully.'));
    }

    public function updatedSearch(): void
    {
        $this->reset('page');
    }

    public function updatedRole(): void
    {
        $this->reset('page');
    }
}
