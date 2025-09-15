<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleCalendarService;
use App\Models\User;

class TestGoogleConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:test-connection {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la conexión con Google Calendar usando cuenta de servicio';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Probando conexión con Google Calendar...');
        
        // Verificar archivo de credenciales
        $credentialsPath = storage_path('app/google-credentials.json');
        if (!file_exists($credentialsPath)) {
            $this->error('❌ Archivo de credenciales no encontrado en: ' . $credentialsPath);
            $this->info('📝 Descarga tu archivo JSON de Google Cloud Console y colócalo en: storage/app/google-credentials.json');
            return 1;
        }
        
        $this->info('✅ Archivo de credenciales encontrado');
        
        // dominios permitidos
$allowedDomains = ['@beltcolombia.com', '@belt.com.co', '@beltforge.com'];

$email = $this->argument('email');

if (!$email) {
    // buscar primer usuario con dominio permitido
    $user = User::where(function ($query) use ($allowedDomains) {
        foreach ($allowedDomains as $domain) {
            $query->orWhere('email', 'like', '%' . $domain);
        }
    })->first();

    if (!$user) {
        $this->error('❌ No se encontró usuario con dominios permitidos: ' . implode(', ', $allowedDomains));
        return 1;
    }

    $email = $user->email;
} else {
    $user = User::where('email', $email)->first();
    if (!$user) {
        $this->error('❌ Usuario no encontrado: ' . $email);
        return 1;
    }
}

        
        $this->info('👤 Probando con usuario: ' . $email);
        
        try {
            $googleService = new GoogleCalendarService();
            
            // Probar permisos
            $this->info('🔐 Verificando permisos...');
            $hasPermissions = $googleService->checkCalendarPermissions($user);
            
            if ($hasPermissions) {
                $this->info('✅ Permisos verificados correctamente');
                
                // Probar obtención de eventos
                $this->info('📅 Obteniendo eventos de Google Calendar...');
                $events = $googleService->getEvents($user, 'primary', now()->subDays(7), now()->addDays(7));
                
                $this->info('✅ Eventos obtenidos: ' . $events->count());
                
                if ($events->count() > 0) {
                    $this->info('📋 Primeros 5 eventos:');
                    $events->take(5)->each(function ($event) {
                        $this->line('  • ' . $event['title'] . ' (' . $event['start']->format('Y-m-d H:i') . ')');
                    });
                }
                
            } else {
                $this->error('❌ No se pudieron verificar los permisos');
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('📋 Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
        
        $this->info('🎉 ¡Conexión exitosa con Google Calendar!');
        return 0;
    }
}
