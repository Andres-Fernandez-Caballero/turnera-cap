<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\Middleware;

class BookingApiController extends Controller implements HasMiddleware
{

        /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $booking = Booking::with('location', 'timeSlots')
            ->get();

        return response()
            ->json($booking, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        static::validateRequest( $request, [
            'location_id' => 'required|exists:locations,id',
            'time_slot_ids' => 'required|array',
            'people_count' => 'required|integer|min:1',
        ] );


        $location = Location::findOrFail($request->location_id);

        // Verificar si la locación tiene TimeSlots
        if ($location->timeSlots->where('is_active', true)->isEmpty()) {
            return response()->json([
                'message' => 'No se pueden crear reservas porque la locación no tiene horarios disponibles.',
            ], 422);
        }

        // Verificar si el horario solicitado está dentro de un TimeSlot
        $timeSlots = TimeSlot::whereIn('id', $request->time_slot_ids)
            ->where('is_active', true)->get();

        if($timeSlots->isEmpty()) {
            return response()->json([
                'message' => 'No se pueden crear reservas porque el horario solicitado no está disponible.',
            ], 422);
        }

        $timeSlots->each(function ($timeSlot) use ($request) {
            $timeSlot->validateTimeSlot($request);
        });

        // Verificar capacidad
        $overlapBookings = $location->bookings()
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($query) use ($request) {
                        $query->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                    });
            })
            ->sum('people_count');


        if ($overlapBookings + $request->people_count > $location->capacity) {
            return response()->json([
                'message' => 'Capacidad excedida para este horario.',
            ], 422);
        }

        $request['user_id'] = $user->id;
        // Crear reserva
        $booking = Booking::create($request->all());

        return response()->json($booking, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $booking = Booking::with('location')->findOrFail($id);
        return response()->json($booking, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        static::validateRequest( $request,
        [
            'start_time' => 'sometimes|date|before:end_time',
            'end_time' => 'sometimes|date|after:start_time',
            'people_count' => 'sometimes|integer|min:1',
        ]
    );


        $booking->update($request->all());
        return response()->json($booking, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return response()->json($booking, 204);
    }

    private static function validateRequest(Request $request, array $rules = [])
    {
        $validator = Validator::make($request->all(),$rules);
        if( $validator->fails() ) {
            return response()->json($validator->errors(), 405);
        }
    }
}
