@extends('layouts.app')

@section('title', 'Nueva Reserva - Reservas')

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

                <!-- Nombre del Responsable -->
                <div>
                    <label for="responsible_name" class="form-label">Nombre del Responsable *</label>
                    <input type="text" 
                           id="responsible_name" 
                           name="responsible_name" 
                           value="{{ old('responsible_name', auth()->user()->name) }}"
                           class="form-input @error('responsible_name') border-red-500 @enderror" 
                           placeholder="Nombre del responsable de la reserva"
                           required>
                    @error('responsible_name')
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
                    <input type="text"
                           id="start_date"
                           name="start_date"
                           value="{{ old('start_date', request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('Y-m-d H:i') : '') }}"
                           class="form-input @error('start_date') border-red-500 @enderror"
                           placeholder="YYYY-MM-DD HH:MM"
                           required>
                    @error('start_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha y Hora de Fin -->
                <div>
                    <label for="end_date" class="form-label">Fecha y Hora de Fin *</label>
                    <input type="text"
                           id="end_date"
                           name="end_date"
                           value="{{ old('end_date', request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('Y-m-d H:i') : (request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('Y-m-d H:i') : '')) }}"
                           class="form-input @error('end_date') border-red-500 @enderror"
                           placeholder="YYYY-MM-DD HH:MM"
                           required>
                    @error('end_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ubicación -->
                @php
                $lockedLocation = request('location');
                $lockedLabel = $lockedLocation === 'jardin' ? 'JARDÍN'
                            : ($lockedLocation === 'casino' ? 'CASINO' : null);
                @endphp

                <div>
                    <label for="location" class="form-label">Ubicación *</label>

                    @if($lockedLabel)
                        <div class="mb-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-800">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <strong>{{ $lockedLabel }}</strong> — ubicación traída desde el calendario
                            </p>
                        </div>
                        <input type="hidden" name="location" value="{{ $lockedLocation }}">
                        <select class="form-select" disabled>
                            <option value="jardin" {{ $lockedLocation==='jardin' ? 'selected' : '' }}>Jardín</option>
                            <option value="casino" {{ $lockedLocation==='casino' ? 'selected' : '' }}>Casino</option>
                        </select>
                    @else
                        <select id="location" name="location"
                                class="form-select @error('location') border-red-500 @enderror"
                                required>
                            <option value="">Selecciona una ubicación</option>
                            <option value="jardin" {{ old('location')=='jardin' ? 'selected' : '' }}>Jardín</option>
                            <option value="casino" {{ old('location')=='casino' ? 'selected' : '' }}>Casino</option>
                        </select>
                    @endif

                    @error('location')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo de Reserva -->
                <div>
                    <label for="type" class="form-label">Tipo de Reserva *</label>
                    <select id="type" name="type"
                            class="form-select @error('type') border-red-500 @enderror"
                            required>
                        <option value="">Selecciona un tipo</option>
                        <option value="meeting" {{ old('type')=='meeting' ? 'selected' : '' }}>Reunión</option>
                        <option value="event" {{ old('type')=='event' ? 'selected' : '' }}>Evento</option>
                        <option value="appointment" {{ old('type')=='appointment' ? 'selected' : '' }}>Cita</option>
                        <option value="other" {{ old('type')=='other' ? 'selected' : '' }}>Otro</option>
                    </select>
                    @error('type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Número de Personas -->
                <div>
                    <label for="people_count" class="form-label">Número de Personas *</label>
                    <input type="number" 
                           id="people_count" 
                           name="people_count" 
                           value="{{ old('people_count', 1) }}"
                           class="form-input @error('people_count') border-red-500 @enderror" 
                           placeholder="Número de personas que asistirán"
                           min="1"
                           max="100"
                           required>
                    @error('people_count')
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
    document.addEventListener('DOMContentLoaded', function() {
        // Flatpickr para fecha/hora con minutos 00, 15, 30, 45
        if (window.flatpickr) {
            const startPicker = flatpickr('#start_date', {
                enableTime: true,
                time_24hr: true,
                minuteIncrement: 15,
                dateFormat: 'Y-m-d H:i',
                onChange: function(selectedDates, dateStr, instance) {
                    // Asegurar redondeo visual si usuario escribe manualmente
                    if (selectedDates[0]) {
                        const d = selectedDates[0];
                        const m = d.getMinutes();
                        const r = m % 15;
                        if (r !== 0) {
                            if (r < 8) d.setMinutes(m - r); else d.setMinutes(m + (15 - r));
                            d.setSeconds(0);
                            
                            instance.setDate(d, true);
                        }
                    }
                }
            });
            
            const endPicker = flatpickr('#end_date', {
                enableTime: true,
                time_24hr: true,
                minuteIncrement: 15,
                dateFormat: 'Y-m-d H:i',
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates[0]) {
                        const d = selectedDates[0];
                        const m = d.getMinutes();
                        const r = m % 15;
                        if (r !== 0) {
                            if (r < 8) d.setMinutes(m - r); else d.setMinutes(m + (15 - r));
                            d.setSeconds(0);
                            instance.setDate(d, true);
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection