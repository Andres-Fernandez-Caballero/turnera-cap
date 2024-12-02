<?php

namespace App\Models\Images;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    protected $fillable = [
        'path'
        ];

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}
