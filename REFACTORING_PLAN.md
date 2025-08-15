# 🏗️ Plan de Refactoring - Reservas Belt

## 📋 **Estado Actual vs Estado Deseado**

### **❌ Problemas Identificados:**
- Controladores con múltiples responsabilidades
- Falta de modelos para entidades de negocio
- Lógica de negocio mezclada en controladores
- Falta de servicios para operaciones complejas

### **✅ Objetivos del Refactoring:**
- Separar controladores por responsabilidad única
- Crear modelos para reservas y eventos
- Implementar servicios para lógica de negocio
- Mejorar la arquitectura MVC

---

## 🔧 **1. Separación de Controladores por Responsabilidad**

### **AuthController.php** (Nuevo)
```php
<?php
namespace App\Http\Controllers;

class AuthController extends Controller
{
    public function redirectToGoogle()
    public function handleGoogleCallback()
    public function logout()
}
```

### **ReservationController.php** (Nuevo)
```php
<?php
namespace App\Http\Controllers;

class ReservationController extends Controller
{
    public function index()           // Listar reservas
    public function create()          // Formulario crear
    public function store()           // Guardar reserva
    public function show($id)         // Ver reserva
    public function edit($id)         // Formulario editar
    public function update($id)       // Actualizar reserva
    public function destroy($id)      // Eliminar reserva
}
```

### **GoogleCalendarController.php** (Refactorizado)
```php
<?php
namespace App\Http\Controllers;

class GoogleCalendarController extends Controller
{
    public function redirectToGoogle()
    public function handleGoogleCallback()
    public function syncEvents()      // Sincronizar eventos
    public function createEvent()     // Crear evento
    public function listEvents()      // Listar eventos
}
```

### **HomeController.php** (Simplificado)
```php
<?php
namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()           // Dashboard principal
    public function calendar()        // Vista del calendario
}
```

---

## 🗄️ **2. Crear Modelos para Entidades de Negocio**

### **Reservation.php** (Nuevo)
```php
<?php
namespace App\Models;

class Reservation extends Model
{
    protected $fillable = [
        'title', 'description', 'start_date', 'end_date',
        'location', 'user_id', 'status', 'type'
    ];

    // Relaciones
    public function user()
    public function events()
    
    // Scopes
    public function scopeActive()
    public function scopeByDate()
    
    // Accessors/Mutators
    public function getFormattedDateAttribute()
}
```

### **Event.php** (Nuevo)
```php
<?php
namespace App\Models;

class Event extends Model
{
    protected $fillable = [
        'google_event_id', 'title', 'description',
        'start_datetime', 'end_datetime', 'location',
        'reservation_id', 'status'
    ];

    // Relaciones
    public function reservation()
    
    // Métodos
    public function syncWithGoogle()
    public function isRecurring()
}
```

### **Calendar.php** (Nuevo)
```php
<?php
namespace App\Models;

class Calendar extends Model
{
    protected $fillable = [
        'name', 'google_calendar_id', 'user_id',
        'is_primary', 'color', 'timezone'
    ];

    // Relaciones
    public function user()
    public function events()
}
```

### **User.php** (Mejorado)
```php
<?php
namespace App\Models;

class User extends Authenticatable
{
    // Agregar campos
    protected $fillable = [
        'name', 'email', 'google_id', 'avatar',
        'timezone', 'preferences'
    ];

    // Relaciones
    public function reservations()
    public function calendars()
    public function events()
}
```

---

## 🚀 **3. Implementar Servicios para Lógica de Negocio**

### **GoogleAuthService.php** (Nuevo)
```php
<?php
namespace App\Services;

class GoogleAuthService
{
    public function validateDomain(string $email): bool
    public function createOrUpdateUser(array $googleUser): User
    public function handleCallback(string $code): User
    public function refreshToken(User $user): bool
}
```

### **ReservationService.php** (Nuevo)
```php
<?php
namespace App\Services;

class ReservationService
{
    public function createReservation(array $data): Reservation
    public function updateReservation(Reservation $reservation, array $data): bool
    public function deleteReservation(Reservation $reservation): bool
    public function checkAvailability(string $startDate, string $endDate, string $location): bool
    public function getConflictingReservations(Reservation $reservation): Collection
}
```

### **GoogleCalendarService.php** (Nuevo)
```php
<?php
namespace App\Services;

class GoogleCalendarService
{
    public function createEvent(Reservation $reservation): Event
    public function updateEvent(Event $event, array $data): bool
    public function deleteEvent(Event $event): bool
    public function syncEvents(User $user): array
    public function getEventDetails(string $eventId): array
}
```

### **NotificationService.php** (Nuevo)
```php
<?php
namespace App\Services;

class NotificationService
{
    public function sendReservationConfirmation(Reservation $reservation): void
    public function sendReminder(Reservation $reservation): void
    public function sendCancellationNotice(Reservation $reservation): void
}
```

---

## 📁 **4. Nueva Estructura de Directorios**

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── HomeController.php
│   │   ├── ReservationController.php
│   │   └── GoogleCalendarController.php
│   ├── Requests/                    # Validaciones
│   │   ├── CreateReservationRequest.php
│   │   └── UpdateReservationRequest.php
│   └── Resources/                   # Transformación de datos
│       ├── ReservationResource.php
│       └── EventResource.php
├── Models/
│   ├── User.php
│   ├── Reservation.php
│   ├── Event.php
│   └── Calendar.php
├── Services/                        # Lógica de negocio
│   ├── GoogleAuthService.php
│   ├── ReservationService.php
│   ├── GoogleCalendarService.php
│   └── NotificationService.php
├── Events/                          # Eventos del sistema
│   ├── ReservationCreated.php
│   ├── ReservationUpdated.php
│   └── ReservationCancelled.php
└── Listeners/                       # Manejadores de eventos
    ├── SendReservationConfirmation.php
    └── SyncWithGoogleCalendar.php
```

---

## 🔄 **5. Flujo de Refactoring Detallado**

### **🎯 Fase 1: Crear Modelos y Base de Datos**
**Objetivo:** Establecer la base de datos y modelos del sistema

#### **1.1 Crear Modelo Reservation**
- [ ] Crear `app/Models/Reservation.php`
- [ ] Definir campos fillable y relaciones
- [ ] Crear migración `create_reservations_table`
- [ ] Crear factory para datos de prueba
- [ ] Crear seeder para datos iniciales

#### **1.2 Crear Modelo Event**
- [ ] Crear `app/Models/Event.php`
- [ ] Definir campos fillable y relaciones
- [ ] Crear migración `create_events_table`
- [ ] Crear factory para datos de prueba

#### **1.3 Crear Modelo Calendar**
- [ ] Crear `app/Models/Calendar.php`
- [ ] Definir campos fillable y relaciones
- [ ] Crear migración `create_calendars_table`
- [ ] Crear factory para datos de prueba

#### **1.4 Mejorar Modelo User**
- [ ] Agregar campos `google_id`, `avatar`, `timezone`
- [ ] Crear migración para agregar campos
- [ ] Definir relaciones con otros modelos
- [ ] Crear accessors y mutators

#### **1.5 Configurar Base de Datos**
- [ ] Ejecutar migraciones
- [ ] Ejecutar seeders
- [ ] Verificar relaciones en tinker
- [ ] Crear índices para optimización

---

### **🚀 Fase 2: Crear Servicios de Lógica de Negocio**
**Objetivo:** Separar la lógica de negocio de los controladores

#### **2.1 Crear GoogleAuthService**
- [ ] Crear `app/Services/GoogleAuthService.php`
- [ ] Implementar `validateDomain()`
- [ ] Implementar `createOrUpdateUser()`
- [ ] Implementar `handleCallback()`
- [ ] Implementar `refreshToken()`
- [ ] Crear tests unitarios

#### **2.2 Crear ReservationService**
- [ ] Crear `app/Services/ReservationService.php`
- [ ] Implementar `createReservation()`
- [ ] Implementar `updateReservation()`
- [ ] Implementar `deleteReservation()`
- [ ] Implementar `checkAvailability()`
- [ ] Implementar `getConflictingReservations()`
- [ ] Crear tests unitarios

#### **2.3 Crear GoogleCalendarService**
- [ ] Crear `app/Services/GoogleCalendarService.php`
- [ ] Implementar `createEvent()`
- [ ] Implementar `updateEvent()`
- [ ] Implementar `deleteEvent()`
- [ ] Implementar `syncEvents()`
- [ ] Implementar `getEventDetails()`
- [ ] Crear tests unitarios

#### **2.4 Crear NotificationService**
- [ ] Crear `app/Services/NotificationService.php`
- [ ] Implementar `sendReservationConfirmation()`
- [ ] Implementar `sendReminder()`
- [ ] Implementar `sendCancellationNotice()`
- [ ] Crear tests unitarios

---

### **🔧 Fase 3: Refactorizar Controladores**
**Objetivo:** Separar responsabilidades y simplificar controladores

#### **3.1 Crear AuthController**
- [ ] Crear `app/Http/Controllers/AuthController.php`
- [ ] Mover `redirectToGoogle()` desde GoogleController
- [ ] Mover `handleGoogleCallback()` desde GoogleController
- [ ] Implementar `logout()`
- [ ] Actualizar rutas
- [ ] Probar funcionalidad

#### **3.2 Crear ReservationController**
- [ ] Crear `app/Http/Controllers/ReservationController.php`
- [ ] Implementar `index()` - Listar reservas
- [ ] Implementar `create()` - Formulario crear
- [ ] Implementar `store()` - Guardar reserva
- [ ] Implementar `show($id)` - Ver reserva
- [ ] Implementar `edit($id)` - Formulario editar
- [ ] Implementar `update($id)` - Actualizar reserva
- [ ] Implementar `destroy($id)` - Eliminar reserva
- [ ] Crear vistas correspondientes
- [ ] Actualizar rutas

#### **3.3 Refactorizar GoogleCalendarController**
- [ ] Limpiar `GoogleCalendarController.php`
- [ ] Remover métodos de autenticación
- [ ] Implementar `syncEvents()`
- [ ] Implementar `createEvent()`
- [ ] Implementar `listEvents()`
- [ ] Usar GoogleCalendarService
- [ ] Probar funcionalidad

#### **3.4 Simplificar HomeController**
- [ ] Limpiar `HomeController.php`
- [ ] Mantener solo `index()` para dashboard
- [ ] Agregar `calendar()` para vista de calendario
- [ ] Usar servicios para obtener datos
- [ ] Probar funcionalidad

---

### **📝 Fase 4: Crear Requests y Resources**
**Objetivo:** Implementar validaciones y transformaciones de datos

#### **4.1 Crear Request Classes**
- [ ] Crear `app/Http/Requests/CreateReservationRequest.php`
- [ ] Crear `app/Http/Requests/UpdateReservationRequest.php`
- [ ] Crear `app/Http/Requests/CreateEventRequest.php`
- [ ] Crear `app/Http/Requests/UpdateEventRequest.php`
- [ ] Implementar reglas de validación
- [ ] Implementar mensajes personalizados

#### **4.2 Crear Resource Classes**
- [ ] Crear `app/Http/Resources/ReservationResource.php`
- [ ] Crear `app/Http/Resources/EventResource.php`
- [ ] Crear `app/Http/Resources/UserResource.php`
- [ ] Implementar transformaciones de datos
- [ ] Implementar colecciones de recursos

#### **4.3 Implementar Middleware Personalizado**
- [ ] Crear `app/Http/Middleware/CheckDomainAccess.php`
- [ ] Crear `app/Http/Middleware/CheckReservationAccess.php`
- [ ] Registrar middleware en Kernel
- [ ] Aplicar a rutas correspondientes

---

### **🎉 Fase 5: Crear Eventos y Listeners**
**Objetivo:** Implementar sistema de eventos para operaciones del sistema

#### **5.1 Crear Eventos del Sistema**
- [ ] Crear `app/Events/ReservationCreated.php`
- [ ] Crear `app/Events/ReservationUpdated.php`
- [ ] Crear `app/Events/ReservationCancelled.php`
- [ ] Crear `app/Events/GoogleEventSynced.php`
- [ ] Definir propiedades y métodos

#### **5.2 Crear Listeners**
- [ ] Crear `app/Listeners/SendReservationConfirmation.php`
- [ ] Crear `app/Listeners/SyncWithGoogleCalendar.php`
- [ ] Crear `app/Listeners/LogReservationActivity.php`
- [ ] Crear `app/Listeners/SendNotificationEmail.php`
- [ ] Implementar lógica de manejo

#### **5.3 Configurar Event Listeners**
- [ ] Registrar eventos en `EventServiceProvider`
- [ ] Configurar listeners
- [ ] Probar eventos y listeners
- [ ] Verificar funcionamiento

---

### **🧪 Fase 6: Testing y Optimización**
**Objetivo:** Asegurar calidad y rendimiento del código

#### **6.1 Crear Tests Unitarios**
- [ ] Tests para todos los servicios
- [ ] Tests para modelos
- [ ] Tests para controladores
- [ ] Tests para eventos y listeners

#### **6.2 Crear Tests de Integración**
- [ ] Tests de flujo completo de reservas
- [ ] Tests de autenticación Google
- [ ] Tests de sincronización con Google Calendar
- [ ] Tests de validaciones

#### **6.3 Optimización**
- [ ] Revisar consultas de base de datos
- [ ] Implementar caché donde sea necesario
- [ ] Optimizar consultas N+1
- [ ] Revisar rendimiento general

---

### **📚 Fase 7: Documentación y Despliegue**
**Objetivo:** Documentar cambios y preparar para producción

#### **7.1 Documentación**
- [ ] Actualizar README.md
- [ ] Documentar API endpoints
- [ ] Crear guía de desarrollo
- [ ] Documentar configuración

#### **7.2 Preparación para Producción**
- [ ] Revisar variables de entorno
- [ ] Configurar logging
- [ ] Configurar monitoreo
- [ ] Crear script de despliegue

---

## 📊 **6. Beneficios del Refactoring**

### **✅ Arquitectura más limpia:**
- Separación clara de responsabilidades
- Código más mantenible
- Fácil de testear

### **✅ Escalabilidad:**
- Fácil agregar nuevas funcionalidades
- Reutilización de código
- Mejor organización

### **✅ Mantenibilidad:**
- Código más legible
- Fácil de debuggear
- Menos acoplamiento

---

## 🎯 **7. Próximos Pasos**

1. **Revisar** este plan
2. **Confirmar** la estructura deseada
3. **Implementar** fase por fase
4. **Probar** cada funcionalidad
5. **Documentar** cambios realizados

---

## 📝 **Notas Importantes**

- **Mantener** funcionalidad existente durante el refactoring
- **Crear** tests para nuevas funcionalidades
- **Documentar** cambios en el código
- **Hacer** commits pequeños y frecuentes
- **Probar** cada cambio antes de continuar

---



*Este documento debe actualizarse conforme se avance en el refactoring*
