<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasTranslations;

class PropertyType extends NoDeleteBaseModel
{
    use HasFactory, HasTranslations;

    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();

        //while creating/inserting item into db
        static::creating(function ($model) {
            $model->slug = \Str::slug($model->name);
        });
        static::updating(function ($model) {
            $model->slug = \Str::slug($model->name);
        });
    }

    /**
     * Get properties of this type
     */
    public function properties()
    {
        return $this->hasMany('App\Models\Property', 'property_type_id', 'id');
    }
}
