# Google Calendar Integration - Laravel

## Descripción

Integración completa de Google Calendar con Laravel para sincronización bidireccional de eventos, manejo de tokens OAuth, y API completa de calendarios.

## Características

- ✅ **OAuth 2.0** con refresh tokens automáticos
- ✅ **Service Account** con impersonación de usuario
- ✅ **Sincronización bidireccional** de eventos
- ✅ **Manejo de tokens por usuario** (archivos separados)
- ✅ **Scopes configurables** para permisos granulares
- ✅ **Comando de diagnóstico** completo
- ✅ **Logging detallado** para auditoría

## Configuración

### 1. Variables de Entorno (.env)

```env
# Google OAuth
GOOGLE_CLIENT_ID=tu_client_id
GOOGLE_CLIENT_SECRET=tu_client_secret
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback

# Google Calendar
GOOGLE_CREDENTIALS_PATH=storage/app/google/credentials.json
GOOGLE_CALENDAR_REDIRECT_URI=http://127.0.0.1:8000/google/callback
GOOGLE_IMPERSONATE_SUBJECT=usuario@dominio.com  # Solo para Service Account
```

### 2. Archivo de Credenciales

Coloca tu archivo `credentials.json` de Google Cloud Console en:
```
storage/app/google/credentials.json
```

### 3. Configuración de Google Cloud Console

1. **Habilitar Google Calendar API**
2. **Configurar OAuth consent screen** con scopes:
   - `https://www.googleapis.com/auth/calendar`
   - `https://www.googleapis.com/auth/calendar.events`
   - `https://www.googleapis.com/auth/calendar.readonly`

## Uso

### Autenticación OAuth

#### Iniciar autorización:
```
GET /google/auth?email=usuario@dominio.com
```

#### Callback (automático):
```
GET /google/callback
```

#### Revocar acceso:
```
GET /google/revoke?email=usuario@dominio.com
```

### API de Calendario

#### Listar calendarios:
```php
$calendars = $googleService->listCalendars('usuario@dominio.com');
```

#### Obtener calendario primario:
```php
$primary = $googleService->getPrimaryCalendar('usuario@dominio.com');
```

#### Listar eventos:
```php
$events = $googleService->listPrimaryEvents('usuario@dominio.com', [
    'maxResults' => 50,
    'timeMin' => Carbon::now()->subDays(7)->toRfc3339String(),
    'timeMax' => Carbon::now()->addDays(30)->toRfc3339String(),
]);
```

#### Verificar permisos:
```php
$permissions = $googleService->verifyPermissions('usuario@dominio.com');
```

## Comando de Diagnóstico

### Uso básico:
```bash
php artisan calendar:test --user="usuario@dominio.com"
```

### Funcionalidades del comando:
1. ✅ Verifica archivo de credenciales
2. ✅ Valida permisos del usuario
3. ✅ Realiza 3 llamadas reales a la API:
   - `calendarList.list` - Lista calendarios
   - `calendars.get('primary')` - Obtiene calendario primario
   - `events.list('primary')` - Lista eventos

### Ejemplo de salida exitosa:
```
🔍 Probando conexión con Google Calendar...
✅ Archivo de credenciales encontrado
👤 Probando con usuario: usuario@dominio.com
🔐 Verificando permisos...
✅ Permisos OK (scope verificado)

🚀 Realizando llamadas de prueba a la API...
✅ calendarList.list: 3 calendarios
   • Mi Calendario (PRIMARIO) - owner
   • Trabajo - writer
   • Personal - reader
✅ calendars.get('primary'): Mi Calendario
   • Zona horaria: America/Bogota
   • Rol de acceso: owner
✅ events.list('primary'): 15 eventos encontrados
   • Primer evento: Reunión de equipo
   • Inicio: 2025-08-15 14:00

🎉 ¡Prueba completada exitosamente!
```

## Estructura de Archivos

```
storage/app/google/
├── credentials.json          # Credenciales de Service Account
└── tokens/                   # Tokens por usuario
    ├── usuario_at_dominio_dot_com.json
    └── otro_usuario_at_dominio_dot_com.json
```

## Manejo de Errores

### ACCESS_TOKEN_SCOPE_INSUFFICIENT
**Causa**: Token no tiene los scopes necesarios
**Solución**: Reautorizar en `/google/auth?email=usuario@dominio.com`

### PERMISSION_DENIED
**Causa**: No hay permisos para acceder al calendario
**Solución**: Verificar configuración de Google Workspace o compartir calendario

### invalid_grant
**Causa**: Token expirado o revocado
**Solución**: Eliminar token y reautorizar

## Troubleshooting

### 1. Token no se guarda
- Verificar permisos de escritura en `storage/app/google/tokens/`
- Revisar logs en `storage/logs/laravel.log`

### 2. Scopes no se aplican
- Verificar configuración en `config/google.php`
- Revisar OAuth consent screen en Google Cloud Console
- Forzar re-consentimiento con `prompt=consent`

### 3. Service Account no funciona
- Verificar Domain-Wide Delegation habilitado
- Configurar `GOOGLE_IMPERSONATE_SUBJECT` correctamente
- Compartir calendario con la cuenta de servicio

## Logs y Auditoría

Todos los errores se registran en `storage/logs/laravel.log` con:
- `domain`: Dominio del error (googleapis.com, global)
- `reason`: Razón específica del error
- `method`: Método de la API que falló
- `service`: Servicio de Google afectado

## Seguridad

- ✅ Tokens almacenados por usuario (archivos separados)
- ✅ Refresh tokens automáticos
- ✅ Scopes granulares configurados
- ✅ Logging de auditoría completo
- ✅ Validación de dominios permitidos

## Soporte

Para problemas específicos:
1. Ejecutar `php artisan calendar:test --user="email"`
2. Revisar logs en `storage/logs/laravel.log`
3. Verificar configuración en Google Cloud Console
4. Confirmar scopes en OAuth consent screen
