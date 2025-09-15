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
use App\Services\GoogleCalendarService;

class ReservationController extends Controller
{
    protected ReservationService $reservationService;
    protected NotificationService $notificationService;

    protected GoogleCalendarService $calendarService; //agg google calendar

    public function __construct(
        ReservationService $reservationService, 
        NotificationService $notificationService,
        GoogleCalendarService $calendarService
    ) {
        $this->reservationService = $reservationService;
        $this->notificationService = $notificationService;
        $this->calendarService = $calendarService;

        $this->middleware('auth'); 
        $this->authorizeResource(\App\Models\Reservation::class, 'reservation');
    }//lo cambie para agg google calendar

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

        // Construir la consulta base
        $query = Reservation::with('user');

        // Si es administrador, mostrar todas las reservas; si no, solo las suyas
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        // Aplicar filtros de búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Aplicar filtro de estado
        if ($status) {
            $query->where('status', $status);
        }

        // Aplicar filtro de tipo
        if ($type) {
            $query->where('type', $type);
        }

        // Ordenar y obtener resultados
        $reservations = $query->orderBy('start_date', 'desc')->get();

        $stats = $this->reservationService->getReservationStats($user);

        return view('reservations.index', compact('reservations', 'stats', 'search', 'status', 'type'));
    }

    
    public function getAllReservations(Request $request): JsonResponse
    {
        try {
            $start = \Carbon\Carbon::parse($request->query('start_date', now()->startOfMonth()));
            $end   = \Carbon\Carbon::parse($request->query('end_date',   now()->endOfMonth()));
            $loc   = $request->query('location'); // 'jardin' | 'casino' | null
            $search = $request->query('search');
            $status = $request->query('status');   // pending | confirmed | completed | cancelled
            $type   = $request->query('type');     // meeting | event | appointment | other
        
        
            $user = \Illuminate\Support\Facades\Auth::user();

            
        
            // 👇 Usa los nombres reales de tus columnas
            $reservas = \App\Models\Reservation::query()
                ->with('user')
                ->whereBetween('start_date', [$start, $end])
                ->when($loc, fn($q) => $q->where('location', $loc))
                ->orderBy('start_date', 'asc')
                ->get();
            $eventos = $reservas->map(function ($r) use ($user) {
                
        
                $isAdmin = $user && (method_exists($user, 'isAdmin') ? $user->isAdmin() : ($user->role === 'admin'));
                $isOwner = $user && (intval($r->user_id) === intval($user->id));
                $canEdit = $isAdmin || $isOwner;

                
        
                return [
                    'id'    => $r->id,
                    'title' => $r->title, // 👈 title (no titulo)
                    'start' => \Carbon\Carbon::parse($r->start_date)->timezone('America/Bogota')->format('Y-m-d\TH:i:s'),
                    'end'   => \Carbon\Carbon::parse($r->end_date)->timezone('America/Bogota')->format('Y-m-d\TH:i:s'),
        
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
                        'responsible' => $r->responsible_name,        // 👈 responsable
                        'people' => (int) ($r->people_count ?? 0),// 👈 asistentes/cupo
                        'ownerId'     => $r->user_id,
                        'ownerEmail'  => $r->usuario_email ?? '',
                        'canEdit'     => $canEdit,
                        'isAdmin'     => $isAdmin,
                        'isOwner'     => $isOwner,
                        'squad'       => $r->squad,
                    ],
                ];
            })->values();
        
            return response()->json([
                'success' => true,
                'events'  => $eventos->values()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en getAllReservations: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error al cargar las reservas',
                'events' => []
            ], 500);
        }
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
            $request->merge(['start_date' => $roundedStart->format('Y-m-d H:i:s')]);
        }
        if ($request->filled('end_date')) {
            $roundedEnd = $this->roundToNearestQuarterHour(\Carbon\Carbon::parse($request->end_date));
            $request->merge(['end_date' => $roundedEnd->format('Y-m-d H:i:s')]);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date',
            'location' => 'required|in:jardin,casino',
            'people_count' => ['required', 'integer','min:1'],
            'type' => 'required|in:meeting,event,appointment,other',
            'squad' => ['nullable','string','max:100'],
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
            // Cargar la relación user para poder acceder a usuario_email
            $reservation->load('user');
            
            $this->notificationService->sendReservationConfirmation($reservation);

            
    
            return redirect()->route('calendar')
                ->with('success', 'Reserva creada correctamente y enviada a Google Calendar');
        } catch (\Exception $e) {
            \Log::error('Error al crear reserva: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'data' => $data ?? null
            ]);
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
        // Cualquier usuario puede ver (según Policy->view). Si quieres forzar policy:
        // $this->authorize('view', $reservation);
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

        // Redondear entradas a múltiplos de 15 antes de validar
        if ($request->filled('start_date')) {
            $roundedStart = $this->roundToNearestQuarterHour(\Carbon\Carbon::parse($request->start_date));
            $request->merge(['start_date' => $roundedStart->format('Y-m-d H:i:s')]);
        }
        if ($request->filled('end_date')) {
            $roundedEnd = $this->roundToNearestQuarterHour(\Carbon\Carbon::parse($request->end_date));
            $request->merge(['end_date' => $roundedEnd->format('Y-m-d H:i:s')]);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'location' => 'required|in:jardin,casino',
            'people_count' => ['required','integer','min:1'],
            'type' => 'required|in:meeting,event,appointment,other'
        ]);

        $reservation->people_count = (int) $request->input('people_count');
        $reservation->save();

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
            $reservation->update(['status' => 'completed']);
            
            return redirect()->back()->with('success', 'Reserva marcada como completada');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al marcar como completada');
        }
    }
}
