@extends('layouts.app')

@section('title', 'Mi Perfil - Reservas')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header del Perfil -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Mi Perfil
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Gestiona tu información personal y preferencias
                    </p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('home') }}" class="btn-secondary">
                        <i class="fas fa-home mr-2"></i>Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido del Perfil -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Información del Usuario -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Información Personal</h3>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" value="{{ $user->name }}" class="form-input" readonly>
                        </div>
                        
                        <div>
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" value="{{ $user->email }}" class="form-input" readonly>
                        </div>
                        
                        <div>
                            <label class="form-label">Fecha de Registro</label>
                            <input type="text" value="{{ $user->created_at->format('d/m/Y H:i') }}" class="form-input" readonly>
                        </div>
                        
                        @if($user->google_id)
                        <div>
                            <label class="form-label">Estado de Google</label>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>Conectado
                                </span>
                                <span class="text-sm text-gray-500">ID: {{ $user->google_id }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="space-y-6">
                <!-- Avatar -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 text-center">
                    @if($user->avatar)
                        <img class="w-24 h-24 rounded-full mx-auto mb-4" src="{{ $user->avatar }}" alt="Avatar">
                    @else
                        <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-white text-3xl font-bold">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <h4 class="font-semibold text-gray-900">{{ $user->name }}</h4>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                </div>

                <!-- Acciones Rápidas -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Acciones Rápidas</h4>
                    <div class="space-y-3">
                        <a href="{{ route('reservations.create') }}" class="block w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>Nueva Reserva
                        </a>
                        <a href="{{ route('calendar') }}" class="block w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                            <i class="fas fa-calendar mr-2"></i>Ver Calendario
                        </a>
                        <a href="{{ route('google.auth') }}?email={{ Auth::user()->email }}" class="block w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                            <i class="fas fa-sync mr-2"></i>Sincronizar Google
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
