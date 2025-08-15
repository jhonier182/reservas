<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Services\GoogleCalendarService;
use App\Services\GoogleAuthService;
use App\Models\Reservation;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GoogleCalendarController extends Controller
{
    protected GoogleCalendarService $googleCalendarService;
    protected GoogleAuthService $googleAuthService;

    public function __construct(GoogleCalendarService $googleCalendarService, GoogleAuthService $googleAuthService)
    {
        $this->googleCalendarService = $googleCalendarService;
        $this->googleAuthService = $googleAuthService;
    }

    /**
     * Mostrar vista de sincronización de calendario
     */
    public function index(): View
    {
        $user = Auth::user();
        
        // Datos básicos para evitar errores con servicios no implementados
        $data = [
            'user' => $user,
            'syncStats' => [
                'total_events' => 0,
                'synced_events' => 0,
                'last_sync' => null,
            ],
            'hasPermissions' => false,
        ];
        
        return view('google.calendar.index', $data);
    }

    /**
     * Sincronizar eventos con Google Calendar
     */
    public function syncEvents(): JsonResponse
    {
        try {
            $user = Auth::user();
            $results = $this->googleCalendarService->syncEvents($user);

            return response()->json([
                'success' => true,
                'message' => 'Sincronización completada',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la sincronización: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear evento en Google Calendar desde una reserva
     */
    public function createEvent(Reservation $reservation): JsonResponse
    {
        try {
            $this->authorize('update', $reservation);

            $event = $this->googleCalendarService->createEvent($reservation);

            return response()->json([
                'success' => true,
                'message' => 'Evento creado en Google Calendar',
                'event_id' => $event->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear evento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar eventos del usuario
     */
    public function listEvents(Request $request): View
    {
        $user = Auth::user();
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $events = $this->googleCalendarService->getEventsForPeriod(
            $user, 
            Carbon::parse($startDate), 
            Carbon::parse($endDate)
        );

        return view('google.calendar.events', compact('events', 'startDate', 'endDate'));
    }

    /**
     * Sincronizar calendarios
     */
    public function syncCalendars(): JsonResponse
    {
        try {
            $user = Auth::user();
            $results = $this->googleCalendarService->syncCalendars($user);

            return response()->json([
                'success' => true,
                'message' => 'Calendarios sincronizados',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al sincronizar calendarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar estado de sincronización
     */
    public function checkSyncStatus(): JsonResponse
    {
        try {
            $user = Auth::user();
            $hasPermissions = $this->googleCalendarService->checkCalendarPermissions($user);
            $syncStats = $this->googleCalendarService->getSyncStats($user);

            return response()->json([
                'success' => true,
                'has_permissions' => $hasPermissions,
                'sync_stats' => $syncStats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener eventos para un período específico (API)
     */
    public function getEventsForPeriod(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $startDate = Carbon::parse($request->get('start_date'));
            $endDate = Carbon::parse($request->get('end_date'));

            $events = $this->googleCalendarService->getEventsForPeriod($user, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'events' => $events
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener eventos: ' . $e->getMessage()
            ], 500);
        }
    }
}
