<?php

namespace App\Core\UseCases\Locations;

use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;

class ListLocations
{
    public function execute(): Collection
    {
        $server_url = env('APP_URL', 'localhost');
        return Location::all()->map(function($location) use($server_url): Location 
        {
            $location->image = "{$server_url}/storage/{$location->image}";
            return $location;
        });
    }
}
