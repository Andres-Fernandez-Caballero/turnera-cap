<?php

namespace App\Models\Images\Traits;

use App\Models\Images\Image;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasImages
{
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class,"imageagle");
    }
}