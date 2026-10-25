<?php

namespace App\Http\Controllers;

use App\Services\WsConnectionStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WsMonitorController extends Controller
{
    public function __construct(private WsConnectionStore $store) {}

    public function stats(): JsonResponse
    {
        return response()->json($this->store->getStats());
    }

    public function connections(Request $request): JsonResponse
    {
        $connections = collect($this->store->allConnections());

        // Filters
        if ($role = $request->query('role')) {
            $connections = $connections->filter(fn($c) => ($c['user_role'] ?? '') === $role);
        }
        if ($search = $request->query('search')) {
            $connections = $connections->filter(function ($c) use ($search) {
                return str_contains((string) ($c['socket_id'] ?? ''), $search)
                    || str_contains((string) ($c['user_name'] ?? ''), $search)
                    || str_contains((string) ($c['ip'] ?? ''), $search);
            });
        }

        // Sorting
        $sort = $request->query('sort', 'connected_at');
        $dir  = $request->query('dir', 'desc');
        $connections = $dir === 'asc'
            ? $connections->sortBy($sort)
            : $connections->sortByDesc($sort);

        return response()->json($connections->values());
    }

    public function channels(): JsonResponse
    {
        $channels = collect($this->store->allChannels())
            ->sortByDesc('count')
            ->values();

        return response()->json($channels);
    }

    public function connection(string $socketId): JsonResponse
    {
        $conn = $this->store->getConnection($socketId);
        if (!$conn) {
            return response()->json(['error' => 'Not found'], 404);
        }
        return response()->json($conn);
    }

    // Called by Reverb event hooks (see WebSocketEventServiceProvider)
    public function hookConnect(Request $request): JsonResponse
    {
        $this->store->connect($request->input('socket_id'), [
            'ip'        => $request->ip(),
            'user_id'   => $request->input('user_id'),
            'user_name' => $request->input('user_name'),
            'user_role' => $request->input('user_role'),
        ]);
        return response()->json(['ok' => true]);
    }

    public function hookDisconnect(Request $request): JsonResponse
    {
        $this->store->disconnect($request->input('socket_id'));
        return response()->json(['ok' => true]);
    }

    public function hookSubscribe(Request $request): JsonResponse
    {
        $this->store->subscribe(
            $request->input('socket_id'),
            $request->input('channel')
        );
        return response()->json(['ok' => true]);
    }

    public function hookUnsubscribe(Request $request): JsonResponse
    {
        $this->store->unsubscribe(
            $request->input('socket_id'),
            $request->input('channel')
        );
        return response()->json(['ok' => true]);
    }
}
