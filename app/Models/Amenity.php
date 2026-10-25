<?php

namespace App\Models;

use App\Traits\HasTranslations;

class Amenity extends NoDeleteBaseModel
{
    use HasTranslations;
    public $translatable = ['name'];
    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $with = [];


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
     * Get properties that have this amenity
     */
    public function properties()
    {
        return $this->belongsToMany('App\Models\Property', 'amenity_property', 'amenity_id', 'property_id')
            ->withTimestamps();
    }



    /**
     * Scope to get only active amenities
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }


}
