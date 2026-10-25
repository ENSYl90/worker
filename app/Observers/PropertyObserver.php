<?php

namespace App\Observers;

use App\Models\Property;
use Illuminate\Support\Str;

class PropertyObserver
{
    /**
     * Handle the Property "creating" event.
     *
     * @param  \App\Models\Property  $property
     * @return void
     */
    public function creating(Property $property)
    {
        $property->slug = $this->generateUniqueSlug($property->name ?? $property->slug);
    }

    /**
     * Handle the Property "updating" event.
     *
     * @param  \App\Models\Property  $property
     * @return void
     */
    public function updating(Property $property)
    {
        if ($property->isDirty('name')) {
            $property->slug = $this->generateUniqueSlug($property->name);
        }
    }

    /**
     * Generate a unique slug for the property.
     *
     * @param string $name
     * @return string
     */
    private function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (Property::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
