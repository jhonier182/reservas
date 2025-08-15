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
        return Socialite::driver('google')->redirect();
    }

    /**
     * Manejar el callback de autenticación de Google
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            $email = $googleUser->email;

            // Validar dominio
            $allowedDomains = ['@beltcolombia.com', '@belt.com.co', '@beltforge.com'];
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
                ]
            );

            // Iniciar sesión
            Auth::login($user);

            // Redirigir al dashboard
            return redirect()->route('home')->with('success', '¡Bienvenido! Has iniciado sesión correctamente.');
            
        } catch (\Exception $e) {
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
    public function showLoginForm(): View
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
