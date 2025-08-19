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
                    <label for="location" class="form-label">Ubicación *</label>
                    <select id="location" 
                            name="location" 
                            class="form-select @error('location') border-red-500 @enderror" 
                            required>
                        <option value="">Selecciona una ubicación</option>
                        <option value="jardin" {{ old('location') == 'jardin' ? 'selected' : '' }}>Jardín</option>
                        <option value="casino" {{ old('location') == 'casino' ? 'selected' : '' }}>Casino</option>
                    </select>
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
                <!-- Opciones Avanzadas -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-cog text-blue-600 mr-2"></i>Opciones Avanzadas
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Evento Recurrente -->
                        <div>
                            <label class="form-label">
                                <input type="checkbox" id="is_recurring" name="is_recurring" class="form-checkbox mr-2">
                                Evento Recurrente
                            </label>
                            <div id="recurrence_options" class="mt-3 hidden">
                                <select name="recurrence_type" class="form-select">
                                    <option value="daily">Diario</option>
                                    <option value="weekly">Semanal</option>
                                    <option value="monthly">Mensual</option>
                                    <option value="yearly">Anual</option>
                                </select>
                                <input type="number" name="recurrence_count" placeholder="Número de repeticiones" 
                                       class="form-input mt-2" min="1" max="52">
                            </div>
                        </div>
                        
                        <!-- Conferencia Automática -->
                        <div>
                            <label class="form-label">
                                <input type="checkbox" id="auto_conference" name="auto_conference" class="form-checkbox mr-2">
                                Crear Conferencia Automática
                            </label>
                            <p class="text-sm text-gray-600 mt-1">Solo para reuniones y citas</p>
                        </div>
                    </div>
                </div>

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
    // Verificar disponibilidad de ubicación en tiempo real
    function checkLocationAvailability() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const location = document.getElementById('location').value;
        
        if (startDate && endDate && location) {
            // Aquí podrías hacer una llamada AJAX para verificar disponibilidad
            // Por ahora solo validamos que las fechas sean válidas
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            if (end <= start) {
                document.getElementById('location').classList.add('border-red-500');
                return false;
            } else {
                document.getElementById('location').classList.remove('border-red-500');
                return true;
            }
        }
        return true;
    }

    // Event listeners para verificar disponibilidad
    document.getElementById('start_date').addEventListener('change', checkLocationAvailability);
    document.getElementById('end_date').addEventListener('change', checkLocationAvailability);
    document.getElementById('location').addEventListener('change', checkLocationAvailability);

    // Opciones avanzadas
    document.addEventListener('DOMContentLoaded', function() {
        const isRecurringCheckbox = document.getElementById('is_recurring');
        const recurrenceOptions = document.getElementById('recurrence_options');
        const autoConferenceCheckbox = document.getElementById('auto_conference');
        const typeSelect = document.getElementById('type');
        
        // Mostrar/ocultar opciones de recurrencia
        if (isRecurringCheckbox) {
            isRecurringCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    recurrenceOptions.classList.remove('hidden');
                } else {
                    recurrenceOptions.classList.add('hidden');
                }
            });
        }
        
        // Habilitar/deshabilitar conferencia automática según el tipo
        function updateConferenceOption() {
            const selectedType = typeSelect.value;
            if (selectedType === 'meeting' || selectedType === 'appointment') {
                autoConferenceCheckbox.disabled = false;
                autoConferenceCheckbox.parentElement.classList.remove('opacity-50');
            } else {
                autoConferenceCheckbox.disabled = true;
                autoConferenceCheckbox.checked = false;
                autoConferenceCheckbox.parentElement.classList.add('opacity-50');
            }
        }
        
        typeSelect.addEventListener('change', updateConferenceOption);
        updateConferenceOption(); // Ejecutar al cargar la página
    });
</script>
@endpush
@endsection
