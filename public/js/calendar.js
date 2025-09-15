// Calendario funcional con FullCalendar
document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  if (!calendarEl) return;

  let selectedLocation = null;

  // ---- Menú mejorado para cambiar vista (Día/Semana/Mes)
  function buildViewMenu(calendar, anchorBtn) {
    document.querySelectorAll('.fc-viewmenu').forEach(m => m.remove());

    const menu = document.createElement('div');
    menu.className = 'fc-viewmenu absolute z-50 mt-2 w-48 rounded-lg border border-gray-200 bg-white shadow-xl';
    menu.innerHTML = `
      <div class="px-3 py-2 border-b border-gray-100 bg-gray-50">
        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Vista del Calendario</span>
      </div>
      <button data-view="dayGridMonth" class="w-full px-4 py-3 text-left hover:bg-blue-50 flex items-center">
        <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
        <div>
          <div class="font-medium text-gray-900">Mes</div>
          <div class="text-xs text-gray-500">Vista mensual completa</div>
        </div>
      </button>
      <button data-view="timeGridWeek" class="w-full px-4 py-3 text-left hover:bg-blue-50 flex items-center">
        <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
        <div>
          <div class="font-medium text-gray-900">Semana</div>
          <div class="text-xs text-gray-500">Vista semanal detallada</div>
        </div>
      </button>
      <button data-view="timeGridDay" class="w-full px-4 py-3 text-left hover:bg-blue-50 flex items-center">
        <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
        <div>
          <div class="font-medium text-gray-900">Día</div>
          <div class="text-xs text-gray-500">Vista diaria detallada</div>
        </div>
      </button>
    `;

    const rect = anchorBtn.getBoundingClientRect();
    Object.assign(menu.style, {
      top: `${rect.bottom + window.scrollY + 4}px`,
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

  // ---- Menú para filtrar por sala
  function buildLocationMenu(calendar, anchorBtn) {
    document.querySelectorAll('.fc-locationmenu').forEach(m => m.remove());

    const menu = document.createElement('div');
    menu.className = 'fc-locationmenu absolute z-50 mt-2 w-56 rounded-lg border border-gray-200 bg-white shadow-xl';
    menu.innerHTML = `
      <div class="px-3 py-2 border-b border-gray-100 bg-gray-50">
        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Filtrar por Sala</span>
      </div>
      <button data-location="all" class="w-full px-4 py-3 text-left hover:bg-green-50 flex items-center">
        <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
        <div>
          <div class="font-medium text-gray-900">Todas las Salas</div>
          <div class="text-xs text-gray-500">Mostrar todas las reservas</div>
        </div>
      </button>
      <button data-location="jardin" class="w-full px-4 py-3 text-left hover:bg-green-50 flex items-center">
        <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
        <div>
          <div class="font-medium text-gray-900">Jardín</div>
          <div class="text-xs text-gray-500">Solo reservas del jardín</div>
        </div>
      </button>
      <button data-location="casino" class="w-full px-4 py-3 text-left hover:bg-green-50 flex items-center">
        <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
        <div>
          <div class="font-medium text-gray-900">Casino</div>
          <div class="text-xs text-gray-500">Solo reservas del casino</div>
        </div>
      </button>
    `;

    const rect = anchorBtn.getBoundingClientRect();
    Object.assign(menu.style, {
      top: `${rect.bottom + window.scrollY + 4}px`,
      left: `${rect.left + window.scrollX}px`
    });

    document.body.appendChild(menu);

    menu.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-location]');
      if (!btn) return;
      const location = btn.dataset.location === 'all' ? null : btn.dataset.location;
      selectedLocation = location;
      calendar.refetchEvents();

      const txt = 
        location === null ? 'Todas las Salas' :
        location === 'jardin' ? 'Jardín' : 'Casino';

      const anchorLabel = anchorBtn.querySelector('.fc-button-label');
      if (anchorLabel) anchorLabel.textContent = txt;
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
    timeZone: 'local',
    editable: true,

    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'locationFilter,viewFilter'
    },

    customButtons: {
      // Filtro de sala como dropdown
      locationFilter: {
        text: 'Todas las Salas',
        click: function (ev) {
          const anchorBtn = this.el || ev.target;
          buildLocationMenu(calendar, anchorBtn);
        }
      },

      // Menú de vista mejorado
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
      const squad = event.extendedProps.squad || 'No especificado';
      const people = event.extendedProps.people ?? null; // 👈 nada de poner 1 por defecto aquí
      const canEdit = !!event.extendedProps.canEdit;
      showEventDetails({
        id: event.id,
        title: event.title,
        description,
        location,
        start: event.start,
        end: event.end,
        responsible,
        people,
        squad,
        canEdit
      });
    },

    // Solo admin/owner pueden mover/redimensionar (el backend ya marca canEdit)
    eventDrop (info) {
      if (!info.event.extendedProps?.canEdit) {
        info.revert(); return;
      }
    },
    eventResize(info) {
      if (!info.event.extendedProps?.canEdit) {
        info.revert();
        return;
      }
      const event = info.event;
      console.log('Evento redimensionado:', event.title, 'de', event.start, 'a', event.end);
    },


    eventDidMount: function (info) {
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
  });

  calendar.render();

  // -------- Helpers --------
  function showEventDetails(data) {
    const { id, title, description, location, start, end, responsible, people, squad, canEdit } = data;
  
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
            <div>
              <label class="text-sm font-medium text-gray-700">Escuadrón:</label>
              <p class="text-sm text-gray-900">${squad}</p>
            </div>

          </div>
          <div class="mt-6 flex justify-between items-center">
            <div class="space-x-2 ${canEdit ? '' : 'hidden'}">
              <a href="/reservations/${id}/edit" class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Editar</a>
              <button data-action="delete" class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700">Eliminar</button>
            </div>
            <button class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400" data-action="close">Cerrar</button>
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
    const closeModal = () => modal.remove();
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    modal.querySelector('[data-action="close"]').addEventListener('click', closeModal);
    const delBtn = modal.querySelector('[data-action="delete"]');
    if (delBtn) {
      delBtn.addEventListener('click', async () => {
        if (!confirm('¿Seguro que deseas eliminar esta reserva?')) return;
        try {
          const res = await fetch(`/reservations/${id}`, {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': getCsrfToken(),
              'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: new URLSearchParams({ _method: 'DELETE' })
          });
          if (!res.ok) throw new Error('Error eliminando reserva');
          closeModal();
          calendar.refetchEvents();
        } catch (err) {
          console.error(err);
          alert('No se pudo eliminar la reserva');
        }
      });
    }
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

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }
});
