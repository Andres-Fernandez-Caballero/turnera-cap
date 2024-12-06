<?php

namespace App\Http\Controllers;

use App\Core\UseCases\Locations\GetTimeSlotsByLocationId;
use App\Core\UseCases\Locations\ListLocations;
use App\Core\UseCases\Locations\ShowLocation;
use Illuminate\Http\Request;

class LocationApiController extends Controller
{
    private ListLocations $listLocations;
    private ShowLocation $showLocation;
    private GetTimeSlotsByLocationId $timeSlotsByLocationId;
    public function __construct(
        ListLocations $listLocations,
        ShowLocation $showLocation,
        GetTimeSlotsByLocationId $timeSlotsByLocationId
    )
    {
        $this->listLocations = $listLocations;
        $this->showLocation = $showLocation;
        $this->timeSlotsByLocationId = $timeSlotsByLocationId;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(
            $this->listLocations->execute(),
            200
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $location = $this->showLocation->execute($id);
        return response()->json(
            $location,
            200
        );
    }

    /**
     * Verificar Disponibilidad de una locacion
     */
    public function getAvailability(Request $request, int $id)
    {
        $date = $request->query('date');
        if (!$date) {
            return response()->json([
                'message' => 'El parámetro "date" es obligatorio.',
            ], 422);
        }

        $slotsAvailables = $this->timeSlotsByLocationId->execute($id, $date);
        
        return response()->json($slotsAvailables, 200);
    }
}
