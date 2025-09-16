@extends('layouts.app')

@section('title', 'Editar Reserva - Reservas')

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
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <form action="{{ route('reservations.update', $reservation) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Título -->
                <div>
                    <label for="title" class="form-label">Título de la Reserva *</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title', $reservation->title) }}"
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
                           value="{{ old('responsible_name', $reservation->responsible_name ?? auth()->user()->name) }}"
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
                              placeholder="Detalles adicionales de la reserva">{{ old('description', $reservation->description) }}</textarea>
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
                           value="{{ old('start_date', \Carbon\Carbon::parse($reservation->start_date)->format('Y-m-d H:i')) }}"
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
                           value="{{ old('end_date', \Carbon\Carbon::parse($reservation->end_date)->format('Y-m-d H:i')) }}"
                           class="form-input @error('end_date') border-red-500 @enderror"
                           placeholder="YYYY-MM-DD HH:MM"
                           required>
                    @error('end_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ubicación -->
                <div>
                    <label for="location" class="form-label">Ubicación *</label>
                    <div class="relative">
                        <select id="location" name="location"
                                class="form-select appearance-none bg-white hover:bg-gray-50 focus:bg-white cursor-pointer transition-colors duration-200 @error('location') border-red-500 @enderror"
                                required>
                            <option value="">Selecciona una ubicación</option>
                            <option value="jardin" {{ old('location', $reservation->location) == 'jardin' ? 'selected' : '' }}> Jardín</option>
                            <option value="casino" {{ old('location', $reservation->location) == 'casino' ? 'selected' : '' }}> Casino</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200 group-hover:text-gray-600"></i>
                        </div>
                    </div>
                    @error('location')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Escuadrón -->
                <div>
                    <label for="squad" class="form-label">Escuadrón *</label>
                    <div class="relative">
                        <select id="squad" name="squad" class="form-select appearance-none bg-white hover:bg-gray-50 focus:bg-white cursor-pointer transition-colors duration-200 @error('squad') border-red-500 @enderror" required>
                            <option value="">Seleccionar escuadrón</option>
                            <option value="design" {{ old('squad', $reservation->squad) === 'design' ? 'selected' : '' }}> Design</option>
                            <option value="estimation" {{ old('squad', $reservation->squad) === 'estimation' ? 'selected' : '' }}> Estimation</option>
                            <option value="development" {{ old('squad', $reservation->squad) === 'development' ? 'selected' : '' }}> Product Development</option>
                            <option value="manufacturing" {{ old('squad', $reservation->squad) === 'manufacturing' ? 'selected' : '' }}> Manufactura</option>
                            <option value="quality" {{ old('squad', $reservation->squad) === 'quality' ? 'selected' : '' }}> Quality</option>
                            <option value="finance" {{ old('squad', $reservation->squad) === 'finance' ? 'selected' : '' }}> Finance</option>
                            <option value="it" {{ old('squad', $reservation->squad) === 'it' ? 'selected' : '' }}> IT</option>
                            <option value="brand" {{ old('squad', $reservation->squad) === 'brand' ? 'selected' : '' }}> Brand & Co</option>
                            <option value="supply" {{ old('squad', $reservation->squad) === 'supply' ? 'selected' : '' }}> Supply Chain</option>
                            <option value="people" {{ old('squad', $reservation->squad) === 'people' ? 'selected' : '' }}> People</option>
                            <option value="Maestro" {{ old('squad', $reservation->squad) === 'Maestro' ? 'selected' : '' }}> Maestro</option>
                            
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200 group-hover:text-gray-600"></i>
                        </div>
                    </div>
                    @error('squad')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo de Reserva -->
                <div>
                    <label for="type" class="form-label">Tipo de Reserva *</label>
                    <div class="relative">
                        <select id="type" name="type"
                                class="form-select appearance-none bg-white hover:bg-gray-50 focus:bg-white cursor-pointer transition-colors duration-200 @error('type') border-red-500 @enderror"
                                required>
                            <option value="">Selecciona un tipo</option>
                            <option value="meeting" {{ old('type', $reservation->type)=='meeting' ? 'selected' : '' }}> Reunión</option>
                            <option value="event" {{ old('type', $reservation->type)=='event' ? 'selected' : '' }}> Evento</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200 group-hover:text-gray-600"></i>
                        </div>
                    </div>
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
                           value="{{ old('people_count', $reservation->people_count ?? 1) }}"
                           class="form-input @error('people_count') border-red-500 @enderror" 
                           placeholder="Número de personas que asistirán"
                           min="1" step="1"
                           max="100"
                           required>
                    @error('people_count')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estado -->
                <div>
                    <label for="status" class="form-label">Estado</label>
                    <div class="relative">
                        <select id="status" name="status" class="form-select appearance-none bg-white hover:bg-gray-50 focus:bg-white cursor-pointer transition-colors duration-200 @error('status') border-red-500 @enderror">
                            <option value="pending" {{ old('status', $reservation->status) === 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="confirmed" {{ old('status', $reservation->status) === 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                            <option value="completed" {{ old('status', $reservation->status) === 'completed' ? 'selected' : '' }}>Completado</option>
                            <option value="cancelled" {{ old('status', $reservation->status) === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200 group-hover:text-gray-600"></i>
                        </div>
                    </div>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Flatpickr para fecha/hora con minutos 00, 15, 30, 45
        if (window.flatpickr) {
            const startPicker = flatpickr('#start_date', {
                enableTime: true,
                time_24hr: false,
                minuteIncrement: 15,
                dateFormat: 'Y-m-d H:i',
                locale: 'es',
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
                        // Ajustar fin automáticamente a +1h si no es posterior
                        try {
                            const endInput = document.getElementById('end_date');
                            const currentEnd = endInput && endInput.value ? new Date(endInput.value.replace(' ', 'T')) : null;
                            const startCopy = new Date(d.getTime());
                            const endCandidate = new Date(startCopy.getTime());
                            endCandidate.setHours(endCandidate.getHours() + 1);
                            if (window.endPicker && (!currentEnd || currentEnd <= d)) {
                                window.endPicker.setDate(endCandidate, true);
                            } else if (endInput && (!currentEnd || currentEnd <= d)) {
                                const pad = (n) => String(n).padStart(2, '0');
                                const formatted = `${endCandidate.getFullYear()}-${pad(endCandidate.getMonth()+1)}-${pad(endCandidate.getDate())} ${pad(endCandidate.getHours())}:${pad(endCandidate.getMinutes())}`;
                                endInput.value = formatted;
                            }
                        } catch (e) {}
                    }
                }
            });
            
            const endPicker = flatpickr('#end_date', {
                enableTime: true,
                time_24hr: false,
                minuteIncrement: 15,
                dateFormat: 'Y-m-d H:i',
                locale: 'es',
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
            // Exponer para usar dentro del onChange de startPicker
            window.endPicker = endPicker;
        }

        // Mejorar interactividad de los comboboxes
        const selects = document.querySelectorAll('select');
        selects.forEach(select => {
            const container = select.closest('.relative');
            const chevron = container?.querySelector('.fa-chevron-down');
            
            if (select && container && chevron) {
                // Agregar clase group para hover effects
                container.classList.add('group');
                
                // Animación del chevron al abrir/cerrar
                select.addEventListener('focus', () => {
                    chevron.style.transform = 'rotate(180deg)';
                });
                
                select.addEventListener('blur', () => {
                    chevron.style.transform = 'rotate(0deg)';
                });
                
                // Cambiar color del chevron al hover
                container.addEventListener('mouseenter', () => {
                    chevron.classList.remove('text-gray-400');
                    chevron.classList.add('text-gray-600');
                });
                
                container.addEventListener('mouseleave', () => {
                    chevron.classList.remove('text-gray-600');
                    chevron.classList.add('text-gray-400');
                });
            }
        });
    });
</script>
@endpush
