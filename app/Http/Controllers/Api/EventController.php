<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EventController extends Controller
{
    /**
     * Obtener todos los eventos para el calendario
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Obtener parámetros de fecha
        $start = $request->get('start', now()->startOfMonth());
        $end = $request->get('end', now()->endOfMonth());
        
        // Obtener reservas del usuario en el rango de fechas
        $reservations = Reservation::where('user_id', $user->id)
            ->whereBetween('start_date', [$start, $end])
            ->orWhereBetween('end_date', [$start, $end])
            ->orWhere(function($query) use ($start, $end) {
                $query->where('start_date', '<=', $start)
                      ->where('end_date', '>=', $end);
            })
            ->get();
        
        // Convertir reservas a formato de FullCalendar
        $events = $reservations->map(function ($reservation) {
            return [
                'id' => $reservation->id,
                'title' => $reservation->title,
                'start' => $reservation->start_date,
                'end' => $reservation->end_date,
                'allDay' => false,
                'extendedProps' => [
                    'description' => $reservation->description,
                    'type' => $reservation->type,
                    'location' => $reservation->location,
                    'status' => $reservation->status ?? 'pending',
                    'user_id' => $reservation->user_id,
                ],
                'backgroundColor' => $this->getEventColor($reservation->type),
                'borderColor' => $this->getEventBorderColor($reservation->status ?? 'pending'),
                'textColor' => '#ffffff',
            ];
        });
        
        return response()->json($events);
    }
    
    /**
     * Crear un nuevo evento
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'type' => 'required|in:meeting,event,appointment,other',
            'location' => 'nullable|string|max:255',
        ]);
        
        $user = Auth::user();
        
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'type' => $request->type,
            'location' => $request->location,
            'status' => 'pending',
        ]);
        
        return response()->json([
            'id' => $reservation->id,
            'title' => $reservation->title,
            'start_date' => $reservation->start_date,
            'end_date' => $reservation->end_date,
            'description' => $reservation->description,
            'type' => $reservation->type,
            'location' => $reservation->location,
            'status' => $reservation->status,
        ], 201);
    }
    
    /**
     * Actualizar un evento existente
     */
    public function update(Request $request, Reservation $event): JsonResponse
    {
        $this->authorize('update', $event);
        
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'type' => 'sometimes|required|in:meeting,event,appointment,other',
            'location' => 'nullable|string|max:255',
            'status' => 'sometimes|required|in:pending,confirmed,completed,cancelled',
        ]);
        
        $event->update($request->only([
            'title', 'description', 'start_date', 'end_date', 'type', 'location', 'status'
        ]));
        
        return response()->json([
            'id' => $event->id,
            'title' => $event->title,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'description' => $event->description,
            'type' => $event->type,
            'location' => $event->location,
            'status' => $event->status,
        ]);
    }
    
    /**
     * Eliminar un evento
     */
    public function destroy(Reservation $event): JsonResponse
    {
        $this->authorize('delete', $event);
        
        $event->delete();
        
        return response()->json(['message' => 'Evento eliminado exitosamente']);
    }
    
    /**
     * Obtener color del evento según el tipo
     */
    private function getEventColor(string $type): string
    {
        return match($type) {
            'meeting' => '#3b82f6',    // Azul
            'event' => '#10b981',      // Verde
            'appointment' => '#f59e0b', // Amarillo
            'other' => '#8b5cf6',      // Púrpura
            default => '#6b7280',      // Gris
        };
    }
    
    /**
     * Obtener color del borde según el estado
     */
    private function getEventBorderColor(string $status): string
    {
        return match($status) {
            'confirmed' => '#10b981',  // Verde
            'pending' => '#f59e0b',    // Amarillo
            'completed' => '#3b82f6',  // Azul
            'cancelled' => '#ef4444',  // Rojo
            default => '#6b7280',      // Gris
        };
    }
}
