<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Google\Client as GoogleClient;
use Google\Service\Oauth2 as GoogleOauth2;
use Google\Service\Calendar as GoogleCalendarApi;
use Illuminate\Support\Facades\Log;

class GoogleAuthService
{
    /**
     * Validar si el dominio del email está permitido
     */
    public function validateDomain(string $email): bool
    {
        $allowedDomains = config('services.google.allowed_domains', ['gmail.com']);
        $domain = substr(strrchr($email, "@"), 1);

        return in_array($domain, $allowedDomains);
    }

    /**
     * Crear o actualizar usuario desde datos de Google
     */
    public function createOrUpdateUser(array $googleUser): User
    {
        // Buscar por google_id primero, luego por email
        $user = User::withTrashed()
            ->where('google_id', $googleUser['id'])
            ->orWhere('email', $googleUser['email'])
            ->first();

        $isAdminEmail = in_array(strtolower($googleUser['email']), array_map('strtolower', config('admin.emails', [])));

        // Preparar payload a guardar/actualizar
        $payload = [
            'google_id'           => $googleUser['id'],
            'name'                => $googleUser['name'] ?? $googleUser['email'],
            'avatar'              => $googleUser['picture'] ?? null,
            'google_access_token' => $googleUser['access_token'] ?? null,
            'token_expires_at'    => $googleUser['expires_at'] ?? null,
            'timezone'            => $googleUser['timezone'] ?? 'America/Bogota',
        ];

        // Incluir refresh_token solo si viene
        if (!empty($googleUser['refresh_token'])) {
            $payload['google_refresh_token'] = $googleUser['refresh_token'];
        }

        if ($user) {
            // Restaurar si estaba borrado
            if (method_exists($user, 'restore') && $user->trashed()) {
                $user->restore();
            }

            // Si no vino refresh_token nuevo, mantener el anterior
            if (empty($payload['google_refresh_token']) && isset($user->google_refresh_token)) {
                unset($payload['google_refresh_token']);
            }

            // No sobreescribir role si ya es admin, salvo que sea un adminEmail
            if ($isAdminEmail && $user->role !== 'admin') {
                $user->role = 'admin';
                $user->save();
            }

            $user->update($payload);
        } else {
            $user = User::create([
                'name'                 => $payload['name'],
                'email'                => $googleUser['email'],
                'google_id'            => $payload['google_id'],
                'avatar'               => $payload['avatar'],
                'password'             => Hash::make(Str::random(32)),
                'google_access_token'  => $payload['google_access_token'],
                'google_refresh_token' => $payload['google_refresh_token'] ?? null,
                'token_expires_at'     => $payload['token_expires_at'],
                'timezone'             => $payload['timezone'],
                'role'                 => $isAdminEmail ? 'admin' : 'user',
            ]);
        }

        // (Opcional) crear datos relacionados (calendarios) si tu modelo lo requiere
        try {
            if (method_exists($user, 'calendars') && $user->calendars()->count() === 0) {
                $user->calendars()->create([
                    'name'       => 'Calendario Principal',
                    'is_primary' => true,
                    'color'      => '#4285f4',
                    'timezone'   => $user->timezone,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning("No se pudo crear calendario relacionado: " . $e->getMessage());
        }

        return $user;
    }

    /**
     * Manejar callback de autenticación de Google
     * Devuelve el User creado/actualizado
     */
    public function handleCallback(string $code): User
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->addScope(GoogleCalendarApi::CALENDAR);

        // Intercambiar code por token
        $token = $client->fetchAccessTokenWithAuthCode($code);

        // Obtener datos del usuario
        $oauth = new GoogleOauth2($client);
        $googleUser = $oauth->userinfo->get();

        // Preparar payload para crear/actualizar usuario
        $userData = [
            'id'            => $googleUser->id,
            'email'         => $googleUser->email,
            'name'          => $googleUser->name,
            'picture'       => $googleUser->picture ?? null,
            'access_token'  => $token['access_token'] ?? null,
            'refresh_token' => $token['refresh_token'] ?? null,
            'expires_at'    => isset($token['expires_in']) ? now()->addSeconds($token['expires_in']) : null,
        ];

        // No intentamos guardar en archivo aquí — guardamos en BD dentro de createOrUpdateUser
        return $this->createOrUpdateUser($userData);
    }

    /**
     * Refrescar token de Google
     */
    public function refreshToken(User $user): bool
    {
        if (!$user->google_refresh_token) {
            return false;
        }

        try {
            $client = new GoogleClient();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->refreshToken($user->google_refresh_token);

            // Optionally fetch new token and save — simplified here
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Revocar acceso de Google
     */
    public function revokeAccess(User $user): bool
    {
        try {
            $user->update([
                'google_access_token' => null,
                'google_refresh_token' => null,
                'token_expires_at' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
