@extends('layouts.app')

@section('title', 'Iniciar Sesión - TodoList')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Fondo animado -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-purple-400/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-tr from-indigo-400/20 to-pink-400/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <div class="max-w-md w-full space-y-8 relative z-10 animate-fade-in-up">
        <!-- Logo y Título -->
        <div class="text-center">
            <div class="mx-auto h-20 w-20 flex items-center justify-center mb-6">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-2xl hover:shadow-blue-500/25 transition-all duration-300 hover:scale-110">
                    <span class="text-white text-2xl font-bold">T</span>
                </div>
            </div>
            <h2 class="text-4xl font-extrabold text-gray-900 mb-3">
                Bienvenido a <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">TodoList</span>
            </h2>
            <p class="text-lg text-gray-600">
                Gestiona tus reservas y calendario de manera eficiente
            </p>
        </div>

        <!-- Formulario -->
        <div class="glass rounded-3xl p-8 shadow-2xl border border-white/20">
            <!-- Botón de Google -->
            <div class="mb-8">
                <a href="{{ route('auth.google') }}" class="w-full btn-secondary group flex items-center justify-center">
                    <svg class="w-5 h-5 mr-3 group-hover:scale-110 transition-transform duration-200" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continuar con Google
                </a>
            </div>

            <!-- Separador -->
            <div class="relative mb-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t-2 border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500 font-medium">
                        O continúa con
                    </span>
                </div>
            </div>

            <!-- Formulario de login tradicional -->
            <form method="POST" action="{{ route('auth.login') }}" class="space-y-6">
                @csrf
                
                @if($errors->any())
                    <div class="rounded-2xl bg-red-50 border-2 border-red-200 p-4">
                        <div class="text-sm text-red-700 font-medium">
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="space-y-4">
                    <div>
                        <label for="email" class="form-label">
                            Correo electrónico
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            class="form-input"
                            placeholder="tu@email.com"
                        >
                    </div>

                    <div>
                        <label for="password" class="form-label">
                            Contraseña
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            class="form-input"
                            placeholder="••••••••"
                        >
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            class="form-checkbox"
                        >
                        <label for="remember" class="ml-2 block text-sm text-gray-700 font-medium">
                            Recordarme
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="#" class="font-semibold text-blue-600 hover:text-blue-700 transition-colors duration-200">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn-primary w-full">
                        Iniciar sesión
                    </button>
                </div>
            </form>

            <div class="text-center pt-4">
                <p class="text-sm text-gray-600">
                    ¿No tienes una cuenta?{' '}
                    <a href="#" class="font-semibold text-blue-600 hover:text-blue-700 transition-colors duration-200">
                        Regístrate aquí
                    </a>
                </p>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="text-center">
            <p class="text-sm text-gray-500">
                Solo usuarios con dominios autorizados pueden acceder
            </p>
            <div class="mt-2 flex justify-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    @beltcolombia.com
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    @belt.com.co
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    @beltforge.com
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
