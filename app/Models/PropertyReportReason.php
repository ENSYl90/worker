<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasTranslations;

class PropertyReportReason extends BaseModel
{
    use HasFactory, SoftDeletes, HasTranslations;

    public $translatable = ['reason'];

    protected $fillable = [
        'reason',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    //propertyReport
    public function propertyReport()
    {
        return $this->hasMany(PropertyReport::class);
    }
}
