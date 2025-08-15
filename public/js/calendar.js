// Calendario funcional con FullCalendar
document.addEventListener('DOMContentLoaded', function() {
    // Elemento del calendario
    const calendarEl = document.getElementById('calendar');
    
    if (!calendarEl) return;

    // Configuración del calendario
    const calendar = new FullCalendar.Calendar(calendarEl, {
        // Plugins
        plugins: [
            'dayGrid',
            'timeGrid',
            'interaction',
            'list'
        ],
        
        // Configuración inicial
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        
        // Configuración de fechas
        locale: 'es',
        firstDay: 1, // Lunes
        weekNumbers: true,
        dayMaxEvents: true,
        
        // Configuración de eventos
        events: '/api/events', // Endpoint para obtener eventos
        eventDisplay: 'block',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: false,
            hour12: false
        },
        
        // Colores por tipo de evento
        eventClassNames: function(arg) {
            const classes = [];
            if (arg.event.extendedProps.type) {
                classes.push(arg.event.extendedProps.type);
            }
            if (arg.event.extendedProps.status) {
                classes.push(arg.event.extendedProps.status);
            }
            return classes;
        },
        
        // Interacciones
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        
        // Selección de fechas
        select: function(info) {
            showCreateEventModal(info.start, info.end);
        },
        
        // Click en evento
        eventClick: function(info) {
            showEventModal(info.event);
        },
        
        // Drag & Drop de eventos
        eventDrop: function(info) {
            updateEventDates(info.event);
        },
        
        // Redimensionar eventos
        eventResize: function(info) {
            updateEventDates(info.event);
        },
        
        // Renderizado personalizado de eventos
        eventDidMount: function(info) {
            // Tooltip personalizado
            const tooltip = new Tooltip(info.el, {
                title: info.event.title,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        },
        
        // Configuración de vistas
        views: {
            dayGridMonth: {
                dayMaxEvents: 4,
                moreLinkClick: 'popover'
            },
            timeGridWeek: {
                slotMinTime: '07:00:00',
                slotMaxTime: '22:00:00'
            },
            timeGridDay: {
                slotMinTime: '07:00:00',
                slotMaxTime: '22:00:00'
            }
        },
        
        // Configuración de horarios
        slotMinTime: '07:00:00',
        slotMaxTime: '22:00:00',
        slotDuration: '00:30:00',
        slotLabelInterval: '01:00:00',
        
        // Configuración de colores
        eventColor: '#3b82f6',
        eventTextColor: '#ffffff',
        
        // Configuración de botones
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día',
            list: 'Lista'
        }
    });

    // Renderizar calendario
    calendar.render();

    // Función para mostrar modal de crear evento
    function showCreateEventModal(start, end) {
        const modal = document.createElement('div');
        modal.className = 'event-modal';
        modal.innerHTML = `
            <div class="event-modal-content">
                <div class="event-modal-header">
                    <h3 class="event-modal-title">Nueva Reserva</h3>
                    <button class="event-modal-close" onclick="this.closest('.event-modal').remove()">&times;</button>
                </div>
                <div class="event-modal-body">
                    <form id="createEventForm">
                        <div class="mb-4">
                            <label class="form-label">Título *</label>
                            <input type="text" name="title" class="form-input" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" class="form-textarea" rows="3"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="form-label">Inicio *</label>
                                <input type="datetime-local" name="start_date" class="form-input" 
                                       value="${start.toISOString().slice(0, 16)}" required>
                            </div>
                            <div>
                                <label class="form-label">Fin *</label>
                                <input type="datetime-local" name="end_date" class="form-input" 
                                       value="${end.toISOString().slice(0, 16)}" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="form-label">Tipo *</label>
                                <select name="type" class="form-select" required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="meeting">Reunión</option>
                                    <option value="event">Evento</option>
                                    <option value="appointment">Cita</option>
                                    <option value="other">Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Ubicación</label>
                                <input type="text" name="location" class="form-input">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="event-modal-actions">
                    <button type="button" class="btn-secondary" onclick="this.closest('.event-modal').remove()">
                        Cancelar
                    </button>
                    <button type="submit" form="createEventForm" class="btn-primary">
                        Crear Reserva
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Manejar envío del formulario
        document.getElementById('createEventForm').addEventListener('submit', function(e) {
            e.preventDefault();
            createEvent(this);
        });
    }

    // Función para mostrar modal de evento
    function showEventModal(event) {
        const modal = document.createElement('div');
        modal.className = 'event-modal';
        modal.innerHTML = `
            <div class="event-modal-content">
                <div class="event-modal-header">
                    <h3 class="event-modal-title">${event.title}</h3>
                    <button class="event-modal-close" onclick="this.closest('.event-modal').remove()">&times;</button>
                </div>
                <div class="event-modal-body">
                    <div class="event-detail">
                        <i class="fas fa-calendar event-detail-icon"></i>
                        <span class="event-detail-text">
                            ${event.start.toLocaleDateString('es-ES')} ${event.start.toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'})} - 
                            ${event.end.toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'})}
                        </span>
                    </div>
                    ${event.extendedProps.description ? `
                        <div class="event-detail">
                            <i class="fas fa-align-left event-detail-icon"></i>
                            <span class="event-detail-text">${event.extendedProps.description}</span>
                        </div>
                    ` : ''}
                    ${event.extendedProps.location ? `
                        <div class="event-detail">
                            <i class="fas fa-map-marker-alt event-detail-icon"></i>
                            <span class="event-detail-text">${event.extendedProps.location}</span>
                        </div>
                    ` : ''}
                    <div class="event-detail">
                        <i class="fas fa-tag event-detail-icon"></i>
                        <span class="event-detail-text">${event.extendedProps.type || 'Sin tipo'}</span>
                    </div>
                    <div class="event-detail">
                        <i class="fas fa-info-circle event-detail-icon"></i>
                        <span class="event-detail-text">${event.extendedProps.status || 'Pendiente'}</span>
                    </div>
                </div>
                <div class="event-modal-actions">
                    <button type="button" class="btn-edit" onclick="editEvent(${event.id})">
                        <i class="fas fa-edit mr-2"></i>Editar
                    </button>
                    <button type="button" class="btn-delete" onclick="deleteEvent(${event.id})">
                        <i class="fas fa-trash mr-2"></i>Eliminar
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

    // Función para crear evento
    async function createEvent(form) {
        const formData = new FormData(form);
        
        try {
            const response = await fetch('/reservations', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    title: formData.get('title'),
                    description: formData.get('description'),
                    start_date: formData.get('start_date'),
                    end_date: formData.get('end_date'),
                    type: formData.get('type'),
                    location: formData.get('location')
                })
            });

            if (response.ok) {
                const result = await response.json();
                
                // Agregar evento al calendario
                calendar.addEvent({
                    id: result.id,
                    title: result.title,
                    start: result.start_date,
                    end: result.end_date,
                    extendedProps: {
                        description: result.description,
                        type: result.type,
                        location: result.location,
                        status: result.status
                    }
                });
                
                // Cerrar modal
                document.querySelector('.event-modal').remove();
                
                // Mostrar mensaje de éxito
                showNotification('Reserva creada exitosamente', 'success');
                
                // Recargar eventos
                calendar.refetchEvents();
            } else {
                throw new Error('Error al crear la reserva');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al crear la reserva', 'error');
        }
    }

    // Función para actualizar fechas de evento
    async function updateEventDates(event) {
        try {
            const response = await fetch(`/reservations/${event.id}`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    start_date: event.start.toISOString(),
                    end_date: event.end.toISOString()
                })
            });

            if (response.ok) {
                showNotification('Evento actualizado exitosamente', 'success');
            } else {
                throw new Error('Error al actualizar el evento');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al actualizar el evento', 'error');
            calendar.refetchEvents(); // Revertir cambios
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

    // Funciones globales para botones
    window.editEvent = function(eventId) {
        window.location.href = `/reservations/${eventId}/edit`;
    };

    window.deleteEvent = function(eventId) {
        if (confirm('¿Estás seguro de que quieres eliminar esta reserva?')) {
            fetch(`/reservations/${eventId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            }).then(response => {
                if (response.ok) {
                    calendar.getEventById(eventId).remove();
                    document.querySelector('.event-modal').remove();
                    showNotification('Reserva eliminada exitosamente', 'success');
                }
            });
        }
    };

    // Exportar calendario para uso global
    window.calendar = calendar;
});
