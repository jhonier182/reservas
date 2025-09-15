@extends('layouts.app')

@section('title', 'Mis Reservas - Reservas')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        @if(auth()->check() && auth()->user()->isAdmin())
                            Todas las Reservas
                        @else
                            Todas las Reservas
                        @endif
                    </h1>
                    <p class="mt-2 text-gray-600">
                        @if(auth()->check() && auth()->user()->isAdmin())
                            Gestiona todas las reservas del sistema como administrador
                        @else
                            Visualiza todas las reservas del sistema
                        @endif
                    </p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('reservations.create') }}" class="btn-primary">
                        <i class="fas fa-plus mr-2"></i>Nueva Reserva
                    </a>
                    <a href="{{ route('home') }}" class="btn-secondary">
                        <i class="fas fa-home mr-2"></i>Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Panel Principal -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Filtros y Búsqueda -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <form method="GET" action="{{ route('reservations.index') }}" id="filterForm">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <input type="text" 
                                       name="search"
                                       placeholder="Buscar reservas..." 
                                       class="form-input w-full"
                                       id="searchInput"
                                       value="{{ $search ?? '' }}">
                            </div>
                            <div class="flex gap-3">
                                <select class="form-select" name="status" id="statusFilter">
                                    <option value="">Todos los estados</option>
                                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}> Pendiente</option>
                                    <option value="confirmed" {{ ($status ?? '') === 'confirmed' ? 'selected' : '' }}> Confirmado</option>
                                    <option value="completed" {{ ($status ?? '') === 'completed' ? 'selected' : '' }}> Completado</option>
                                    <option value="cancelled" {{ ($status ?? '') === 'cancelled' ? 'selected' : '' }}> Cancelado</option>
                                </select>
                                <select class="form-select" name="type" id="typeFilter">
                                    <option value="">Todos los tipos</option>
                                    <option value="meeting" {{ ($type ?? '') === 'meeting' ? 'selected' : '' }}> Reunión</option>
                                    <option value="event" {{ ($type ?? '') === 'event' ? 'selected' : '' }}> Evento</option>
                                </select>
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-search mr-2"></i>Filtrar
                                </button>
                                @if($search || $status || $type)
                                    <a href="{{ route('reservations.index') }}" class="btn-secondary">
                                        <i class="fas fa-times mr-2"></i>Limpiar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Lista de Reservas -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Reservas Recientes</h3>
                        @if($search || $status || $type)
                            <div class="flex items-center space-x-2 text-sm text-gray-600">
                                <i class="fas fa-filter"></i>
                                <span>Filtros activos:</span>
                                @if($search)
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                        Búsqueda: "{{ $search }}"
                                    </span>
                                @endif
                                @if($status)
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
                                        Estado: {{ ucfirst($status) }}
                                    </span>
                                @endif
                                @if($type)
                                    <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs">
                                        Tipo: {{ ucfirst($type) }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                    
                    @if(isset($reservations) && $reservations->count() > 0)
                        <div class="space-y-4">
                            @foreach($reservations as $reservation)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <h4 class="font-semibold text-gray-900">{{ $reservation->title ?? 'Sin título' }}</h4>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    @if(($reservation->status ?? 'pending') === 'confirmed') bg-green-100 text-green-800
                                                    @elseif(($reservation->status ?? 'pending') === 'pending') bg-yellow-100 text-yellow-800
                                                    @elseif(($reservation->status ?? 'pending') === 'completed') bg-blue-100 text-blue-800
                                                    @else bg-red-100 text-red-800
                                                    @endif">
                                                    {{ ucfirst($reservation->status ?? 'pending') }}
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ ucfirst($reservation->type ?? 'other') }}
                                                </span>
                                            </div>
                                            
                                            @if($reservation->description)
                                                <p class="text-gray-600 text-sm mb-2">{{ $reservation->description }}</p>
                                            @endif
                                            
                                            <!-- Información del propietario y responsable -->
                                            <div class="flex items-center space-x-1 text-sm text-gray-500 mb-2">
                                                <i class="fas fa-user"></i>
                                                <span>Reservado por: {{ $reservation->user->name ?? 'Usuario no disponible' }}</span>
                                            </div>
                                            @if($reservation->responsible_name)
                                                <div class="flex items-center space-x-1 text-sm text-gray-500 mb-2">
                                                    <i class="fas fa-user-tie"></i>
                                                    <span>Responsable: {{ $reservation->responsible_name }}</span>
                                                </div>
                                            @endif
                                            @if($reservation->people_count)
                                                <div class="flex items-center space-x-1 text-sm text-gray-500 mb-2">
                                                    <i class="fas fa-users"></i>
                                                    <span>{{ $reservation->people_count }} persona(s)</span>
                                                </div>
                                            @endif
                                            
                                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                                <div class="flex items-center space-x-1">
                                                    <i class="fas fa-calendar"></i>
                                                    <span>{{ \Carbon\Carbon::parse($reservation->start_date ?? now())->format('d/m/Y g:i A') }}</span>
                                                </div>
                                                @if($reservation->location)
                                                    <div class="flex items-center space-x-1">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        <span>
                                                            @if($reservation->location === 'jardin')
                                                                 Jardín
                                                            @elseif($reservation->location === 'casino')
                                                                 Casino
                                                            @else
                                                                {{ ucfirst($reservation->location) }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-2 ml-4">
                                            @can('update', $reservation)
                                                <a href="{{ route('reservations.edit', $reservation->id ?? 1) }}" 
                                                   class="text-blue-600 hover:text-blue-800 p-2"
                                                   title="Editar reserva">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            <a href="{{ route('reservations.show', $reservation->id ?? 1) }}" 
                                               class="text-green-600 hover:text-green-800 p-2"
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Paginación -->
                        @if(method_exists($reservations, 'links'))
                            <div class="mt-6">
                                {{ $reservations->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-calendar text-gray-400 text-4xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">No tienes reservas</h3>
                            <p class="text-gray-600 mb-6">Crea tu primera reserva para comenzar a organizar tu agenda</p>
                            <a href="{{ route('reservations.create') }}" class="btn-primary">
                                <i class="fas fa-plus mr-2"></i>Crear Primera Reserva
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="space-y-6">
                <!-- Estadísticas -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Estadísticas</h4>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Total</span>
                            <span class="font-semibold text-gray-900">{{ $stats['total'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Confirmadas</span>
                            <span class="font-semibold text-green-600">{{ $stats['confirmed'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Pendientes</span>
                            <span class="font-semibold text-yellow-600">{{ $stats['pending'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Completadas</span>
                            <span class="font-semibold text-blue-600">{{ $stats['completed'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Canceladas</span>
                            <span class="font-semibold text-red-600">{{ $stats['cancelled'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Acciones Rápidas</h4>
                    <div class="space-y-3">
                        <a href="{{ route('calendar') }}" class="block w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                            <i class="fas fa-calendar mr-2"></i>Ver Calendario
                        </a>
                        <a href="{{ route('google.auth') }}?email={{ Auth::user()->email }}" class="block w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                            <i class="fab fa-google mr-2"></i>Sincronizar Google
                        </a>
                        <a href="{{ route('profile') }}" class="block w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                            <i class="fas fa-user mr-2"></i>Mi Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-submit del formulario cuando cambian los filtros
    document.getElementById('statusFilter').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    document.getElementById('typeFilter').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    // Debounce para la búsqueda (esperar 500ms después de que el usuario deje de escribir)
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            document.getElementById('filterForm').submit();
        }, 500);
    });

    // Submit inmediato al presionar Enter en el campo de búsqueda
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            clearTimeout(searchTimeout);
            document.getElementById('filterForm').submit();
        }
    });
</script>
@endpush
@endsection
