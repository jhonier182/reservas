<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Ajusta esta condición a tu implementación de roles
        if (!$user || ($user->role ?? null) !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No tienes permisos de administrador.'], 403);
            }
            abort(403, 'No tienes permisos de administrador.');
        }

        return $next($request);
    }
}
