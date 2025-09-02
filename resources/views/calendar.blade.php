@extends('layouts.app')

@section('title', 'Calendario - Reservas')

@section('content')

<div class="min-h-screen bg-gray-50">
    <!-- Header del Calendario -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <script>
                    window.IS_ADMIN = {{ auth()->check() && auth()->user()->isAdmin() ? 'true' : 'false' }};
                  </script>
                  
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Calendario
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Gestiona tu agenda y reservas con vista completa
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

    <!-- Contenido del Calendario -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <!-- Calendario FullCalendar -->
            <div id="calendar"></div>
            
            <!-- Instrucciones de uso -->
            <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                <h4 class="font-semibold text-blue-900 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>¿Cómo usar el calendario?
                </h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• <strong>Click en una fecha</strong> para crear una nueva reserva</li>
                    <li>• <strong>Click en un evento</strong> para ver detalles</li>
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <li>• <strong>Arrastra eventos</strong> para cambiar fechas (solo administradores)</li>
                        <li>• <strong>Redimensiona eventos</strong> para cambiar duración (solo administradores)</li>
                    @else
                        <li>• <strong>Los eventos no se pueden modificar</strong> - Solo los administradores pueden editar reservas</li>
                    @endif
                    <li>• <strong>Cambia de vista</strong> entre mes, semana, día y lista</li>
                </ul>
                
                @if(auth()->check() && !auth()->user()->isAdmin())
                    <div class="mt-3 p-3 bg-yellow-50 rounded border border-yellow-200">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Nota:</strong> Puedes ver todas las reservas del sistema, pero solo los administradores pueden modificarlas.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('css/fullcalendar.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('js/calendar.js') }}"></script>
@endpush
