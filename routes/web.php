<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\GoogleCalendarController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas web para tu aplicación.
| Estas rutas son cargadas por RouteServiceProvider y todas
| ellas asignarán el middleware "web" a tu grupo de rutas.
|
*/

// Rutas públicas
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/no-autorizado', function () { return view('no-autorizado'); })->name('no-autorizado');

// Rutas de autenticación
Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/google', [AuthController::class, 'redirectToGoogle'])->name('google');
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.callback');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {
    
    // Dashboard y vistas principales
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/calendar', [HomeController::class, 'calendar'])->name('calendar');
    Route::get('/profile', [HomeController::class, 'profile'])->name('profile');
    
    // Gestión de reservas
    Route::resource('reservations', ReservationController::class);
    Route::post('/reservations/{reservation}/change-status', [ReservationController::class, 'changeStatus'])->name('reservations.change-status');
    Route::post('/reservations/{reservation}/mark-completed', [ReservationController::class, 'markAsCompleted'])->name('reservations.mark-completed');
    
    // Google Calendar
    Route::prefix('google')->name('google.')->group(function () {
        Route::get('/calendar', [GoogleCalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/events', [GoogleCalendarController::class, 'listEvents'])->name('calendar.events');
        Route::post('/calendar/sync-events', [GoogleCalendarController::class, 'syncEvents'])->name('calendar.sync-events');
        Route::post('/calendar/sync-calendars', [GoogleCalendarController::class, 'syncCalendars'])->name('calendar.sync-calendars');
        Route::get('/calendar/check-status', [GoogleCalendarController::class, 'checkSyncStatus'])->name('calendar.check-status');
        Route::get('/calendar/events-period', [GoogleCalendarController::class, 'getEventsForPeriod'])->name('calendar.events-period');
        Route::post('/calendar/create-event/{reservation}', [GoogleCalendarController::class, 'createEvent'])->name('calendar.create-event');
    });
    
    // Revocar acceso de Google
    Route::post('/revoke-google-access', [AuthController::class, 'revokeGoogleAccess'])->name('revoke.google.access');
});

// Ruta de fallback
Route::fallback(function () {
    return redirect()->route('home');
});


