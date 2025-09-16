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
                                <strong>{{ $lockedLabel }}</strong> — Ubicación registrada en el calendario
                            </p>
                        </div>
                        <input type="hidden" name="location" value="{{ $lockedLocation }}">
                        <div class="relative">
                            <select class="form-select appearance-none bg-gray-100 cursor-not-allowed" disabled>
                                <option value="jardin" {{ $lockedLocation==='jardin' ? 'selected' : '' }}> Jardín</option>
                                <option value="casino" {{ $lockedLocation==='casino' ? 'selected' : '' }}> Casino</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                        </div>
                    @else
                        <div class="relative">
                            <select id="location" name="location"
                                    class="form-select appearance-none bg-white hover:bg-gray-50 focus:bg-white cursor-pointer transition-colors duration-200 @error('location') border-red-500 @enderror"
                                    required>
                                <option value="">Selecciona una ubicación</option>
                                <option value="jardin" {{ old('location')=='jardin' ? 'selected' : '' }}> Jardín</option>
                                <option value="casino" {{ old('location')=='casino' ? 'selected' : '' }}> Casino</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200 group-hover:text-gray-600"></i>
                            </div>
                        </div>
                    @endif

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
                            <option value="design" {{ old('squad') === 'design' ? 'selected' : '' }}> Design</option>
                            <option value="estimation" {{ old('squad') === 'estimation' ? 'selected' : '' }}> Estimation</option>
                            <option value="development" {{ old('squad') === 'development' ? 'selected' : '' }}> Product Development</option>
                            <option value="manufacturing" {{ old('squad') === 'manufacturing' ? 'selected' : '' }}> Manufactura</option>
                            <option value="quality" {{ old('squad') === 'quality' ? 'selected' : '' }}> Quality</option>
                            <option value="finance" {{ old('squad') === 'finance' ? 'selected' : '' }}> Finance</option>
                            <option value="it" {{ old('squad') === 'it' ? 'selected' : '' }}> IT</option>
                            <option value="brand" {{ old('squad') === 'brand' ? 'selected' : '' }}> Brand & Co</option>
                            <option value="supply" {{ old('squad') === 'supply' ? 'selected' : '' }}> Supply Chain</option>
                            <option value="people" {{ old('squad') === 'people' ? 'selected' : '' }}> People</option>
                            <option value="Maestro" {{ old('squad') === 'Maestro' ? 'selected' : '' }}> Maestro</option>

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
                            <option value="meeting" {{ old('type')=='meeting' ? 'selected' : '' }}> Reunión</option>
                            <option value="event" {{ old('type')=='event' ? 'selected' : '' }}> Evento</option>
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

                {{-- Aviso fin de semana (se controla por JS) --}}
            <div id="weekendNotice" class="hidden mb-4 p-3 rounded-lg border border-amber-300 bg-amber-50 text-amber-800 text-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Estás reservando fuera del horario laboral (sábado/domingo).
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
document.addEventListener('DOMContentLoaded', function () {
    const weekendBox = document.getElementById('weekendNotice');

    function toggleWeekendNotice(dateObj) {
        if (!dateObj || !weekendBox) return;
        const day = dateObj.getDay(); // 0=Dom, 6=Sáb
        const isWeekend = (day === 0 || day === 6);
        weekendBox.classList.toggle('hidden', !isWeekend);
    }

    if (window.flatpickr) {
        const startPicker = flatpickr('#start_date', {
            enableTime: true,
            time_24hr: false,
            minuteIncrement: 15,
            dateFormat: 'Y-m-d H:i',
            locale: 'es',
            onChange: function (selectedDates, dateStr, instance) {
                if (selectedDates[0]) {
                    const d = selectedDates[0];
                    const m = d.getMinutes();
                    const r = m % 15;
                    if (r !== 0) {
                        if (r < 8) d.setMinutes(m - r); else d.setMinutes(m + (15 - r));
                        d.setSeconds(0);
                        instance.setDate(d, true);
                    }

                    // Ajuste automático de end y aviso de fin de semana
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

                    // Aquí encendemos/apagamos el aviso
                    toggleWeekendNotice(selectedDates[0]);
                }
            }
        });

        const endPicker = flatpickr('#end_date', {
            enableTime: true,
            time_24hr: false,
            minuteIncrement: 15,
            dateFormat: 'Y-m-d H:i',
            locale: 'es',
            onChange: function (selectedDates, dateStr, instance) {
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

        // Exponer para onChange de start
        window.endPicker = endPicker;

        // Si el campo ya viene precargado (old/request), mostrar aviso al cargar
        const startInputVal = document.getElementById('start_date')?.value;
        if (startInputVal) {
            const first = new Date(startInputVal.replace(' ', 'T'));
            if (!isNaN(first.getTime())) toggleWeekendNotice(first);
        }
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
@endsection