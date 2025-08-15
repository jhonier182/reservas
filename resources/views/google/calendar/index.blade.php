@extends('layouts.app')

@section('title', 'Sincronización Google Calendar - TodoList')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Google Calendar
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Sincroniza tu agenda con Google Calendar
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

    <!-- Contenido Principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Panel Principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Estado de Sincronización -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Estado de Sincronización
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $syncStats['total_events'] }}</div>
                            <div class="text-sm text-gray-600">Total de Eventos</div>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ $syncStats['synced_events'] }}</div>
                            <div class="text-sm text-gray-600">Eventos Sincronizados</div>
                        </div>
                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">
                                @if($syncStats['last_sync'])
                                    {{ \Carbon\Carbon::parse($syncStats['last_sync'])->diffForHumans() }}
                                @else
                                    Nunca
                                @endif
                            </div>
                            <div class="text-sm text-gray-600">Última Sincronización</div>
                        </div>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" class="btn-primary" onclick="syncEvents()">
                            <i class="fas fa-sync mr-2"></i>Sincronizar Eventos
                        </button>
                        <button type="button" class="btn-secondary" onclick="checkStatus()">
                            <i class="fas fa-info-circle mr-2"></i>Verificar Estado
                        </button>
                    </div>
                </div>

                <!-- Permisos de Google -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Permisos de Google Calendar
                    </h3>
                    
                    @if($hasPermissions)
                        <div class="flex items-center space-x-3 p-4 bg-green-50 rounded-lg">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            <div>
                                <div class="font-medium text-green-800">Conectado a Google Calendar</div>
                                <div class="text-sm text-green-600">Tienes permisos para sincronizar eventos</div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center space-x-3 p-4 bg-yellow-50 rounded-lg">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                            <div>
                                <div class="font-medium text-yellow-800">No conectado a Google Calendar</div>
                                <div class="text-sm text-yellow-600">Necesitas autorizar el acceso a tu calendario</div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="{{ route('auth.google') }}" class="btn-primary">
                                <i class="fab fa-google mr-2"></i>Conectar con Google
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="space-y-6">
                <!-- Acciones Rápidas -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Acciones Rápidas</h4>
                    <div class="space-y-3">
                        <button type="button" onclick="syncCalendars()" class="block w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                            <i class="fas fa-calendar-plus mr-2"></i>Sincronizar Calendarios
                        </button>
                        <a href="{{ route('reservations.create') }}" class="block w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>Nueva Reserva
                        </a>
                        <a href="{{ route('calendar') }}" class="block w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                            <i class="fas fa-calendar mr-2"></i>Ver Calendario
                        </a>
                    </div>
                </div>

                <!-- Información del Usuario -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Información del Usuario</h4>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                            </div>
                        </div>
                        
                        @if($user->google_id)
                            <div class="flex items-center space-x-2 text-sm text-gray-600">
                                <i class="fab fa-google text-green-600"></i>
                                <span>Conectado a Google</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Función para sincronizar eventos
    function syncEvents() {
        // Aquí iría la lógica de sincronización
        alert('Función de sincronización en desarrollo');
    }

    // Función para verificar estado
    function checkStatus() {
        // Aquí iría la lógica de verificación
        alert('Función de verificación en desarrollo');
    }

    // Función para sincronizar calendarios
    function syncCalendars() {
        // Aquí iría la lógica de sincronización de calendarios
        alert('Función de sincronización de calendarios en desarrollo');
    }
</script>
@endpush
@endsection
