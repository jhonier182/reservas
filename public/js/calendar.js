// Calendario funcional con FullCalendar
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar FullCalendar
    var calendarEl = document.getElementById('calendar');
    
    if (calendarEl) {

        let selectedLocation = null;
        // --- arriba, antes del Calendar ---
function buildViewMenu(calendar, anchorBtn) {
    // Cierra menús abiertos
    document.querySelectorAll('.fc-viewmenu').forEach(m => m.remove());
  
    const menu = document.createElement('div');
    menu.className = 'fc-viewmenu absolute z-50 mt-2 w-40 rounded-md border bg-white shadow-lg';
    menu.innerHTML = `
      <button data-view="timeGridDay"   class="w-full px-3 py-2 text-left hover:bg-gray-100">Día</button>
      <button data-view="timeGridWeek"  class="w-full px-3 py-2 text-left hover:bg-gray-100">Semana</button>
      <button data-view="dayGridMonth"  class="w-full px-3 py-2 text-left hover:bg-gray-100">Mes</button>
      
    `;
  
    // Posicionar debajo del botón
    const rect = anchorBtn.getBoundingClientRect();
    Object.assign(menu.style, {
      top: `${rect.bottom + window.scrollY}px`,
      left: `${rect.left + window.scrollX}px`
    });
  
    document.body.appendChild(menu);
  
    // Click en opción
    menu.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-view]');
      if (!btn) return;
      const view = btn.dataset.view;
  
      // Si no tienes el plugin de "multiMonthYear", caer a "dayGridMonth"
      if (view === 'multiMonthYear' && !calendar.viewSpecs['multiMonthYear']) {
        calendar.changeView('dayGridMonth');
      } else {
        calendar.changeView(view);
      }
  
      // Actualiza la etiqueta del botón con la vista activa
      const txt = (view === 'timeGridDay') ? 'Día' :
                  (view === 'timeGridWeek') ? 'Semana' :
                  (view === 'dayGridMonth') ? 'Mes' : 'Año';
      const anchorLabel = anchorBtn.querySelector('.fc-button-label');
      if (anchorLabel) anchorLabel.textContent = `Vista: ${txt}`;
      menu.remove();
    });
  
    // Cerrar al hacer click fuera / ESC
    const close = (ev) => {
      if (ev.type === 'keydown' && ev.key !== 'Escape') return;
      if (ev.type === 'mousedown' && menu.contains(ev.target)) return;
      menu.remove();
      document.removeEventListener('mousedown', close, true);
      document.removeEventListener('keydown', close, true);
    };
    document.addEventListener('mousedown', close, true);
    document.addEventListener('keydown', close, true);
  }
  
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    locale: 'es',
  
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'allBtn,jardinBtn,casinoBtn,viewFilter,listWeek'
    },
    customButtons: {
      // Filtros de sala
      allBtn:   { text: 'Todas',  click(){ selectedLocation = null;     calendar.refetchEvents(); } },
      jardinBtn:{ text: 'Jardín', click(){ selectedLocation = 'jardin'; calendar.refetchEvents(); } },
      casinoBtn:{ text: 'Casino', click(){ selectedLocation = 'casino'; calendar.refetchEvents(); } },
  
      // ✅ Deja SOLO esta versión de viewFilter
      viewFilter: {
        text: 'Vista: Mes', // etiqueta inicial
        click: function(ev) {
          const anchorBtn = this.el || ev.target;
          buildViewMenu(calendar, anchorBtn);
        }
      }
    },
  
    buttonText: { today:'Hoy', month:'Mes', week:'Semana', day:'Día', list:'Lista' },
  
    height: 'auto',
    editable: false,
    eventStartEditable: false,
    eventDurationEditable: false,
    selectable: true,
    selectMirror: true,
    dayMaxEvents: true,
    weekends: true,
  
    events: function(info, successCallback, failureCallback) {
      // Cargar reservas locales directamente
      loadLocalReservations(info.startStr, info.endStr, successCallback, selectedLocation);
    },
                

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
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
                                title: event.summary || 'Sin título',
                                start: event.start?.dateTime || event.start?.date,
                                end: event.end?.dateTime || event.end?.date,
                                description: event.description || '',
                                location: event.location || '',
                                backgroundColor: '#3B82F6',
                                borderColor: '#2563EB',
                                textColor: '#FFFFFF',
                                extendedProps: {
                                    googleEventId: event.id,
                                    description: event.description,
                                    location: event.location,
                                    attendees: event.attendees
                                }
                            };
                        });
                        
                        successCallback(events);
                    } else {
                        console.error('Error cargando eventos:', data.message);
                        successCallback([]);
                    }
                })
                .catch(error => {
                    console.error('Error en la petición:', error);
                    successCallback([]);
                });
            },

            
            // Seleccionar fecha para crear reserva
            select: function (info) {
              // Helpers
              const pad = n => String(n).padStart(2, '0');
              const toParam = d =>
                `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
            
              // Redondeo a cuartos (si lo usas)
              const floorQuarter = d => {
                const x = new Date(d); x.setSeconds(0,0);
                x.setMinutes(x.getMinutes() - (x.getMinutes() % 15));
                return x;
              };
            
              // Base desde FullCalendar
              let start = new Date(info.start);
            
              // Si es allDay (p.ej. seleccionaste un día en la vista mensual), define hora por defecto
              if (info.allDay) {
                start.setHours(9, 0, 0, 0); // 09:00, ajusta si quieres otra
              }
            
              // Redondear a 00/15/30/45 (opcional)
              start = floorQuarter(start);
            
              // 👉 Fuerza fin = inicio (misma fecha y hora)
              const end = new Date(start.getTime());
            
              // Armar querystring
              const params = new URLSearchParams({
                start_date: toParam(start),
                end_date:   toParam(end)
              });
            
              // Si estás filtrando por sala, inclúyela
              if (selectedLocation) params.set('location', selectedLocation);
            
              window.location.href = `/reservations/create?${params.toString()}`;
            },
            
              
            
            // Click en evento para ver detalles
            eventClick: function(info) {
                var event = info.event;
                var description = event.extendedProps.description || 'Sin descripción';
                var location = event.extendedProps.location || 'Sin ubicación';
                
                // Mostrar modal con detalles del evento
                showEventDetails(event.title, description, location, event.start, event.end);
            },
            
            // Arrastrar evento para cambiar fecha (solo admin)
            eventDrop: function(info) {
                if (!info.event.extendedProps?.canEdit) {
                    info.revert();
                    return;
                }
                // Solo administradores pueden arrastrar eventos
                // Aquí podrías implementar la actualización en Google Calendar
            },
            
            // Redimensionar evento para cambiar duración (solo admin)
            eventResize: function(info) {

                if (!info.event.extendedProps?.canEdit) {
                    info.revert();
                    return;
                }
                // Solo administradores pueden redimensionar eventos

                var event = info.event;
                console.log('Evento redimensionado:', event.title, 'duración:', event.start, 'a', event.end);
                // Aquí podrías implementar la actualización en Google Calendar
            },
            
            // Configuración específica para la vista de lista
            views: {
                listMonth: {
                    listDayFormat: { weekday: 'long', month: 'long', day: 'numeric' },
                    listDaySideFormat: { month: 'long', day: 'numeric', year: 'numeric' },
                    noEventsMessage: 'No hay eventos para mostrar en este mes',
                    eventDisplay: 'block',
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        meridiem: false,
                        hour12: false
                    }
                },
                listWeek: {
                    listDayFormat: { weekday: 'long', month: 'long', day: 'numeric' },
                    listDaySideFormat: { month: 'long', day: 'numeric', year: 'numeric' },
                    noEventsMessage: 'No hay eventos para mostrar en esta semana',
                    eventDisplay: 'block',
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        meridiem: false,
                        hour12: false
                    }
                },
                listDay: {
                    listDayFormat: { weekday: 'long', month: 'long', day: 'numeric' },
                    listDaySideFormat: { month: 'long', day: 'numeric', year: 'numeric' },
                    noEventsMessage: 'No hay eventos para mostrar en este día',
                    eventDisplay: 'block',
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        meridiem: false,
                        hour12: false
                    }
                }
            },
            
            // Personalizar el renderizado de eventos en la vista de lista
            eventDidMount: function(info) {
                // Agregar tooltip con información del evento
                if (info.view.type.includes('list')) {
                    var event = info.event;
                    var tooltip = event.extendedProps.description || event.extendedProps.location || '';
                    
                    if (tooltip) {
                        info.el.setAttribute('title', tooltip);
                    }
                    
                    // Agregar enlace a Google Calendar si existe
                    if (event.extendedProps.htmlLink) {
                        var link = document.createElement('a');
                        link.href = event.extendedProps.htmlLink;
                        link.target = '_blank';
                        link.className = 'ml-2 text-blue-600 hover:text-blue-800 text-xs';
                        link.innerHTML = '<i class="fas fa-external-link-alt"></i>';
                        info.el.appendChild(link);
                    }
                }

            }

        });
        
        calendar.render();
    }

    
                // Reemplaza el botón por un <select> nativo
                /* ===== Inyectar combobox dentro del header ===== */
        (function injectViewSelect(){
          // Ubica el botón generado por FullCalendar y reemplázalo por un <select>
          const btn = calendarEl.querySelector('.fc-viewSelect-button');
          if (!btn) return;

          // Limpia contenido del botón y evita estilos raros
          btn.innerHTML = '';
          btn.classList.remove('fc-button-primary');   // que no se vea como botón
          btn.classList.add('p-0', 'border-0', 'bg-transparent');

          const select = document.createElement('select');
          // estilos simples, o puedes copiar clases de tu framework
          select.className = 'border rounded px-2 py-1 text-sm';
          select.innerHTML = `
            <option value="timeGridDay">Día</option>
            <option value="timeGridWeek">Semana</option>
            <option value="dayGridMonth">Mes</option>
            <option value="listYear">Año</option>
          `;

          // Selecciona la vista actual
          const current = calendar.view.type;
          const opt = [...select.options].find(o => o.value === current);
          if (opt) opt.selected = true;

          // Cambiar vista
          select.addEventListener('change', function () {
            const v = this.value;
            calendar.changeView(v);
          });

          btn.appendChild(select);
        })();

    
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
    function loadLocalReservations(startDate, endDate, successCallback, selectedLocation = null) {
        let url = `/reservas?start_date=${startDate}&end_date=${endDate}`;
        if (selectedLocation) url += `&location=${selectedLocation}`;
        
        console.log('Cargando reservas desde:', url);
      
        fetch(url, {
          method: 'GET',
          headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' },
          credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(data => {
          console.log('Respuesta del servidor:', data);
          let evs = data.success ? data.events : [];
          console.log('Eventos encontrados:', evs.length);
          
          if (selectedLocation) {
            const norm = s => (s||'').toString().trim().toLowerCase();
            evs = evs.filter(e => norm(e.extendedProps?.location || e.location) === selectedLocation);
          }
          successCallback(evs);
        })
        .catch(err => {
          console.error('Error cargando reservas locales:', err);
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
