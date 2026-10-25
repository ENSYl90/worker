<?php

namespace App\Observers;

use App\Events\ModelNotificationEvent;
use App\Models\NotificationSetting;

class ModelNotificationObserver
{
    /**
     * Handle the model "created" event.
     */
    public function created($model): void
    {
        $this->dispatchNotification($model, 'created');
    }

    /**
     * Handle the model "updated" event.
     */
    public function updated($model): void
    {
        $this->dispatchNotification($model, 'updated');
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted($model): void
    {
        $this->dispatchNotification($model, 'deleted');
    }

    /**
     * Dispatch notification if enabled for this model and action
     */
    protected function dispatchNotification($model, string $action): void
    {
        $modelType = class_basename($model);

        // Check if notification is enabled for this model and action
        if (NotificationSetting::isEnabled($modelType, $action)) {
            event(new ModelNotificationEvent($modelType, $action, $model));
        }
    }
}
