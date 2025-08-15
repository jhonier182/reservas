<?php

namespace App\Services;

use App\Models\User;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\CalendarList;
use Google\Service\Calendar\Events;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GoogleCalendarService
{
    private string $credentialsPath;
    private array $scopes;
    private string $redirectUri;
    private ?string $impersonateSubject;

    public function __construct()
    {
        $this->credentialsPath = config('google.credentials_path');
        $this->scopes = config('google.scopes');
        $this->redirectUri = config('google.redirect_uri');
        $this->impersonateSubject = config('google.impersonate_subject');
    }

    /**
     * Obtener cliente Google configurado para un usuario específico
     */
    public function getClientForUser(string $email): Client
    {
        try {
            Log::info("Configurando cliente Google para usuario: {$email}");
            
            $client = new Client();
            
            // Configurar credenciales
            if (file_exists($this->credentialsPath)) {
                $client->setAuthConfig($this->credentialsPath);
                Log::info("Credenciales de Service Account cargadas");
                
                // Si hay subject para impersonar, configurarlo
                if ($this->impersonateSubject) {
                    $client->setSubject($this->impersonateSubject);
                    Log::info("Impersonando usuario: {$this->impersonateSubject}");
                }
            } else {
                // Fallback a OAuth de usuario
                $client->setClientId(config('services.google.client_id'));
                $client->setClientSecret(config('services.google.client_secret'));
                $client->setRedirectUri($this->redirectUri);
                Log::info("Configuración OAuth de usuario cargada");
            }
            
            // Configurar scopes y parámetros OAuth
            $client->setScopes($this->scopes);
            $client->setAccessType(config('google.access_type'));
            $client->setPrompt(config('google.prompt'));
            $client->setIncludeGrantedScopes(config('google.include_granted_scopes'));
            
            Log::info("Scopes configurados: " . implode(', ', $client->getScopes()));
            
            // Cargar token del usuario si existe
            $tokenPath = $this->getTokenPath($email);
            if (file_exists($tokenPath)) {
                $tokenData = json_decode(file_get_contents($tokenPath), true);
                $client->setAccessToken($tokenData);
                
                // Verificar si el token expiró
                if ($client->isAccessTokenExpired()) {
                    Log::info("Token expirado para usuario: {$email}");
                    
                    if (isset($tokenData['refresh_token'])) {
                        $client->refreshToken($tokenData['refresh_token']);
                        $newToken = $client->getAccessToken();
                        
                        // Guardar nuevo token
                        $this->saveToken($email, $newToken);
                        Log::info("Token refrescado para usuario: {$email}");
                    } else {
                        throw new \Exception("Token expirado y no hay refresh token disponible para usuario: {$email}");
                    }
                }
                
                Log::info("Token cargado para usuario: {$email}");
            } else {
                Log::info("No hay token guardado para usuario: {$email}");
            }
            
            return $client;
            
        } catch (\Exception $e) {
            Log::error("Error configurando cliente Google para usuario {$email}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Listar calendarios del usuario
     */
    public function listCalendars(string $email): array
    {
        try {
            $client = $this->getClientForUser($email);
            $service = new Calendar($client);
            
            $calendarList = $service->calendarList->listCalendarList();
            
            $calendars = [];
            foreach ($calendarList->getItems() as $calendar) {
                $calendars[] = [
                    'id' => $calendar->getId(),
                    'summary' => $calendar->getSummary(),
                    'primary' => $calendar->getPrimary() ?? false,
                    'accessRole' => $calendar->getAccessRole() ?? 'reader',
                ];
            }
            
            Log::info("Calendarios listados para usuario {$email}: " . count($calendars));
            return $calendars;
            
        } catch (\Exception $e) {
            Log::error("Error listando calendarios para usuario {$email}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener calendario primario
     */
    public function getPrimaryCalendar(string $email): array
    {
        try {
            $client = $this->getClientForUser($email);
            $service = new Calendar($client);
            
            $calendar = $service->calendars->get('primary');
            
            $result = [
                'id' => $calendar->getId(),
                'summary' => $calendar->getSummary(),
                'description' => $calendar->getDescription() ?? '',
                'timeZone' => $calendar->getTimeZone() ?? 'UTC',
                'accessRole' => 'owner', // Por defecto para calendario primario
            ];
            
            Log::info("Calendario primario obtenido para usuario {$email}: {$result['summary']}");
            return $result;
            
        } catch (\Exception $e) {
            Log::error("Error obteniendo calendario primario para usuario {$email}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Listar eventos del calendario primario
     */
    public function listPrimaryEvents(string $email, array $params = []): array
    {
        try {
            $client = $this->getClientForUser($email);
            $service = new Calendar($client);
            
            // Parámetros por defecto
            $defaultParams = [
                'timeMin' => Carbon::now()->subDays(7)->toRfc3339String(),
                'timeMax' => Carbon::now()->addDays(30)->toRfc3339String(),
                'singleEvents' => true,
                'orderBy' => 'startTime',
                'maxResults' => 50,
            ];
            
            $params = array_merge($defaultParams, $params);
            
            $events = $service->events->listEvents('primary', $params);
            
            $eventList = [];
            foreach ($events->getItems() as $event) {
                $eventList[] = [
                    'id' => $event->getId(),
                    'summary' => $event->getSummary(),
                    'description' => $event->getDescription(),
                    'start' => $event->getStart(),
                    'end' => $event->getEnd(),
                    'location' => $event->getLocation(),
                    'attendees' => $event->getAttendees(),
                ];
            }
            
            Log::info("Eventos listados para usuario {$email}: " . count($eventList));
            return $eventList;
            
        } catch (\Exception $e) {
            Log::error("Error listando eventos para usuario {$email}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verificar permisos del usuario
     */
    public function verifyPermissions(string $email): array
    {
        try {
            $client = $this->getClientForUser($email);
            $service = new Calendar($client);
            
            // Intentar obtener el calendario primario para verificar permisos
            $calendar = $service->calendars->get('primary');
            
            $result = [
                'success' => true,
                'message' => 'Permisos verificados correctamente',
                'calendar_summary' => $calendar->getSummary(),
                'access_role' => 'owner', // Por defecto para calendario primario
            ];
            
            Log::info("Permisos verificados para usuario {$email}: {$result['message']}");
            return $result;
            
        } catch (\Exception $e) {
            $error = [
                'success' => false,
                'message' => 'Error verificando permisos',
                'error' => $e->getMessage(),
            ];
            
            // Extraer detalles del error de Google
            if (strpos($e->getMessage(), 'ACCESS_TOKEN_SCOPE_INSUFFICIENT') !== false) {
                $error['reason'] = 'ACCESS_TOKEN_SCOPE_INSUFFICIENT';
                $error['suggestion'] = 'El token no tiene los scopes necesarios. Reautoriza en /google/auth?email=' . $email;
            } elseif (strpos($e->getMessage(), 'PERMISSION_DENIED') !== false) {
                $error['reason'] = 'PERMISSION_DENIED';
                $error['suggestion'] = 'No tienes permisos para acceder al calendario';
            }
            
            Log::error("Error verificando permisos para usuario {$email}: " . $e->getMessage());
            return $error;
        }
    }

    /**
     * Obtener ruta del archivo de token para un usuario
     */
    public function getTokenPath(string $email): string
    {
        $safeEmail = str_replace(['@', '.'], ['_at_', '_dot_'], $email);
        return storage_path("app/google/tokens/{$safeEmail}.json");
    }

    /**
     * Guardar token para un usuario
     */
    public function saveToken(string $email, array $tokenData): void
    {
        $tokenPath = $this->getTokenPath($email);
        $tokenDir = dirname($tokenPath);
        
        if (!is_dir($tokenDir)) {
            mkdir($tokenDir, 0755, true);
        }
        
        file_put_contents($tokenPath, json_encode($tokenData, JSON_PRETTY_PRINT));
        Log::info("Token guardado para usuario {$email}");
    }

    /**
     * Eliminar token de un usuario
     */
    public function removeToken(string $email): bool
    {
        $tokenPath = $this->getTokenPath($email);
        
        if (file_exists($tokenPath)) {
            unlink($tokenPath);
            Log::info("Token eliminado para usuario {$email}");
            return true;
        }
        
        return false;
    }

    /**
     * Obtener información del token actual
     */
    public function getTokenInfo(string $email): array
    {
        $tokenPath = $this->getTokenPath($email);
        
        if (!file_exists($tokenPath)) {
            return [
                'exists' => false,
                'message' => 'No hay token guardado'
            ];
        }
        
        $tokenData = json_decode(file_get_contents($tokenPath), true);
        
        return [
            'exists' => true,
            'scope' => $tokenData['scope'] ?? 'No especificado',
            'created' => $tokenData['created'] ?? 'No especificado',
            'expires_in' => $tokenData['expires_in'] ?? 'No especificado',
            'has_refresh_token' => isset($tokenData['refresh_token']),
        ];
    }
}
