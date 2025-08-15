<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Models\Reservation;
use App\Services\ReservationService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    protected ReservationService $reservationService;
    protected NotificationService $notificationService;

    public function __construct(ReservationService $reservationService, NotificationService $notificationService)
    {
        $this->reservationService = $reservationService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $search = $request->get('search');
        $status = $request->get('status');
        $type = $request->get('type');

        $reservations = $this->reservationService->getUserReservations($user);
        
        if ($search) {
            $reservations = $this->reservationService->searchReservations($user, $search);
        }

        if ($status) {
            $reservations = $reservations->where('status', $status);
        }

        if ($type) {
            $reservations = $reservations->where('type', $type);
        }

        $stats = $this->reservationService->getReservationStats($user);

        return view('reservations.index', compact('reservations', 'stats', 'search', 'status', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = Auth::user();
        return view('reservations.create', compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'type' => 'required|in:meeting,event,appointment,other'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $request->all();
            $data['user_id'] = Auth::id();
            
            $reservation = $this->reservationService->createReservation($data);
            
            // Enviar notificación de confirmación
            $this->notificationService->sendReservationConfirmation($reservation);
            
            return redirect()->route('reservations.show', $reservation)
                ->with('success', 'Reserva creada correctamente');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear la reserva: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation): View
    {
        $this->authorize('view', $reservation);
        
        return view('reservations.show', compact('reservation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation): View
    {
        $this->authorize('update', $reservation);
        
        return view('reservations.edit', compact('reservation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->authorize('update', $reservation);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'type' => 'required|in:meeting,event,appointment,other'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $changes = $reservation->getDirty();
            
            $this->reservationService->updateReservation($reservation, $request->all());
            
            // Enviar notificación de cambio si hay cambios importantes
            if (!empty($changes)) {
                $this->notificationService->sendReservationChanged($reservation, $changes);
            }
            
            return redirect()->route('reservations.show', $reservation)
                ->with('success', 'Reserva actualizada correctamente');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar la reserva: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation): RedirectResponse
    {
        $this->authorize('delete', $reservation);

        try {
            $this->reservationService->deleteReservation($reservation);
            
            // Enviar notificación de cancelación
            $this->notificationService->sendCancellationNotice($reservation);
            
            return redirect()->route('reservations.index')
                ->with('success', 'Reserva eliminada correctamente');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la reserva: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estado de la reserva
     */
    public function changeStatus(Request $request, Reservation $reservation): JsonResponse
    {
        $this->authorize('update', $reservation);

        $validStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        $newStatus = $request->get('status');

        if (!in_array($newStatus, $validStatuses)) {
            return response()->json(['error' => 'Estado no válido'], 400);
        }

        try {
            $reservation->update(['status' => $newStatus]);
            
            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'status' => $newStatus
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el estado'], 500);
        }
    }

    /**
     * Marcar reserva como completada
     */
    public function markAsCompleted(Reservation $reservation): RedirectResponse
    {
        $this->authorize('update', $reservation);

        try {
            $reservation->markAsCompleted();
            
            return redirect()->back()->with('success', 'Reserva marcada como completada');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al marcar como completada');
        }
    }
}
