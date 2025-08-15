@extends('layouts.app')

@section('title', 'Calendario - TodoList')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header del Calendario -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Calendario
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Gestiona tu agenda y reservas
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
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-calendar text-blue-600 text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">
                    Calendario en Desarrollo
                </h3>
                <p class="text-gray-600 mb-6">
                    Esta funcionalidad estará disponible próximamente. 
                    Por ahora, puedes gestionar tus reservas desde el dashboard.
                </p>
                <a href="{{ route('home') }}" class="btn-primary">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
