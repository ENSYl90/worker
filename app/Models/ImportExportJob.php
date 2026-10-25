<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ImportExportJob extends Model
{
    const TYPE_IMPORT = 'import';
    const TYPE_EXPORT = 'export';

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'type',
        'data_type',
        'data_type_name',
        'file_name',
        'status',
        'progress',
        'source_path',
        'result_path',
        'message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected $appends = [
        'download_url',
    ];

    public function getDownloadUrlAttribute()
    {
        if ($this->type !== self::TYPE_EXPORT || $this->status !== self::STATUS_COMPLETED || empty($this->result_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->result_path);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
