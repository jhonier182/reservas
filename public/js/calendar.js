// Calendario funcional con FullCalendar
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar FullCalendar
    var calendarEl = document.getElementById('calendar');
    
    if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            buttonText: {
                today: 'Hoy',
                month: 'Mes',
                week: 'Semana',
                day: 'Día',
                list: 'Lista'
            },
            height: 'auto',
            editable: true,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            weekends: true,
            
            // Cargar eventos de Google Calendar
            events: function(info, successCallback, failureCallback) {
                // Hacer petición AJAX para obtener eventos
                fetch('/google/calendar/events?start_date=' + info.startStr + '&end_date=' + info.endStr, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Convertir eventos de Google Calendar a formato FullCalendar
                        var events = data.events.map(function(event) {
                            return {
                                id: event.id,
                                title: event.title || event.summary || 'Sin título',
                                start: event.start?.dateTime || event.start?.date || event.start,
                                end: event.end?.dateTime || event.end?.date || event.end,
                                description: event.description || '',
                                location: event.location || '',
                                backgroundColor: event.backgroundColor || '#3B82F6',
                                borderColor: event.borderColor || '#2563EB',
                                textColor: event.textColor || '#FFFFFF',
                                extendedProps: {
                                    googleEventId: event.googleEventId,
                                    description: event.description,
                                    location: event.location,
                                    attendees: event.attendees,
                                    type: event.extendedProps?.type || 'google_event'
                                }
                            };
                        });
                        
                        successCallback(events);
                    } else {
                        console.error('Error cargando eventos:', data.message);
                        // Si falla Google Calendar, intentar cargar solo reservas locales
                        loadLocalReservations(info.startStr, info.endStr, successCallback);
                    }
                })
                .catch(error => {
                    console.error('Error en la petición:', error);
                    // Si falla Google Calendar, intentar cargar solo reservas locales
                    loadLocalReservations(info.startStr, info.endStr, successCallback);
                });
            },
            
            // Seleccionar fecha para crear reserva
            select: function(info) {
                // Redirigir a crear reserva con fecha seleccionada
                var startDate = info.startStr;
                var endDate = info.endStr;
                
                window.location.href = '/reservations/create?start_date=' + startDate + '&end_date=' + endDate;
            },
            
            // Click en evento para ver detalles
            eventClick: function(info) {
                var event = info.event;
                var description = event.extendedProps.description || 'Sin descripción';
                var location = event.extendedProps.location || 'Sin ubicación';
                
                // Mostrar modal con detalles del evento
                showEventDetails(event.title, description, location, event.start, event.end);
            },
            
            // Arrastrar evento para cambiar fecha
            eventDrop: function(info) {
                var event = info.event;
                console.log('Evento movido:', event.title, 'a', event.start);
                // Aquí podrías implementar la actualización en Google Calendar
            },
            
            // Redimensionar evento para cambiar duración
            eventResize: function(info) {
                var event = info.event;
                console.log('Evento redimensionado:', event.title, 'duración:', event.start, 'a', event.end);
                // Aquí podrías implementar la actualización en Google Calendar
            }
        });
        
        calendar.render();
    }
    
    // Función para mostrar detalles del evento
    function showEventDetails(title, description, location, start, end) {
        var modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
        modal.innerHTML = `
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">${title}</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Descripción:</label>
                            <p class="text-sm text-gray-900">${description}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Ubicación:</label>
                            <p class="text-sm text-gray-900">${location}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Inicio:</label>
                            <p class="text-sm text-gray-900">${start ? formatDateTime(start) : 'No especificado'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Fin:</label>
                            <p class="text-sm text-gray-900">${end ? formatDateTime(end) : 'No especificado'}</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Cerrar modal al hacer click fuera
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }
    
    // Función para cargar reservas locales
    function loadLocalReservations(startDate, endDate, successCallback) {
        fetch('/google/calendar/events?start_date=' + startDate + '&end_date=' + endDate, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.local_count > 0) {
                // Filtrar solo eventos locales
                var localEvents = data.events.filter(function(event) {
                    return event.extendedProps && event.extendedProps.type === 'local_reservation';
                });
                successCallback(localEvents);
            } else {
                successCallback([]);
            }
        })
        .catch(error => {
            console.error('Error cargando reservas locales:', error);
            successCallback([]);
        });
    }
    
    // Función para formatear fechas con AM/PM
    function formatDateTime(dateString) {
        const date = new Date(dateString);
        const day = date.getDate().toString().padStart(2, '0');
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const year = date.getFullYear();
        
        let hours = date.getHours();
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        
        // Convertir a formato 12 horas
        hours = hours % 12;
        hours = hours ? hours : 12; // Si es 0, mostrar 12
        
        return `${day}/${month}/${year} ${hours}:${minutes} ${ampm}`;
    }
});
