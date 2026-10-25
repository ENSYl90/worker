<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserActiveOrdersUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public int $activeOrdersCount;

    public function __construct(int $userId, int $activeOrdersCount)
    {
        $this->userId = $userId;
        $this->activeOrdersCount = $activeOrdersCount;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.active-orders.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ActiveOrdersUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'active' => $this->activeOrdersCount,
        ];
    }
}
