<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Google\Client;
use App\Services\GoogleCalendarService;
use App\Models\User;

class GoogleController extends Controller
{
    private GoogleCalendarService $googleService;

    public function __construct(GoogleCalendarService $googleService)
    {
        $this->googleService = $googleService;
    }

    /**
     * Iniciar autorización OAuth para Google Calendar
     */
    public function auth(Request $request): RedirectResponse
    {
        $email = $request->query('email');

        if (!$email) {
            return redirect()->back()->with('error', 'Email requerido para autorización');
        }

        try {
            Log::info("Iniciando autorización OAuth para usuario: {$email}");

            $client = new Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setRedirectUri(config('services.google.redirect'));
            $client->setScopes(config('services.google.scopes'));
            $client->setAccessType('offline');
            $client->setPrompt('consent');
            $client->setIncludeGrantedScopes(true);

            // Guardar email en sesión para usarlo en el callback
            session(['google_auth_email' => $email]);

            $authUrl = $client->createAuthUrl();

            Log::info("URL de autorización generada para usuario: {$email}", ['url' => $authUrl]);
            return redirect($authUrl);

        } catch (\Exception $e) {
            Log::error("Error iniciando autorización para usuario {$email}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error iniciando autorización: ' . $e->getMessage());
        }
    }

    /**
     * Manejar callback de autorización OAuth
     * recibir el callback y guardar tokens
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $code = $request->query('code');
            $email = session('google_auth_email');

            if (!$code || !$email) {
                Log::error("Callback sin código o email: code={$code}, email={$email}");
                return redirect()->route('home')->with('error', 'Error en la autorización: datos incompletos');
            }

            Log::info("Callback recibido para usuario: {$email}");

            $client = new Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setRedirectUri(config('services.google.redirect'));
            $client->setScopes(config('services.google.scopes'));
            $client->setAccessType('offline');
            $client->setPrompt('consent');

            // Intercambiar código por tokens
            $accessToken = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($accessToken['error'])) {
                Log::error("Error obteniendo token para usuario {$email}: " . json_encode($accessToken));
                return redirect()->route('home')->with('error', 'Error obteniendo token: ' . ($accessToken['error_description'] ?? $accessToken['error']));
            }

            // Guardar tokens en la base de datos (usuario debe existir)
            $user = User::where('email', $email)->firstOrFail();
            $user->update([
                'google_access_token'  => $accessToken['access_token'] ?? $user->google_access_token,
                'google_refresh_token' => $accessToken['refresh_token'] ?? $user->google_refresh_token,
                'token_expires_at'     => isset($accessToken['expires_in']) ? now()->addSeconds($accessToken['expires_in']) : $user->token_expires_at,
            ]);

            Log::info("Token guardado en DB para usuario {$email}", [
                'scope' => $accessToken['scope'] ?? 'No especificado',
                'expires_in' => $accessToken['expires_in'] ?? 'No especificado',
                'has_refresh_token' => isset($accessToken['refresh_token']),
                'token_type' => $accessToken['token_type'] ?? 'No especificado',
            ]);

            session()->forget('google_auth_email');

            return redirect()->route('home')->with('success', 'Autorización de Google Calendar completada exitosamente');

        } catch (\Exception $e) {
            Log::error("Error en callback de Google Calendar: " . $e->getMessage());
            return redirect()->route('home')->with('error', 'Error en la autorización: ' . $e->getMessage());
        }
    }

    /**
     * Revocar acceso de Google Calendar
     */
    public function revoke(Request $request): RedirectResponse
    {
        $email = $request->query('email');

        if (!$email) {
            return redirect()->back()->with('error', 'Email requerido');
        }

        try {
            $user = User::where('email', $email)->firstOrFail();
            $user->update([
                'google_access_token'  => null,
                'google_refresh_token' => null,
                'token_expires_at'     => null,
            ]);

            Log::info("Acceso de Google Calendar revocado para usuario: {$email}");

            return redirect()->route('home')->with('success', 'Acceso de Google Calendar revocado correctamente');

        } catch (\Exception $e) {
            Log::error("Error revocando acceso para usuario {$email}: " . $e->getMessage());
            return redirect()->route('home')->with('error', 'Error revocando acceso: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar información del token
     */
    public function tokenInfo(Request $request): \Illuminate\Http\JsonResponse
    {
        $email = $request->query('email');

        if (!$email) {
            return response()->json(['error' => 'Email requerido'], 400);
        }

        try {
            $user = User::where('email', $email)->firstOrFail();

            if (!$user->google_access_token) {
                return response()->json(['error' => 'El usuario no tiene token de Google guardado'], 404);
            }

            $tokenInfo = [
                'access_token'  => $user->google_access_token,
                'refresh_token' => $user->google_refresh_token,
                'has_refresh'   => (bool) $user->google_refresh_token,
                'expires_at'    => $user->token_expires_at,
            ];

            return response()->json($tokenInfo);

        } catch (\Exception $e) {
            Log::error("Error obteniendo información del token para usuario {$email}: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener eventos del calendario de Google + reservas locales
     */
    public function getCalendarEvents(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();
            $email = $user->email;

            $startDate = $request->get('start_date');
            $endDate   = $request->get('end_date');
            $status    = $request->get('status');
            $type      = $request->get('type');
            $search    = $request->get('search');

            Log::info("Solicitando eventos para usuario {$email} desde {$startDate} hasta {$endDate}");

            $googleEvents = [];
            try {
                $googleEvents = $this->googleService->getEvents($email, $startDate, $endDate);
            } catch (\Exception $e) {
                Log::warning("No se pudieron obtener eventos de Google Calendar: " . $e->getMessage());
            }

            // Reservas locales
            $q = \App\Models\Reservation::query()
                ->whereBetween('start_date', [$startDate, $endDate]);

            if ($user->role !== 'admin') {
                $q->where('user_id', $user->id);
            }

            $q->when($status, fn($qq) => $qq->where('status', $status))
              ->when($type, fn($qq) => $qq->where('type', $type))
              ->when($search, function ($qq) use ($search) {
                  $qq->where(function ($w) use ($search) {
                      $w->where('title','like',"%{$search}%")
                        ->orWhere('description','like',"%{$search}%")
                        ->orWhere('location','like',"%{$search}%")
                        ->orWhere('customer_name','like',"%{$search}%")
                        ->orWhere('customer_email','like',"%{$search}%");
                  });
              });

            $localReservations = $q->get()->map(function($reservation) {
                return [
                    'id' => 'local_' . $reservation->id,
                    'title' => $reservation->title,
                    'start' => $reservation->start_date->toISOString(),
                    'end'   => $reservation->end_date->toISOString(),
                    'description' => $reservation->description,
                    'location'    => $reservation->location,
                    'backgroundColor' => '#10B981',
                    'borderColor'     => '#059669',
                    'textColor'       => '#FFFFFF',
                    'extendedProps' => [
                        'type'           => 'local_reservation',
                        'reservation_id' => $reservation->id,
                        'status'         => $reservation->status,
                        'category'       => $reservation->type,
                    ]
                ];
            });

            $allEvents = array_merge($googleEvents, $localReservations->toArray());

            Log::info("Eventos obtenidos para usuario {$email}: Google=" . count($googleEvents) . ", Locales=" . count($localReservations) . ", Total=" . count($allEvents));

            return response()->json([
                'success'      => true,
                'events'       => $allEvents,
                'count'        => count($allEvents),
                'google_count' => count($googleEvents),
                'local_count'  => count($localReservations)
            ]);

        } catch (\Exception $e) {
            Log::error("Error obteniendo eventos del calendario: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo eventos: ' . $e->getMessage(),
                'events'  => []
            ], 500);
        }
    }
}
