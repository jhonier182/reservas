<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $adminEmails = array_map('strtolower', config('admin.emails', []));
        $isAdminEmail = in_array(strtolower($user->email), $adminEmails);
        $isAdminRole  = ($user->role ?? null) === 'admin';

        if (!($isAdminEmail || $isAdminRole)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Forbidden'], 403)
                : redirect()->route('no-autorizado');
        }
        return $next($request);
    }
}
