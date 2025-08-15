<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Google\Client;
use App\Services\GoogleCalendarService;

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
            $client->setRedirectUri(config('google.redirect_uri'));
            $client->setScopes(config('google.scopes'));
            $client->setAccessType(config('google.access_type'));
            $client->setPrompt(config('google.prompt'));
            $client->setIncludeGrantedScopes(config('google.include_granted_scopes'));
            
            // Guardar email en sesión para el callback
            session(['google_auth_email' => $email]);
            
            $authUrl = $client->createAuthUrl();
            
            Log::info("URL de autorización generada para usuario: {$email}");
            return redirect($authUrl);
            
        } catch (\Exception $e) {
            Log::error("Error iniciando autorización para usuario {$email}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error iniciando autorización: ' . $e->getMessage());
        }
    }

    /**
     * Manejar callback de autorización OAuth
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
            $client->setRedirectUri(config('google.redirect_uri'));
            $client->setScopes(config('google.scopes'));
            
            // Intercambiar código por tokens
            $accessToken = $client->fetchAccessTokenWithAuthCode($code);
            
            if (isset($accessToken['error'])) {
                Log::error("Error obteniendo token para usuario {$email}: " . json_encode($accessToken));
                return redirect()->route('home')->with('error', 'Error obteniendo token: ' . $accessToken['error_description'] ?? $accessToken['error']);
            }
            
            // Guardar token completo (incluye scope, refresh_token, etc.)
            $this->googleService->saveToken($email, $accessToken);
            
            // Loggear información del token para auditoría
            Log::info("Token obtenido para usuario {$email}", [
                'scope' => $accessToken['scope'] ?? 'No especificado',
                'expires_in' => $accessToken['expires_in'] ?? 'No especificado',
                'has_refresh_token' => isset($accessToken['refresh_token']),
                'token_type' => $accessToken['token_type'] ?? 'No especificado',
            ]);
            
            // Limpiar sesión
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
            $this->googleService->removeToken($email);
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
            $tokenInfo = $this->googleService->getTokenInfo($email);
            return response()->json($tokenInfo);
            
        } catch (\Exception $e) {
            Log::error("Error obteniendo información del token para usuario {$email}: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener eventos del calendario de Google
     */
    public function getCalendarEvents(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Obtener email del usuario autenticado
            $email = auth()->user()->email;
            
            // Obtener fechas de la petición
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            
            Log::info("Solicitando eventos para usuario {$email} desde {$startDate} hasta {$endDate}");
            
            // Obtener eventos usando el servicio
            $events = $this->googleService->getEvents($email, $startDate, $endDate);
            
            Log::info("Eventos obtenidos para usuario {$email}: " . count($events));
            
            return response()->json([
                'success' => true,
                'events' => $events,
                'count' => count($events)
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error obteniendo eventos del calendario: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo eventos: ' . $e->getMessage(),
                'events' => []
            ], 500);
        }
    }
}


