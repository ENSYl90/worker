<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'state_id',
        'name',
        'status',
        'is_active',
        'boundaries'
    ];

    protected $casts = [
        // 'boundaries' => 'array'
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
