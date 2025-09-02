<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReservationController;

use App\Http\Controllers\GoogleController;

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
    Route::get('/reservas', [ReservationController::class, 'getAllReservations'])->middleware('auth');

    // Google Calendar OAuth
    Route::prefix('google')->name('google.')->group(function () {
        Route::get('/auth', [GoogleController::class, 'auth'])->name('auth');
        Route::get('/callback', [GoogleController::class, 'callback'])->name('callback');
        Route::get('/revoke', [GoogleController::class, 'revoke'])->name('revoke');
        Route::get('/token-info', [GoogleController::class, 'tokenInfo'])->name('token-info');
        
        // Ruta para obtener eventos del calendario
        Route::get('/calendar/events', [GoogleController::class, 'getCalendarEvents'])->name('calendar.events');
    });
    
        // Web (sesiones)
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/admin/reservas', [ReservationController::class, 'index']);
    });

        // API (con Sanctum, por ejemplo)
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/api/admin/reservas', [ReservationController::class, 'index']);
    });
    
    // Revocar acceso de Google
    Route::post('/revoke-google-access', [AuthController::class, 'revokeGoogleAccess'])->name('revoke.google.access');
});

// Ruta de fallback
Route::fallback(function () {
    return redirect()->route('home');
});


