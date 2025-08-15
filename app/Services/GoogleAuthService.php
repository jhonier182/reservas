<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $user = User::where('google_id', $googleUser['id'])->first();

        if (!$user) {
            $user = User::where('email', $googleUser['email'])->first();
        }

        if ($user) {
            // Actualizar usuario existente
            $user->update([
                'google_id' => $googleUser['id'],
                'name' => $googleUser['name'],
                'avatar' => $googleUser['picture'] ?? null,
                'google_access_token' => $googleUser['access_token'] ?? null,
                'google_refresh_token' => $googleUser['refresh_token'] ?? null,
                'token_expires_at' => $googleUser['expires_at'] ?? null,
            ]);
        } else {
            // Crear nuevo usuario
            $user = User::create([
                'name' => $googleUser['name'],
                'email' => $googleUser['email'],
                'google_id' => $googleUser['id'],
                'avatar' => $googleUser['picture'] ?? null,
                'password' => Hash::make(Str::random(16)),
                'google_access_token' => $googleUser['access_token'] ?? null,
                'google_refresh_token' => $googleUser['refresh_token'] ?? null,
                'token_expires_at' => $googleUser['expires_at'] ?? null,
                'timezone' => $googleUser['timezone'] ?? 'UTC',
            ]);

            // Crear calendario principal por defecto
            $user->calendars()->create([
                'name' => 'Calendario Principal',
                'is_primary' => true,
                'color' => '#4285f4',
                'timezone' => $user->timezone,
            ]);
        }

        return $user;
    }

    /**
     * Manejar callback de autenticación de Google
     */
    public function handleCallback(string $code): User
    {
        // Aquí se implementará la lógica para intercambiar el código por tokens
        // Por ahora retornamos un usuario de ejemplo
        return User::first();
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
            // Aquí se implementará la lógica para refrescar el token
            // Por ahora retornamos true
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
