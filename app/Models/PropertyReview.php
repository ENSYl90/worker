<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyReview extends NoDeleteBaseModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'rating',
        'review',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
