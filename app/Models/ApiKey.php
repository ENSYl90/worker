<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'key_hash',
        'key_prefix',
        'last_four',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public static function generatePlainKey(): string
    {
        return self::plainKeyPrefix() . str_replace(['+', '/', '='], '', base64_encode(random_bytes(48)));
    }

    public static function plainKeyPrefix(): string
    {
        $name = Str::slug(config('app.name', 'app'), '_');
        $name = preg_replace('/[^a-z0-9_]/', '', strtolower($name));
        $name = trim($name, '_') ?: 'app';

        return substr($name, 0, 20) . '_sk_';
    }

    public static function hashKey(string $key): string
    {
        return hash_hmac('sha256', $key, config('app.key'));
    }

    public static function cacheKeyForHash(string $hash): string
    {
        return 'api_security_key_' . $hash;
    }

    public static function previewPrefix(string $key): string
    {
        return substr($key, 0, min(strlen(self::plainKeyPrefix()) + 4, 32));
    }

    public static function previewLastFour(string $key): string
    {
        return substr($key, -4);
    }
}
