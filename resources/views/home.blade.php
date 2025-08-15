@extends('layouts.app')

@section('title', 'Inicio - TodoList')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header del Dashboard -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        ¡Bienvenido, {{ auth()->user()->name }}!
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Aquí tienes un resumen de tus actividades y reservas
                    </p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('reservations.create') }}" class="btn-primary">
                        <i class="fas fa-plus mr-2"></i>Nueva Reserva
                    </a>
                    <a href="{{ route('calendar') }}" class="btn-secondary">
                        <i class="fas fa-calendar mr-2"></i>Ver Calendario
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Estadísticas rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Reservas Activas</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $activeReservations ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Completadas</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $completedReservations ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pendientes</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $pendingReservations ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Eventos Hoy</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $todayEvents ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido en dos columnas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Próximas Reservas -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Próximas Reservas</h3>
                </div>
                <div class="p-6">
                    @if(isset($upcomingReservations) && count($upcomingReservations) > 0)
                        <div class="space-y-4">
                            @foreach($upcomingReservations as $reservation)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-calendar text-blue-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $reservation->title }}</p>
                                            <p class="text-sm text-gray-500">{{ $reservation->start_time->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('reservations.show', $reservation) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                        Ver detalles
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-calendar-plus text-gray-400 text-xl"></i>
                            </div>
                            <p class="text-gray-500">No tienes reservas próximas</p>
                            <a href="{{ route('reservations.create') }}" class="mt-2 inline-flex items-center text-blue-600 hover:text-blue-700 text-sm font-medium">
                                Crear primera reserva
                                <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actividad Reciente -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Actividad Reciente</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-check text-green-600 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">Reserva completada</p>
                                <p class="text-sm text-gray-500">Reunión de equipo - hace 2 horas</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-plus text-blue-600 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">Nueva reserva creada</p>
                                <p class="text-sm text-gray-500">Presentación cliente - hace 1 día</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-sync text-purple-600 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">Calendario sincronizado</p>
                                <p class="text-sm text-gray-500">Google Calendar - hace 2 días</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones Rápidas</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('reservations.create') }}" class="flex items-center p-4 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors duration-200">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-plus text-blue-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Nueva Reserva</p>
                        <p class="text-sm text-gray-500">Crear una nueva reserva</p>
                    </div>
                </a>
                
                <a href="{{ route('calendar') }}" class="flex items-center p-4 bg-green-50 rounded-xl hover:bg-green-100 transition-colors duration-200">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-calendar text-green-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Ver Calendario</p>
                        <p class="text-sm text-gray-500">Gestionar tu agenda</p>
                    </div>
                </a>
                
                <a href="{{ route('google.auth') }}?email={{ Auth::user()->email }}" class="flex items-center p-4 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors duration-200">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-sync text-purple-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Sincronizar</p>
                        <p class="text-sm text-gray-500">Con Google Calendar</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
