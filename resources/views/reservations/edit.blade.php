@extends('layouts.app')

@section('title', 'Editar Reserva - TodoList')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Editar Reserva
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Modifica los detalles de tu reserva
                    </p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('reservations.show', $reservation) }}" class="btn-secondary">
                        <i class="fas fa-eye mr-2"></i>Ver Detalles
                    </a>
                    <a href="{{ route('reservations.index') }}" class="btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Edición -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <form action="{{ route('reservations.update', $reservation) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Título -->
                <div>
                    <label for="title" class="form-label">Título de la Reserva *</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $reservation->title) }}" 
                           class="form-input @error('title') border-red-500 @enderror" 
                           placeholder="Ej: Reunión de equipo" required>
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div>
                    <label for="description" class="form-label">Descripción</label>
                    <textarea id="description" name="description" rows="4" 
                              class="form-input @error('description') border-red-500 @enderror" 
                              placeholder="Describe los detalles de tu reserva">{{ old('description', $reservation->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fechas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="start_date" class="form-label">Fecha y Hora de Inicio *</label>
                        <input type="datetime-local" id="start_date" name="start_date" 
                               value="{{ old('start_date', \Carbon\Carbon::parse($reservation->start_date)->format('Y-m-d\TH:i')) }}" 
                               class="form-input @error('start_date') border-red-500 @enderror" required>
                        @error('start_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_date" class="form-label">Fecha y Hora de Fin *</label>
                        <input type="datetime-local" id="end_date" name="end_date" 
                               value="{{ old('end_date', \Carbon\Carbon::parse($reservation->end_date)->format('Y-m-d\TH:i')) }}" 
                               class="form-input @error('end_date') border-red-500 @enderror" required>
                        @error('end_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Tipo y Ubicación -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="type" class="form-label">Tipo de Reserva *</label>
                        <select id="type" name="type" class="form-input @error('type') border-red-500 @enderror" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="meeting" {{ old('type', $reservation->type) === 'meeting' ? 'selected' : '' }}>Reunión</option>
                            <option value="event" {{ old('type', $reservation->type) === 'event' ? 'selected' : '' }}>Evento</option>
                            <option value="appointment" {{ old('type', $reservation->type) === 'appointment' ? 'selected' : '' }}>Cita</option>
                            <option value="other" {{ old('type', $reservation->type) === 'other' ? 'selected' : '' }}>Otro</option>
                        </select>
                        @error('type')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Ubicación -->
                    <div>
                        <label for="location" class="form-label">Ubicación *</label>
                        <select id="location" 
                                name="location" 
                                class="form-select @error('location') border-red-500 @enderror" 
                                required>
                            <option value="">Selecciona una ubicación</option>
                            <option value="jardin" {{ old('location', $reservation->location) == 'jardin' ? 'selected' : '' }}>Jardín</option>
                            <option value="casino" {{ old('location', $reservation->location) == 'casino' ? 'selected' : '' }}>Casino</option>
                        </select>
                        @error('location')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Estado -->
                <div>
                    <label for="status" class="form-label">Estado</label>
                    <select id="status" name="status" class="form-input @error('status') border-red-500 @enderror">
                        <option value="pending" {{ old('status', $reservation->status) === 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="confirmed" {{ old('status', $reservation->status) === 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                        <option value="completed" {{ old('status', $reservation->status) === 'completed' ? 'selected' : '' }}>Completado</option>
                        <option value="cancelled" {{ old('status', $reservation->status) === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botones de Acción -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('reservations.show', $reservation) }}" class="btn-secondary">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i>Actualizar Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Validación de fechas en el cliente
    document.addEventListener('DOMContentLoaded', function() {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        
        function validateDates() {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            
            if (startDate >= endDate) {
                endDateInput.setCustomValidity('La fecha de fin debe ser posterior a la fecha de inicio');
            } else {
                endDateInput.setCustomValidity('');
            }
        }
        
        startDateInput.addEventListener('change', validateDates);
        endDateInput.addEventListener('change', validateDates);
        
        // Validar al cargar la página
        validateDates();
    });
</script>
@endpush
