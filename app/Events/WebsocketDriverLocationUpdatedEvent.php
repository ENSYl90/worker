<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebsocketDriverLocationUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Location Data
     * @var array
     */
    public $locationData;

    /**
     * Driver's account ID
     * @var int
     */
    public $driverId;


    /**
     * Create a new event instance.
     *
     * @param $driverId
     */
    public function __construct($driverId, array $locationData)
    {
        $this->driverId = $driverId;
        $this->locationData = $locationData;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("drivers." . $this->driverId . ".location.updated"),
        ];
    }


    public function broadcastAs()
    {
        return "LocationUpdated";
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return $this->locationData;
    }
}
