<?php

namespace App\Models\Traits;

trait HasCapitalizeName
{
    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = ucwords(strtolower($value));
    }
}