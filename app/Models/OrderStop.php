<?php

namespace App\Models;


use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Fluent;

class OrderStop extends BaseModel
{

    protected $appends = ['delivery_address', 'proof', 'attachments'];

    public function getDeliveryAddressAttribute()
    {
        // If there's a related delivery_address loaded or exists in DB
        if ($this->stop_id && $this->deliveryAddressRelation) {
            $deliveryAddress = $this->deliveryAddressRelation;
        } else {

            // Fallback to inline address stored directly on the order_stops table
            $deliveryAddress = new Fluent([
                'id' => 0,
                'name'   => $this->address,
                'address'   => $this->address,
                'latitude'  => $this->latitude,
                'longitude' => $this->longitude,
                'city'      => $this->city,
                'state'     => $this->state,
                'country'   => $this->country,
                'is_default' => 0,
                "user_id"   => \Auth::id(),
                "created_at" => \Carbon\Carbon::now(),
                "updated_at" => \Carbon\Carbon::now(),
                "formatted_date" => translated_format_date(\Carbon\Carbon::now(), 'd M Y'),
                "photo" => env("APP_URL"),
            ]);
        }

        return $deliveryAddress;
    }

    public function deliveryAddressRelation()
    {
        return $this->belongsTo('App\Models\DeliveryAddress', 'stop_id', 'id')->withTrashed();
    }

    public function order()
    {
        return $this->belongsTo('App\Models\Order', 'order_id', 'id');
    }

    public function getProofAttribute()
    {
        return $this->getFirstMediaUrl('proof');
    }

    //MEDIA
    public function getAttachmentsAttribute()
    {
        $mediaFiles = Media::where("model_id", $this->id)->where("model_type", "App\Models\OrderStop")->get();
        $links = $mediaFiles->map(function ($media, $key) {
            return [
                "link" => $media->getFullUrl(),
                "collection_name" => $media->collection_name,
            ];
        });
        return $links;
    }
}
