@extends('layouts.app')

@section('title', 'Nueva Reserva - TodoList')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Nueva Reserva
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Crea una nueva reserva o evento en tu agenda
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

    <!-- Formulario -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <form action="{{ route('reservations.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Título -->
                <div>
                    <label for="title" class="form-label">Título de la Reserva *</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title') }}"
                           class="form-input @error('title') border-red-500 @enderror" 
                           placeholder="Ej: Reunión de equipo"
                           required>
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div>
                    <label for="description" class="form-label">Descripción</label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              class="form-textarea @error('description') border-red-500 @enderror" 
                              placeholder="Detalles adicionales de la reserva">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha y Hora de Inicio -->
                <div>
                    <label for="start_date" class="form-label">Fecha y Hora de Inicio *</label>
                    <input type="datetime-local" 
                           id="start_date" 
                           name="start_date" 
                           value="{{ old('start_date') }}"
                           class="form-input @error('start_date') border-red-500 @enderror" 
                           required>
                    @error('start_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha y Hora de Fin -->
                <div>
                    <label for="end_date" class="form-label">Fecha y Hora de Fin *</label>
                    <input type="datetime-local" 
                           id="end_date" 
                           name="end_date" 
                           value="{{ old('end_date') }}"
                           class="form-input @error('end_date') border-red-500 @enderror" 
                           required>
                    @error('end_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ubicación -->
                <div>
                    <label for="location" class="form-label">Ubicación</label>
                    <input type="text" 
                           id="location" 
                           name="location" 
                           value="{{ old('location') }}"
                           class="form-input @error('location') border-red-500 @enderror" 
                           placeholder="Ej: Sala de conferencias A">
                    @error('location')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo de Reserva -->
                <div>
                    <label for="type" class="form-label">Tipo de Reserva *</label>
                    <select id="type" 
                            name="type" 
                            class="form-select @error('type') border-red-500 @enderror" 
                            required>
                        <option value="">Selecciona un tipo</option>
                        <option value="meeting" {{ old('type') == 'meeting' ? 'selected' : '' }}>Reunión</option>
                        <option value="event" {{ old('type') == 'event' ? 'selected' : '' }}>Evento</option>
                        <option value="appointment" {{ old('type') == 'appointment' ? 'selected' : '' }}>Cita</option>
                        <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Otro</option>
                    </select>
                    @error('type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botones -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('home') }}" class="btn-secondary">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i>Crear Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Validación de fechas en tiempo real
    document.getElementById('start_date').addEventListener('change', function() {
        const startDate = new Date(this.value);
        const endDateInput = document.getElementById('end_date');
        
        // Establecer fecha mínima para end_date
        const minEndDate = new Date(startDate.getTime() + 30 * 60000); // 30 minutos después
        endDateInput.min = minEndDate.toISOString().slice(0, 16);
        
        // Si end_date es anterior a start_date, limpiarlo
        if (endDateInput.value && new Date(endDateInput.value) <= startDate) {
            endDateInput.value = '';
        }
    });
</script>
@endpush
@endsection
