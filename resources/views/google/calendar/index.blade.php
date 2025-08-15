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
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $syncStats['total_events'] ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Total de Eventos</div>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ $syncStats['synced_events'] ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Eventos Sincronizados</div>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600">{{ $syncStats['sync_rate'] ?? 0 }}%</div>
                            <div class="text-sm text-gray-600">Tasa de Sincronización</div>
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

                <!-- Información adicional de sincronización -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        <div class="text-sm text-blue-800">
                            <strong>Tip:</strong> Si no ves tus eventos de Google Calendar, usa "Sincronización Completa" para traer todos los eventos existentes.
                        </div>
                    </div>
                </div>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" class="btn-primary" onclick="syncEvents()">
                            <i class="fas fa-sync mr-2"></i>Sincronizar Eventos
                        </button>
                        <button type="button" class="btn-secondary" onclick="forceFullSync()">
                            <i class="fas fa-download mr-2"></i>Sincronización Completa
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

                <!-- Funcionalidades Avanzadas -->
                @if($hasPermissions)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-rocket text-blue-600 mr-2"></i>Funcionalidades Avanzadas
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-video text-blue-600 mr-2"></i>
                                <h4 class="font-medium text-blue-900">Conferencias Automáticas</h4>
                            </div>
                            <p class="text-sm text-blue-700">Las reuniones se crean automáticamente con Google Meet</p>
                        </div>
                        
                        <div class="p-4 bg-green-50 rounded-lg">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-repeat text-green-600 mr-2"></i>
                                <h4 class="font-medium text-green-900">Eventos Recurrentes</h4>
                            </div>
                            <p class="text-sm text-green-700">Crea eventos que se repiten automáticamente</p>
                        </div>
                        
                        <div class="p-4 bg-purple-50 rounded-lg">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-users text-purple-600 mr-2"></i>
                                <h4 class="font-medium text-purple-900">Gestión de Participantes</h4>
                            </div>
                            <p class="text-sm text-purple-700">Invita y gestiona asistentes fácilmente</p>
                        </div>
                        
                        <div class="p-4 bg-orange-50 rounded-lg">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-paperclip text-orange-600 mr-2"></i>
                                <h4 class="font-medium text-orange-900">Archivos de Drive</h4>
                            </div>
                            <p class="text-sm text-orange-700">Adjunta documentos y presentaciones</p>
                        </div>
                    </div>
                </div>
                @endif
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
    async function syncEvents() {
        const button = event.target;
        const originalText = button.innerHTML;
        
        try {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sincronizando...';
            
            const response = await fetch('/google/calendar/sync-events', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('Sincronización completada exitosamente', 'success');
                // Recargar la página para mostrar estadísticas actualizadas
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Error en la sincronización: ' + result.message, 'error');
            }
            
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error en la sincronización', 'error');
        } finally {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    }

    // Función para sincronización completa forzada
    async function forceFullSync() {
        const button = event.target;
        const originalText = button.innerHTML;
        
        try {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sincronización Completa...';
            
            showNotification('Iniciando sincronización completa. Esto puede tomar varios minutos...', 'info');
            
            const response = await fetch('/google/calendar/force-sync', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification(`Sincronización completa finalizada: ${result.results.created} eventos creados, ${result.results.updated} actualizados`, 'success');
                // Recargar la página para mostrar estadísticas actualizadas
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification('Error en la sincronización completa: ' + result.message, 'error');
            }
            
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error en la sincronización completa', 'error');
        } finally {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    }

    // Función para verificar estado
    async function checkStatus() {
        try {
            const response = await fetch('/google/calendar/check-status', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('Estado verificado correctamente', 'success');
                // Recargar la página para mostrar estado actualizado
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Error al verificar estado: ' + result.message, 'error');
            }
            
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al verificar estado', 'error');
        }
    }

    // Función para sincronizar calendarios
    async function syncCalendars() {
        try {
            const response = await fetch('/google/calendar/sync-calendars', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('Calendarios sincronizados correctamente', 'success');
            } else {
                showNotification('Error al sincronizar calendarios: ' + result.message, 'error');
            }
            
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al sincronizar calendarios', 'error');
        }
    }

    // Función para mostrar notificaciones
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
</script>
@endpush
@endsection
