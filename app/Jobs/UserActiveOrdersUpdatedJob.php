<?php

namespace App\Jobs;

use App\Events\UserActiveOrdersUpdatedEvent;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UserActiveOrdersUpdatedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const INACTIVE_ORDER_STATUSES = [
        'failed',
        'cancelled',
        'delivered',
        'completed',
        'successful',
    ];

    public int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function handle(): void
    {
        self::emitForUser($this->userId);
    }

    public static function emitForUser(int $userId): int
    {
        $activeOrdersCount = Order::where('user_id', $userId)
            ->otherCurrentStatus(self::INACTIVE_ORDER_STATUSES)
            ->count();

        event(new UserActiveOrdersUpdatedEvent(
            $userId,
            $activeOrdersCount
        ));

        return $activeOrdersCount;
    }
}
