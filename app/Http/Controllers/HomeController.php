<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\ReservationService;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected ReservationService $reservationService;
    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * Dashboard principal
     */
    public function index(): View
    {
        $user = Auth::user();
        $now = now();

        // Base query: si no es admin, filtrar por el usuario actual
        $baseQuery = \App\Models\Reservation::query();
        if (!$user->isAdmin()) {
            $baseQuery->where('user_id', $user->id);
        }

        // Estadísticas
        $activeReservations = (clone $baseQuery)
            ->where('status', '!=', 'cancelled')
            ->where('end_date', '>=', $now)
            ->count();

        $completedReservations = (clone $baseQuery)
            ->where('status', 'completed')
            ->count();

        $pendingReservations = (clone $baseQuery)
            ->where('status', 'pending')
            ->count();

        $todayEvents = (clone $baseQuery)
            ->whereDate('start_date', $now->toDateString())
            ->count();

        // Próximas reservas (limitar a 5)
        $upcomingReservations = (clone $baseQuery)
            ->where('status', '!=', 'cancelled')
            ->where('start_date', '>=', $now)
            ->orderBy('start_date', 'asc')
            ->limit(5)
            ->get();

        return view('home', [
            'user' => $user,
            'activeReservations' => $activeReservations,
            'completedReservations' => $completedReservations,
            'pendingReservations' => $pendingReservations,
            'todayEvents' => $todayEvents,
            'upcomingReservations' => $upcomingReservations,
        ]);
    }

    /**
     * Vista del calendario
     */
    public function calendar(Request $request): View
    {
        $user = Auth::user();
        
        return view('calendar', [
            'user' => $user,
            'reservations' => [],
            'events' => [],
            'startDate' => now()->startOfMonth(),
            'endDate' => now()->endOfMonth(),
        ]);
    }

    /**
     * Vista de perfil del usuario
     */
    public function profile(): View
    {
        $user = Auth::user();
        
        return view('profile', [
            'user' => $user,
            'primaryCalendar' => null,
        ]);
    }
}

