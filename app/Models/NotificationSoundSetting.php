<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSoundSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'sound_file',
        'sound_enabled',
        'sound_volume',
    ];

    protected $casts = [
        'sound_enabled' => 'boolean',
        'sound_volume' => 'integer',
    ];

    /**
     * Get the current sound settings (singleton pattern)
     */
    public static function current()
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'sound_file' => 'notification-1.mp3',
                'sound_enabled' => true,
                'sound_volume' => 30,
            ]
        );
    }

    /**
     * Get available notification sounds
     */
    public static function getAvailableSounds(): array
    {
        return [
            'notification-1.mp3' => 'Bell',
            'notification-2.mp3' => 'Chime',
            'notification-3.mp3' => 'Pop',
            'notification-4.mp3' => 'Ding',
            'none' => 'None (Silent)',
        ];
    }
}
