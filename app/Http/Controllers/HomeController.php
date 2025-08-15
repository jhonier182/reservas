<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\ReservationService;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected ReservationService $reservationService;
    protected GoogleCalendarService $googleCalendarService;

    public function __construct(ReservationService $reservationService, GoogleCalendarService $googleCalendarService)
    {
        $this->reservationService = $reservationService;
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * Dashboard principal
     */
    public function index(): View
    {
        $user = Auth::user();
        
        // Datos básicos para el dashboard
        $data = [
            'user' => $user,
            'activeReservations' => 0,
            'completedReservations' => 0,
            'pendingReservations' => 0,
            'todayEvents' => 0,
            'upcomingReservations' => [],
        ];
        
        return view('home', $data);
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

