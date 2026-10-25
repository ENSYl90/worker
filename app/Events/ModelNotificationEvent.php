<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelNotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $modelType;
    public $action;
    public $modelData;
    public $modelId;

    /**
     * Create a new event instance.
     */
    public function __construct(string $modelType, string $action, $model)
    {
        $this->modelType = $modelType;
        $this->action = $action;
        $this->modelId = $model->id ?? null;

        // Convert model to array for broadcasting
        $this->modelData = [
            'id' => $model->id ?? null,
            'type' => class_basename($model),
            'data' => $this->getModelData($model),
        ];
    }

    /**
     * Get simplified model data for notification
     */
    protected function getModelData($model): array
    {
        $data = [];

        // Get basic attributes
        if (method_exists($model, 'toArray')) {
            $attributes = $model->toArray();

            // Only include basic fields to keep payload small
            $allowedFields = ['id', 'name', 'code', 'status', 'email', 'phone', 'total', 'created_at', 'updated_at'];
            foreach ($allowedFields as $field) {
                if (isset($attributes[$field])) {
                    $data[$field] = $attributes[$field];
                }
            }
        }

        return $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('backend-notifications'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'model.notification';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $possibleLink = "";
        //depending on the model


        return [
            'modelType' => $this->modelType,
            'action' => $this->action,
            'modelData' => $this->modelData,
            "link" => $possibleLink,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
