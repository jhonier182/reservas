<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Log;

class CalendarTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendar:test {--user= : Email del usuario a probar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar conexión y permisos de Google Calendar para un usuario específico';

    /**
     * Execute the console command.
     */
    public function handle(GoogleCalendarService $googleService): int
    {
        $email = $this->option('user');
        
        if (!$email) {
            $this->error('❌ Debes especificar un usuario con --user="email@dominio.com"');
            return 1;
        }

        $this->info("🔍 Probando conexión con Google Calendar...");
        
        // Verificar archivo de credenciales
        $credentialsPath = config('google.credentials_path');
        if (file_exists($credentialsPath)) {
            $this->info("✅ Archivo de credenciales encontrado");
        } else {
            $this->error("❌ Archivo de credenciales no encontrado en: {$credentialsPath}");
            return 1;
        }

        $this->info("👤 Probando con usuario: {$email}");
        $this->info("🔐 Verificando permisos...");

        try {
            // Verificar permisos
            $permissions = $googleService->verifyPermissions($email);
            
            if (!$permissions['success']) {
                $this->error("❌ No se pudieron verificar los permisos");
                
                if (isset($permissions['reason'])) {
                    $this->error("Motivo: {$permissions['reason']}");
                }
                
                if (isset($permissions['suggestion'])) {
                    $this->warn("Sugerencia: {$permissions['suggestion']}");
                }
                
                // Mostrar información del token actual
                $tokenInfo = $googleService->getTokenInfo($email);
                if ($tokenInfo['exists']) {
                    $this->warn("Scopes del token actual: {$tokenInfo['scope']}");
                } else {
                    $this->warn("Scopes del token actual: ninguno");
                }
                
                $this->warn("Scopes requeridos: " . implode(', ', config('google.scopes')));
                $this->warn("Borra el token y vuelve a autenticar en /google/auth?email={$email}");
                
                return 1;
            }

            $this->info("✅ Permisos OK (scope verificado)");
            
            // Realizar 3 llamadas reales a la API
            $this->info("\n🚀 Realizando llamadas de prueba a la API...");
            
            // 1. calendarList.list
            try {
                $calendars = $googleService->listCalendars($email);
                $this->info("✅ calendarList.list: " . count($calendars) . " calendarios");
                
                foreach ($calendars as $calendar) {
                    $primary = $calendar['primary'] ? ' (PRIMARIO)' : '';
                    $this->line("   • {$calendar['summary']}{$primary} - {$calendar['accessRole']}");
                }
            } catch (\Exception $e) {
                $this->error("❌ calendarList.list falló: " . $e->getMessage());
                $this->logError($e, 'calendarList.list');
            }
            
            // 2. calendars.get('primary')
            try {
                $primaryCalendar = $googleService->getPrimaryCalendar($email);
                $this->info("✅ calendars.get('primary'): {$primaryCalendar['summary']}");
                $this->line("   • Zona horaria: {$primaryCalendar['timeZone']}");
                $this->line("   • Rol de acceso: {$primaryCalendar['accessRole']}");
            } catch (\Exception $e) {
                $this->error("❌ calendars.get('primary') falló: " . $e->getMessage());
                $this->logError($e, 'calendars.get');
            }
            
            // 3. events.list('primary')
            try {
                $events = $googleService->listPrimaryEvents($email, ['maxResults' => 10]);
                $this->info("✅ events.list('primary'): " . count($events) . " eventos encontrados");
                
                if (count($events) > 0) {
                    $firstEvent = $events[0];
                    $this->line("   • Primer evento: {$firstEvent['summary']}");
                    
                    if (isset($firstEvent['start']['dateTime'])) {
                        $startTime = \Carbon\Carbon::parse($firstEvent['start']['dateTime'])->format('Y-m-d H:i');
                        $this->line("   • Inicio: {$startTime}");
                    }
                }
            } catch (\Exception $e) {
                $this->error("❌ events.list('primary') falló: " . $e->getMessage());
                $this->logError($e, 'events.list');
            }
            
            $this->info("\n🎉 ¡Prueba completada exitosamente!");
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error general: " . $e->getMessage());
            $this->logError($e, 'general');
            return 1;
        }
    }
    
    /**
     * Loggear error con detalles para auditoría
     */
    private function logError(\Exception $e, string $method): void
    {
        $errorData = [
            'message' => $e->getMessage(),
            'method' => $method,
            'service' => 'calendar-json.googleapis.com',
        ];
        
        // Extraer detalles específicos de Google si están disponibles
        if (strpos($e->getMessage(), 'ACCESS_TOKEN_SCOPE_INSUFFICIENT') !== false) {
            $errorData['reason'] = 'ACCESS_TOKEN_SCOPE_INSUFFICIENT';
            $errorData['domain'] = 'googleapis.com';
        } elseif (strpos($e->getMessage(), 'PERMISSION_DENIED') !== false) {
            $errorData['reason'] = 'insufficientPermissions';
            $errorData['domain'] = 'global';
        }
        
        Log::error("Error en comando calendar:test", $errorData);
    }
}
