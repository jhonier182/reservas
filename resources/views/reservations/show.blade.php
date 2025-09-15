@extends('layouts.app')

@section('title', 'Detalles de Reserva - Reservas')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Detalles de Reserva
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Información completa de tu reserva
                    </p>
                </div>
                <div class="flex space-x-3">
                    @can('update', $reservation)
                        <a href="{{ route('reservations.edit', $reservation) }}" class="btn-primary">
                            <i class="fas fa-edit mr-2"></i>Editar
                        </a>
                    @endcan
                    <a href="{{ route('reservations.index') }}" class="btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <!-- Título y Estado -->
            <div class="border-b border-gray-200 pb-6 mb-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $reservation->title }}</h2>
                    <span class="px-3 py-1 rounded-full text-sm font-medium 
                        @if($reservation->status === 'confirmed') bg-green-100 text-green-800
                        @elseif($reservation->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($reservation->status === 'completed') bg-blue-100 text-blue-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst($reservation->status ?? 'pending') }}
                    </span>
                </div>
                <p class="mt-2 text-gray-600">{{ $reservation->description }}</p>
            </div>

            <!-- Detalles de la Reserva -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Información de Fechas -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Información de Fechas</h3>
                    <div class="space-y-4">
                        <div class="flex items-center p-4 bg-blue-50 rounded-lg">
                            <i class="fas fa-calendar text-blue-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-gray-900">Fecha de Inicio</p>
                                <p class="text-gray-600">{{ \Carbon\Carbon::parse($reservation->start_date)->format('d/m/Y g:i A') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-green-50 rounded-lg">
                            <i class="fas fa-clock text-green-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-gray-900">Fecha de Fin</p>
                                <p class="text-gray-600">{{ \Carbon\Carbon::parse($reservation->end_date)->format('d/m/Y g:i A') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-purple-50 rounded-lg">
                            <i class="fas fa-hourglass-half text-purple-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-gray-900">Duración</p>
                                <p class="text-gray-600">
                                    {{ \Carbon\Carbon::parse($reservation->start_date)->diffForHumans(\Carbon\Carbon::parse($reservation->end_date), true) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información Adicional -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Información Adicional</h3>
                    <div class="space-y-4">
                        <div class="flex items-center p-4 bg-yellow-50 rounded-lg">
                            <i class="fas fa-tag text-yellow-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-gray-900">Tipo</p>
                                <p class="text-gray-600 capitalize">{{ $reservation->type }}</p>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-orange-50 rounded-lg">
                            <i class="fas fa-user text-orange-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-gray-900">Responsable</p>
                                <p class="text-gray-600">{{ $reservation->responsible_name ?? 'No especificado' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-pink-50 rounded-lg">
                            <i class="fas fa-users text-pink-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-gray-900">Número de Personas</p>
                                <p class="text-gray-600">{{ $reservation->people_count ?? 1 }} persona(s)</p>
                            </div>
                        </div>
                        @if($reservation->location)
                        <div class="flex items-center p-4 bg-indigo-50 rounded-lg">
                            <i class="fas fa-map-marker-alt text-indigo-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-gray-900">Ubicación</p>
                                <p class="text-gray-600 capitalize">
                                    @if($reservation->location === 'jardin')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Jardín
                                        </span>
                                    @elseif($reservation->location === 'casino')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Casino
                                        </span>
                                    @else
                                        {{ ucfirst($reservation->location) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endif
                        <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-calendar-plus text-gray-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-gray-900">Creada</p>
                                <p class="text-gray-600">{{ \Carbon\Carbon::parse($reservation->created_at)->format('d/m/Y g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del Propietario -->
            <div class="border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Información del Propietario</h3>
                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                    <i class="fas fa-user text-gray-600 mr-3"></i>
                    <div>
                        <p class="font-medium text-gray-900">Reservado por</p>
                        <p class="text-gray-600">{{ $reservation->user->name ?? 'Usuario no disponible' }}</p>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones</h3>
                <div class="flex space-x-3">
                    @can('update', $reservation)
                        <a href="{{ route('reservations.edit', $reservation) }}" class="btn-primary">
                            <i class="fas fa-edit mr-2"></i>Editar Reserva
                        </a>
                    @else
                        <div class="text-sm text-gray-600 bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                            <i class="fas fa-info-circle mr-2"></i>
                            Solo los administradores pueden editar esta reserva
                        </div>
                    @endcan

                    @can('delete', $reservation)
                        <form method="POST" action="{{ route('reservations.destroy', $reservation) }}" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar esta reserva?')">
                                <i class="fas fa-trash mr-2"></i>Eliminar Reserva
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
