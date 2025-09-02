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
use Carbon\Carbon;

class ReservationController extends Controller
{
    protected ReservationService $reservationService;
    protected NotificationService $notificationService;

    public function __construct(ReservationService $reservationService, NotificationService $notificationService)
    {
        $this->reservationService = $reservationService;
        $this->notificationService = $notificationService;
        $this->middleware('auth'); // si requieres login
        $this->authorizeResource(\App\Models\Reservation::class, 'reservation');
    }

    /**
     * Redondea una fecha/hora al múltiplo de 15 minutos más cercano.
     */
    private function roundToNearestQuarterHour(\Carbon\Carbon $dateTime): \Carbon\Carbon
    {
        $minutes = $dateTime->minute;
        $remainder = $minutes % 15;
        if ($remainder !== 0) {
            if ($remainder < 8) {
                $dateTime->subMinutes($remainder);
            } else {
                $dateTime->addMinutes(15 - $remainder);
            }
        }
        $dateTime->second(0);
        return $dateTime;
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

    
    public function getAllReservations(Request $request): JsonResponse
    {
        $start = \Carbon\Carbon::parse($request->query('start_date', now()->startOfMonth()));
        $end   = \Carbon\Carbon::parse($request->query('end_date',   now()->endOfMonth()));
        $loc   = $request->query('location'); // 'jardin' | 'casino' | null
    
        $user = \Illuminate\Support\Facades\Auth::user();
    
        // 👇 Usa los nombres reales de tus columnas
        $reservas = \App\Models\Reservation::query()
            ->whereBetween('start_date', [$start, $end])
            ->when($loc, fn($q) => $q->where('location', $loc))
            ->orderBy('start_date', 'asc')
            ->get();
    
        $eventos = $reservas->map(function ($r) use ($user) {
            $ownerId = $r->user_id ?? null;
    
            $isAdmin = $user && (($user->role ?? null) === 'admin');
            $isOwner = $user && $ownerId && ((int)$user->id === (int)$ownerId);
            $canEdit = $isAdmin || $isOwner;
    
            return [
                'id'    => $r->id,
                'title' => $r->title, // 👈 title (no titulo)
                'start' => \Carbon\Carbon::parse($r->start_date)->toIso8601String(),
                'end'   => \Carbon\Carbon::parse($r->end_date)->toIso8601String(),
    
                'backgroundColor' => '#10B981',
                'borderColor'     => '#059669',
                'textColor'       => '#FFFFFF',
    
                // Permisos por evento
                'editable'         => $canEdit,
                'startEditable'    => $canEdit,
                'durationEditable' => $canEdit,
    
                'extendedProps' => [
                    'type'        => 'local_reservation',
                    'description' => $r->description, // 👈 description (no descripcion)
                    'location'    => $r->location,    // 👈 location (no ubicacion)
                    'ownerId'     => $ownerId,
                    'ownerEmail'  => $r->usuario_email ?? null,
                    'canEdit'     => $canEdit,
                    'isAdmin'     => $isAdmin,
                    'isOwner'     => $isOwner,
                ],
            ];
        })->values();
    
        return response()->json([
            'success' => true,
            'events'  => $eventos,
        ]);
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
        // Redondear entradas a múltiplos de 15 antes de validar
        if ($request->filled('start_date')) {
            $roundedStart = $this->roundToNearestQuarterHour(\Carbon\Carbon::parse($request->start_date));
            $request->merge(['start_date' => $roundedStart->format('Y-m-d H:i')]);
        }
        if ($request->filled('end_date')) {
            $roundedEnd = $this->roundToNearestQuarterHour(\Carbon\Carbon::parse($request->end_date));
            $request->merge(['end_date' => $roundedEnd->format('Y-m-d H:i')]);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date',
            'location' => 'required|in:jardin,casino',
            'type' => 'required|in:meeting,event,appointment,other'
        ]);
        

        // Validación personalizada para conflictos de ubicación y múltiplos de 15 minutos
        $validator->after(function ($validator) use ($request) {
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            
            // Validar múltiplos de 15 minutos
            if ($startDate->minute % 15 !== 0) {
                $validator->errors()->add('start_date', 'Los minutos de la fecha de inicio deben ser 00, 15, 30 o 45.');
            }
            if ($endDate->minute % 15 !== 0) {
                $validator->errors()->add('end_date', 'Los minutos de la fecha de fin deben ser 00, 15, 30 o 45.');
            }

            if ($endDate->lte($startDate)) {
                $validator->errors()->add('end_date', 'La fecha de fin debe ser posterior a la fecha de inicio.');
            }

            // Verificar si la ubicación está disponible
            if (!\App\Models\Reservation::isLocationAvailable($request->location, $startDate, $endDate)) {
                $validator->errors()->add('location', 'La ubicación seleccionada no está disponible en la fecha y hora especificadas.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $request->all();
            $data['user_id'] = Auth::id();
    
            // Forzar la sala si viene desde el calendario
            $locked = $request->query('location');
            if (in_array($locked, ['jardin','casino'], true)) {
                $data['location'] = $locked;
            }

            // 👇 AÑADE ESTO: responsable por defecto = usuario actual
            $data['responsible_name'] = Auth::user()?->name ?? 'Sistema';
    
            $reservation = $this->reservationService->createReservation($data);
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
        // Verificar que el usuario sea el propietario de la reserva
        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para ver esta reserva.');
        }
        
        return view('reservations.show', compact('reservation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation): View
    {
        // Verificar que el usuario sea el propietario de la reserva
        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para editar esta reserva.');
        }
        
        return view('reservations.edit', compact('reservation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->authorize('update', $reservation);

        // Redondear entradas a múltiplos de 15 antes de validar
        if ($request->filled('start_date')) {
            $roundedStart = $this->roundToNearestQuarterHour(\Carbon\Carbon::parse($request->start_date));
            $request->merge(['start_date' => $roundedStart->format('Y-m-d H:i')]);
        }
        if ($request->filled('end_date')) {
            $roundedEnd = $this->roundToNearestQuarterHour(\Carbon\Carbon::parse($request->end_date));
            $request->merge(['end_date' => $roundedEnd->format('Y-m-d H:i')]);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'location' => 'required|in:jardin,casino',
            'type' => 'required|in:meeting,event,appointment,other'
        ]);

        // Validación personalizada para conflictos de ubicación y múltiplos de 15 minutos
        $validator->after(function ($validator) use ($request, $reservation) {
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            
            // Validar múltiplos de 15 minutos
            if ($startDate->minute % 15 !== 0) {
                $validator->errors()->add('start_date', 'Los minutos de la fecha de inicio deben ser 00, 15, 30 o 45.');
            }
            if ($endDate->minute % 15 !== 0) {
                $validator->errors()->add('end_date', 'Los minutos de la fecha de fin deben ser 00, 15, 30 o 45.');
            }

            if ($endDate->lte($startDate)) {
                $validator->errors()->add('end_date', 'La fecha de fin debe ser posterior a la fecha de inicio.');
            }

            // Verificar si la ubicación está disponible (excluyendo la reserva actual)
            if (!\App\Models\Reservation::isLocationAvailable($request->location, $startDate, $endDate, $reservation->id)) {
                $validator->errors()->add('location', 'La ubicación seleccionada no está disponible en la fecha y hora especificadas.');
            }
        });

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
        // Verificar que el usuario puede eliminar esta reserva
        if ($reservation->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'No tienes permisos para eliminar esta reserva');
        }

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
        // Verificar que el usuario puede actualizar esta reserva
        if ($reservation->user_id !== Auth::id()) {
            return response()->json(['error' => 'No tienes permisos para actualizar esta reserva'], 403);
        }

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
        // Verificar que el usuario puede actualizar esta reserva
        if ($reservation->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'No tienes permisos para actualizar esta reserva');
        }

        try {
            $reservation->update(['status' => 'completed']);
            
            return redirect()->back()->with('success', 'Reserva marcada como completada');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al marcar como completada');
        }
    }
}
