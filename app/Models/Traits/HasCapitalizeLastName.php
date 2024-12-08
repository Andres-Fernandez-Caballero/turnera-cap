<?php

namespace App\Models\Traits;

trait HasCapitalizeLastName
{
    public function setLastNameAttribute(string $value): void
    {
        $this->attributes['last_name'] = ucwords(strtolower($value));
    }
}