<?php

namespace App\Core\UseCases\Locations;

use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class ListLocations
{
    public function execute(): Collection
    {
        return Location::all()->map(function($location): Location 
        {
            $location->image = config('app.url') . Storage::url($location->image); 
            return $location;
        });
    }
}
