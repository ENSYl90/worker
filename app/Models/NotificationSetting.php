<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_type',
        'action',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * Check if a notification is enabled for a specific model and action
     */
    public static function isEnabled(string $modelType, string $action): bool
    {
        $setting = static::where('model_type', $modelType)
            ->where('action', $action)
            ->first();

        return $setting ? $setting->is_enabled : true;
    }

    /**
     * Get all available models for notification settings
     */
    public static function getAvailableModels(): array
    {
        return [
            'Order' => 'App\\Models\\Order',
            'User' => 'App\\Models\\User',
            'Vendor' => 'App\\Models\\Vendor',
            'Payment' => 'App\\Models\\Payment',
            'SubscriptionVendor' => 'App\\Models\\SubscriptionVendor',
            'Status' => 'Spatie\\ModelStatus\\Status',
        ];
    }

    /**
     * Get all available actions
     */
    public static function getAvailableActions(): array
    {
        return ['created', 'updated', 'deleted'];
    }
}
