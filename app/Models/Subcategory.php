<?php

namespace App\Models;

use Kirschbaum\PowerJoins\PowerJoins;
use App\Traits\HasTranslations;
use App\Models\Service;

class Subcategory extends BaseModel
{
    use PowerJoins;
    use HasTranslations;
    public $translatable = ['name'];

    protected $fillable = [
        'id',
        'name',
        'category_id',
        'is_active'
    ];

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function products()
    {
        return $this->belongsToMany('App\Models\Product');
    }

    public function services()
    {
        // return $this->belongsToMany('App\Models\Service');
        return $this->hasMany(Service::class, 'subcategory_id');
    }
}
