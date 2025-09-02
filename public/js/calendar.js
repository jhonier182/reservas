// Calendario funcional con FullCalendar
document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  if (!calendarEl) return;

  let selectedLocation = null;

  // ---- Menú simple para cambiar vista (Día/Semana/Mes)
  function buildViewMenu(calendar, anchorBtn) {
    document.querySelectorAll('.fc-viewmenu').forEach(m => m.remove());

    const menu = document.createElement('div');
    menu.className = 'fc-viewmenu absolute z-50 mt-2 w-40 rounded-md border bg-white shadow-lg';
    menu.innerHTML = `
      <button data-view="timeGridDay"  class="w-full px-3 py-2 text-left hover:bg-gray-100">Día</button>
      <button data-view="timeGridWeek" class="w-full px-3 py-2 text-left hover:bg-gray-100">Semana</button>
      <button data-view="dayGridMonth" class="w-full px-3 py-2 text-left hover:bg-gray-100">Mes</button>
    `;

    const rect = anchorBtn.getBoundingClientRect();
    Object.assign(menu.style, {
      top: `${rect.bottom + window.scrollY}px`,
      left: `${rect.left + window.scrollX}px`
    });

    document.body.appendChild(menu);

    menu.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-view]');
      if (!btn) return;
      const view = btn.dataset.view;
      calendar.changeView(view);

      const txt =
        view === 'timeGridDay'  ? 'Día' :
        view === 'timeGridWeek' ? 'Semana' : 'Mes';

      const anchorLabel = anchorBtn.querySelector('.fc-button-label');
      if (anchorLabel) anchorLabel.textContent = `Vista: ${txt}`;
      menu.remove();
    });

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

  // ---- ÚNICA inicialización de FullCalendar
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
      allBtn:    { text: 'Todas',  click(){ selectedLocation = null;     calendar.refetchEvents(); } },
      jardinBtn: { text: 'Jardín', click(){ selectedLocation = 'jardin'; calendar.refetchEvents(); } },
      casinoBtn: { text: 'Casino', click(){ selectedLocation = 'casino'; calendar.refetchEvents(); } },

      // Menú de vista
      viewFilter: {
        text: 'Vista: Mes',
        click: function (ev) {
          const anchorBtn = this.el || ev.target;
          buildViewMenu(calendar, anchorBtn);
        }
      }
    },

    buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día', list: 'Lista' },

    height: 'auto',
    editable: true,
    eventStartEditable: true,
    eventDurationEditable: true,
    selectable: true,
    selectMirror: true,
    dayMaxEvents: true,
    weekends: true,

    // Cargar reservas locales
    events: function (info, success, failure) {
      loadLocalReservations(info.startStr, info.endStr, success, selectedLocation);
    },

    // Selección para crear reserva
    select: function (info) {
      const pad = n => String(n).padStart(2, '0');
      const toParam = d =>
        `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;

      const floorQuarter = d => {
        const x = new Date(d); x.setSeconds(0, 0);
        x.setMinutes(x.getMinutes() - (x.getMinutes() % 15));
        return x;
      };

      let start = new Date(info.start);
      if (info.allDay) start.setHours(9, 0, 0, 0);
      start = floorQuarter(start);

      const end = new Date(start.getTime()); // mismo inicio = mismo fin

      const params = new URLSearchParams({
        start_date: toParam(start),
        end_date: toParam(end)
      });
      if (selectedLocation) params.set('location', selectedLocation);

      window.location.href = `/reservations/create?${params.toString()}`;
    },

    eventClick: function (info) {
      const event = info.event;
      const description = event.extendedProps.description || 'Sin descripción';
      const location    = event.extendedProps.location    || 'Sin ubicación';
      const responsible = event.extendedProps.responsible || 'No especificado';
      const people      = (event.extendedProps.people ?? event.extendedProps.attendees?.length) ?? null;

  showEventDetails(event.title, description, location, event.start, event.end, {
    responsible,
    people
  });
    },

    // Solo admin/owner pueden mover/redimensionar (el backend ya marca canEdit)
    eventDrop: function (info) {
      if (!info.event.extendedProps?.canEdit) {
        info.revert();
      }
    },
    eventResize: function (info) {
      if (!info.event.extendedProps?.canEdit) {
        info.revert();
        return;
      }
      const event = info.event;
      console.log('Evento redimensionado:', event.title, 'de', event.start, 'a', event.end);
    },

    // Ajustes de vistas de lista (si las usas)
    views: {
      listMonth: {
        listDayFormat: { weekday: 'long', month: 'long', day: 'numeric' },
        listDaySideFormat: { month: 'long', day: 'numeric', year: 'numeric' },
        noEventsMessage: 'No hay eventos para mostrar en este mes',
        eventDisplay: 'block',
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false, hour12: false }
      },
      listWeek: {
        listDayFormat: { weekday: 'long', month: 'long', day: 'numeric' },
        listDaySideFormat: { month: 'long', day: 'numeric', year: 'numeric' },
        noEventsMessage: 'No hay eventos para mostrar en esta semana',
        eventDisplay: 'block',
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false, hour12: false }
      },
      listDay: {
        listDayFormat: { weekday: 'long', month: 'long', day: 'numeric' },
        listDaySideFormat: { month: 'long', day: 'numeric', year: 'numeric' },
        noEventsMessage: 'No hay eventos para mostrar en este día',
        eventDisplay: 'block',
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false, hour12: false }
      }
    },

    eventDidMount: function (info) {
      if (info.view.type.includes('list')) {
        const tooltip = info.event.extendedProps.description || info.event.extendedProps.location || '';
        if (tooltip) info.el.setAttribute('title', tooltip);
        if (info.event.extendedProps.htmlLink) {
          const link = document.createElement('a');
          link.href = info.event.extendedProps.htmlLink;
          link.target = '_blank';
          link.className = 'ml-2 text-blue-600 hover:text-blue-800 text-xs';
          link.innerHTML = '<i class="fas fa-external-link-alt"></i>';
          info.el.appendChild(link);
        }
      }
    }
  });

  calendar.render();

  // -------- Helpers --------
  function showEventDetails(title, description, location, start, end, extra = {}) {
    const { responsible, people } = extra;
  
    const peopleText = (people === null || people === undefined)
      ? 'No especificado'
      : `${people}`;
  
    const modal = document.createElement('div');
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
              <label class="text-sm font-medium text-gray-700">Responsable:</label>
              <p class="text-sm text-gray-900">${responsible || 'No especificado'}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Personas:</label>
              <p class="text-sm text-gray-900">${peopleText}</p>
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
            <button class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400" onclick="this.closest('.fixed').remove()">Cerrar</button>
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
    modal.addEventListener('click', e => { if (e.target === modal) modal.remove(); });
  }
  

  function loadLocalReservations(startDate, endDate, successCallback, selectedLocation = null) {
    let url = `/reservas?start_date=${startDate}&end_date=${endDate}`;
    if (selectedLocation) url += `&location=${selectedLocation}`;

    fetch(url, {
      method: 'GET',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(data => {
        let evs = data.success ? data.events : [];
        if (selectedLocation) {
          const norm = s => (s || '').toString().trim().toLowerCase();
          evs = evs.filter(e => norm(e.extendedProps?.location || e.location) === selectedLocation);
        }
        successCallback(evs);
      })
      .catch(err => {
        console.error('Error cargando reservas locales:', err);
        successCallback([]);
      });
  }

  function formatDateTime(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    let hours = date.getHours();
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12; hours = hours ? hours : 12;
    return `${day}/${month}/${year} ${hours}:${minutes} ${ampm}`;
  }
});
