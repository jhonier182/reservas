<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Events\MensajeEnviado;
use Google\Client;


class GoogleController extends Controller
{
    
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }


     public function enviarMensaje(Request $request)
{
    $mensaje = $request->input('mensaje');

    // Evitar errores si no hay mensaje
    if (!empty($mensaje)) {
        event(new MensajeEnviado($mensaje));
        return response()->json(['status' => 'Mensaje enviado correctamente']);
    }

    // Si no hay mensaje, simplemente no hace nada y devuelve respuesta vacía
    return response()->json(['status' => 'Sin mensaje para enviar']);
}

    public function home()
{
    // Revisar sesión o usuario autenticado
    $usuario = session('usuario', Auth::user());

    if (!$usuario) {
        return redirect('/auth/google');
    }

    return view('home', compact('usuario'));
}



    public function handleGoogleCallback()
{
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
        ['name' => $googleUser->name, 'google_id' => $googleUser->id]

    );

    // Guardar en sesión
    session([
    'usuario' => [
        'name' => $googleUser->name,
        'email' => $googleUser->email,
        'avatar' => $googleUser->avatar // 👈 Foto de perfil
    ]
]);

    // Iniciar sesión
    Auth::login($user);

    return redirect()->route('home');
}

}


