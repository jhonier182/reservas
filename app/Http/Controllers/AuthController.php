<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Services\GoogleAuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected GoogleAuthService $googleAuthService;

    public function __construct(GoogleAuthService $googleAuthService)
    {
        $this->googleAuthService = $googleAuthService;
    }

    /**
     * Redirigir al usuario a Google para autenticación
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes([
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.events',
                'https://www.googleapis.com/auth/calendar.readonly'
            ])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    /**
     * Manejar el callback de autenticación de Google
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            \Log::info('Callback de Google iniciado');
            
            $googleUser = Socialite::driver('google')->stateless()->user();
            \Log::info('Usuario de Google obtenido: ' . $googleUser->email);
            
            $email = $googleUser->email;

            // Validar dominio
            $allowedDomains = config('admin.allowed_domains', ['@beltcolombia.com', '@belt.com.co', '@beltforge.com', '@belforge.com']);
            $isAllowed = false;
            foreach ($allowedDomains as $domain) {
                if (str_ends_with($email, $domain)) {
                    $isAllowed = true;
                    break;
                }
            }
            
            if (!$isAllowed) {
                return redirect()->route('no-autorizado');
            }

            // Buscar o crear usuario
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $googleUser->name, 
                    'google_id' => $googleUser->id,
                    'google_access_token' => $googleUser->token ?? null,
                    'google_refresh_token' => $googleUser->refreshToken ?? null,
                    'password' => bcrypt(Str::random(16)), // Contraseña aleatoria para usuarios de Google
                    'role' => 'user', // Rol por defecto
                ]
            );
            
            // Si es el correo específico del admin, asignar rol de admin
            $adminEmails = config('admin.admin_emails', ['admin@tuapp.com']);
            if (in_array($email, $adminEmails)) {
                $user->update(['role' => 'admin']);
                \Log::info('Usuario admin asignado: ' . $email);
            }
            
            // Actualizar tokens siempre (tanto para usuarios nuevos como existentes)
            $user->update([
                'google_id' => $googleUser->id,
                'google_access_token' => $googleUser->token ?? null,
                'google_refresh_token' => $googleUser->refreshToken ?? null,
            ]);
            
            \Log::info('Usuario encontrado/creado: ' . $user->email);
            \Log::info('Rol del usuario: ' . $user->role);
            \Log::info('Token de acceso: ' . ($googleUser->token ? 'SÍ' : 'NO'));
            \Log::info('Refresh token: ' . ($googleUser->refreshToken ? 'SÍ' : 'NO'));
            \Log::info('Tokens actualizados en base de datos');

            // Iniciar sesión
            Auth::login($user);

            // Redirigir al dashboard
            return redirect()->route('home')->with('success', '¡Bienvenido! Has iniciado sesión correctamente.');
            
        } catch (\Exception $e) {
            \Log::error('Error en callback de Google: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Error en la autenticación: ' . $e->getMessage());
        }
    }

    /**
     * Manejar login tradicional (email/password)
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('home'))->with('success', '¡Bienvenido!');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->withInput($request->only('email'));
    }

    /**
     * Cerrar sesión del usuario
     */
    public function logout(): RedirectResponse
    {
        Auth::logout();
        Session::flush();
        
        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente');
    }

    /**
     * Mostrar formulario de login
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        
        return view('auth.login');
    }

    /**
     * Revocar acceso de Google
     */
    public function revokeGoogleAccess(): RedirectResponse
    {
        try {
            $user = Auth::user();
            
            if ($this->googleAuthService->revokeAccess($user)) {
                return redirect()->route('home')->with('success', 'Acceso de Google revocado correctamente');
            }
            
            return redirect()->route('home')->with('error', 'Error al revocar acceso de Google');
            
        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
