# Sistema de Permisos de Reservas

## Descripción General

Este sistema permite que **todos los usuarios autenticados puedan ver todas las reservas** del sistema, pero **solo los administradores pueden modificarlas**.

## Funcionalidades por Tipo de Usuario

### Usuarios Regulares
- ✅ **Ver todas las reservas** en el calendario
- ✅ **Ver todas las reservas** en la lista de reservas
- ✅ **Ver detalles** de cualquier reserva
- ✅ **Crear nuevas reservas** para sí mismos
- ❌ **Editar reservas** existentes
- ❌ **Eliminar reservas** existentes
- ❌ **Cambiar estado** de las reservas

### Administradores
- ✅ **Ver todas las reservas** en el calendario
- ✅ **Ver todas las reservas** en la lista de reservas
- ✅ **Ver detalles** de cualquier reserva
- ✅ **Crear nuevas reservas** para cualquier usuario
- ✅ **Editar cualquier reserva** (fechas, ubicación, descripción, etc.)
- ✅ **Eliminar cualquier reserva**
- ✅ **Cambiar estado** de cualquier reserva
- ✅ **Arrastrar y redimensionar** eventos en el calendario

## Cómo Funciona

### 1. Políticas de Autorización
El sistema utiliza las políticas de Laravel (`ReservationPolicy`) para controlar el acceso:

```php
// Todos pueden ver
public function viewAny(User $user = null): bool
{
    return true;
}

public function view(User $user = null, Reservation $reservation): bool
{
    return true;
}

// Solo administradores pueden modificar
public function update(User $user, Reservation $reservation): bool
{
    return $user->isAdmin();
}

public function delete(User $user, Reservation $reservation): bool
{
    return $user->isAdmin();
}
```

### 2. Controlador
El `ReservationController` aplica estas políticas:

```php
// Método show - todos pueden ver
public function show(Reservation $reservation): View
{
    return view('reservations.show', compact('reservation'));
}

// Método edit - solo administradores
public function edit(Reservation $reservation): View
{
    $this->authorize('update', $reservation);
    return view('reservations.edit', compact('reservation'));
}
```

### 3. Vistas
Las vistas muestran botones y funcionalidades según los permisos:

```blade
@can('update', $reservation)
    <a href="{{ route('reservations.edit', $reservation) }}" class="btn-primary">
        <i class="fas fa-edit mr-2"></i>Editar
    </a>
@else
    <div class="text-sm text-gray-600 bg-yellow-50 p-3 rounded-lg">
        Solo los administradores pueden editar esta reserva
    </div>
@endcan
```

### 4. Calendario JavaScript
El calendario respeta los permisos:

```javascript
// Solo administradores pueden arrastrar eventos
eventDrop: function(info) {
    if (!info.event.extendedProps?.canEdit) {
        info.revert();
        return;
    }
    // Lógica de actualización...
}
```

## Configuración

### 1. Crear Usuario Administrador
Para crear un usuario administrador, usa el comando artisan:

```bash
php artisan user:create-admin admin@example.com "Nombre Admin" "password123"
```

### 2. Verificar Rol de Usuario
El sistema verifica si un usuario es administrador usando:

```php
public function isAdmin(): bool
{
    return $this->role === 'admin';
}
```

### 3. Middleware de Administrador
El middleware `AdminMiddleware` protege las rutas administrativas:

```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/reservas', [ReservationController::class, 'index']);
});
```

## Rutas Protegidas

### Rutas Públicas (con autenticación)
- `/home` - Dashboard principal
- `/calendar` - Vista del calendario
- `/reservations` - Lista de todas las reservas
- `/reservations/{id}` - Ver detalles de reserva
- `/reservations/create` - Crear nueva reserva

### Rutas de Administrador
- `/reservations/{id}/edit` - Editar reserva
- `/reservations/{id}` (DELETE) - Eliminar reserva
- `/reservations/{id}/change-status` - Cambiar estado
- `/reservations/{id}/mark-completed` - Marcar como completada

## Seguridad

### 1. Verificación en Frontend y Backend
- **Frontend**: Las vistas ocultan botones según permisos
- **Backend**: Las políticas y middleware verifican permisos
- **API**: Todas las operaciones están protegidas

### 2. Prevención de Acceso No Autorizado
- Usuarios no pueden acceder a rutas de edición
- Intentos de modificación son bloqueados por políticas
- Errores 403 para accesos no autorizados

### 3. Auditoría
- Todas las operaciones quedan registradas en la base de datos
- Se mantiene el historial de cambios

## Uso del Sistema

### Para Usuarios Regulares
1. **Ver Calendario**: Accede a `/calendar` para ver todas las reservas
2. **Ver Lista**: Accede a `/reservations` para ver todas las reservas
3. **Crear Reserva**: Haz clic en una fecha del calendario o ve a `/reservations/create`
4. **Ver Detalles**: Haz clic en cualquier evento del calendario

### Para Administradores
1. **Todas las funcionalidades de usuarios regulares**
2. **Editar Reservas**: Haz clic en "Editar" en cualquier reserva
3. **Eliminar Reservas**: Usa el botón "Eliminar" en cualquier reserva
4. **Modificar en Calendario**: Arrastra y redimensiona eventos directamente
5. **Cambiar Estados**: Usa los botones de cambio de estado

## Personalización

### Cambiar Permisos
Para modificar los permisos, edita `app/Policies/ReservationPolicy.php`:

```php
// Ejemplo: Permitir que los propietarios también editen
public function update(User $user, Reservation $reservation): bool
{
    return $user->isAdmin() || $user->id === $reservation->user_id;
}
```

### Agregar Nuevos Roles
Para agregar nuevos roles, modifica el modelo `User`:

```php
public function hasRole($role): bool
{
    return $this->role === $role;
}

public function isModerator(): bool
{
    return $this->role === 'moderator';
}
```

## Solución de Problemas

### Error 403 - Acceso Denegado
- Verifica que el usuario tenga el rol correcto
- Asegúrate de que la política esté configurada correctamente
- Revisa que el middleware esté aplicado en las rutas correctas

### Botones No Aparecen
- Verifica que las directivas `@can` estén correctamente escritas
- Asegúrate de que las políticas devuelvan `true` para los permisos esperados
- Revisa que el usuario esté autenticado

### Calendario No Permite Edición
- Verifica que `extendedProps.canEdit` esté configurado correctamente
- Asegúrate de que el usuario sea administrador
- Revisa la consola del navegador para errores JavaScript

## Mantenimiento

### Actualizar Permisos
1. Modifica las políticas en `app/Policies/ReservationPolicy.php`
2. Actualiza las vistas para reflejar los nuevos permisos
3. Prueba las funcionalidades con diferentes tipos de usuario

### Agregar Nuevas Funcionalidades
1. Define los permisos en la política correspondiente
2. Aplica las políticas en el controlador
3. Actualiza las vistas para mostrar/ocultar elementos según permisos
4. Prueba la funcionalidad con diferentes roles de usuario

