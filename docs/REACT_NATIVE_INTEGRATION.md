# Integración de la app React Native con ERP RH

> Documento de continuidad para desarrollar la aplicación móvil sin perder el contexto del backend. Actualizar este archivo cuando cambien rutas, payloads o reglas de negocio.

## 1. Proyectos y responsabilidad

- Backend/API: `C:\Users\Alberto Tapia\Documents\PROYECTOS\erp-rh-main`
- Tecnología: Laravel 12, PHP 8.2+, MySQL, Laravel Sanctum.
- Base de datos local: `recursos_humanos`.
- App móvil: React Native (repositorio separado).
- Prefijo vigente de API: `/api/v1`.
- Zona horaria de negocio: `America/Mexico_City` (Ciudad de México).
- El backend es la fuente de verdad para permisos, secuencias de asistencia, horarios, geocercas, retardos y horas extra. La app no debe duplicar esas reglas.

No copiar credenciales de `.env`, contraseñas, secretos QR ni claves de base de datos al proyecto móvil.

## 2. Conexión desde React Native

La URL configurada actualmente en Laravel es `http://localhost:8000`, pero `localhost` desde un teléfono o emulador apunta al propio dispositivo.

Ejemplos de URL base durante desarrollo:

| Entorno | URL orientativa |
|---|---|
| Android Emulator | `http://10.0.2.2:8000/api/v1` |
| iOS Simulator | `http://127.0.0.1:8000/api/v1` |
| Teléfono físico en la misma Wi-Fi | `http://IP_LAN_DE_LA_PC:8000/api/v1` |
| Producción | `https://dominio-seguro/api/v1` |

Para un teléfono físico, iniciar Laravel escuchando en la red local y permitir el puerto en el firewall:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

En producción es obligatorio HTTPS. No deshabilitar validaciones TLS en la app.

Variable sugerida en React Native:

```env
EXPO_PUBLIC_API_URL=http://10.0.2.2:8000/api/v1
```

## 3. Autenticación

Se usa un Bearer token de Laravel Sanctum. Guardarlo en almacenamiento seguro del dispositivo (Expo SecureStore, iOS Keychain o Android Keystore), no en AsyncStorage sin cifrado.

### Iniciar sesión

`POST /auth/login`

```json
{
  "email": "usuario@empresa.com",
  "password": "contraseña",
  "device_name": "android-a1b2c3"
}
```

Respuesta `200`:

```json
{
  "token": "TOKEN_SANCTUM",
  "user": {
    "id": 10,
    "name": "Nombre",
    "email": "usuario@empresa.com",
    "role": "employee",
    "employee": {
      "id": 20,
      "employee_number": "EMP-020",
      "work_mode": "office",
      "work_location_id": 1,
      "work_location": {}
    }
  }
}
```

El backend elimina previamente otro token con el mismo `device_name`. Usar un identificador estable por instalación.

### Peticiones autenticadas

```http
Authorization: Bearer TOKEN_SANCTUM
Accept: application/json
Content-Type: application/json
```

- `GET /auth/me`: refresca usuario, empleado, modalidad y sede.
- `POST /auth/logout`: revoca el token actual; respuesta `204`.

Si una petición devuelve `401`, borrar el token local y volver al login.

## 4. Modalidades de asistencia

`employee.work_mode` admite:

- `office`: el colaborador escanea el QR rotativo mostrado en una tablet/PC de su sede.
- `hospital`: la app envía geolocalización y el backend exige estar dentro de la geocerca asignada.
- `mixed`: la app permite elegir entre escanear el QR de cualquier sede activa o enviar geolocalización dentro de la geocerca de cualquier sede activa.
- `home_office`: la app envía geolocalización para auditoría, sin restricción de rango.

La app debe consultar `/attendance/status` antes de mostrar el botón de marcaje.

## 5. Secuencia de asistencia

Secuencia vigente y estricta:

1. `clock_in` — inicio de jornada.
2. `breakfast_start` — salida a desayuno.
3. `breakfast_end` — regreso de desayuno.
4. `lunch_start` — salida a comida.
5. `lunch_end` — regreso de comida.
6. `clock_out` — fin de jornada.

El backend devuelve `next_event` y `flow`; la app debe presentar únicamente `next_event`. El flujo puede omitir desayuno, comida o ambos según el horario general asignado al empleado, por lo que nunca debe inferirse localmente.

Los horarios de desayuno y comida son libres. Sólo se mide duración. Los límites predeterminados son 30 y 60 minutos, se configuran por horario general y día, y cada horario indica si aplica desayuno y/o comida.

## 6. Endpoints de asistencia disponibles

Todos requieren `auth:sanctum`.

### Estado actual

`GET /attendance/status`

Respuesta aproximada:

```json
{
  "current_status": "breakfast_start",
  "next_event": "breakfast_end",
  "flow": ["clock_in", "breakfast_start", "breakfast_end", "lunch_start", "lunch_end", "clock_out"],
  "last_entry": {},
  "records": []
}
```

Si todavía no hay marcas: `current_status = "not_started"` y `next_event = "clock_in"`. Al terminar: `next_event = null`.

### Registrar marca

`POST /attendance/clock`

Tipos válidos: `clock_in`, `breakfast_start`, `breakfast_end`, `lunch_start`, `lunch_end`, `clock_out`.

Oficina fija:

```json
{
  "event_type": "clock_in",
  "station_token": "CONTENIDO_LEIDO_DEL_QR",
  "notes": null
}
```

Hospital, home office o modalidad mixta por geolocalización debe enviarse como `multipart/form-data`. La selfie es obligatoria en cada marcación:

```text
event_type: clock_in
latitude: 19.432608
longitude: -99.133209
photo: archivo JPG/PNG/WEBP capturado con la cámara (máximo 5 MB)
notes: opcional
```

Para modalidad mixta por QR se usa el mismo cuerpo de oficina fija. El backend acepta el QR vigente de cualquier sede activa y no requiere selfie ni coordenadas.

Respuesta `201`:

```json
{
  "record": {},
  "status": {
    "current_status": "clock_in",
    "next_event": "breakfast_start",
    "last_entry": {},
    "records": []
  }
}
```

Reglas importantes:

- QR de oficina: firmado, ligado a sede y rotado cada minuto.
- La app sólo escanea el QR; no solicita `/attendance/stations/{id}/token`.
- Hospital fuera de geocerca: respuesta `422`.
- Hospital o home office sin coordenadas o sin selfie: respuesta `422`.
- La app debe abrir la cámara frontal y agregar el archivo como campo `photo`; no debe enviar la imagen en Base64 ni como JSON.
- Secuencia incorrecta: respuesta `422` indicando la marca esperada.
- Al volver de desayuno/comida se calculan duración, límite y exceso.
- En `clock_in` se calculan puntualidad y retardo.
- En `clock_out` se calculan minutos extra.

Campos relevantes en un registro:

```text
event_type, registered_at, work_mode, work_location_id,
latitude, longitude, distance_meters, in_geofence,
verification_method, punctuality, late_minutes,
break_duration_minutes, break_limit_minutes,
break_excess_minutes, break_compliance,
overtime_minutes, overtime_reason, photo_path, notes
```

### Historial

`GET /attendance/history?from=2026-09-01&to=2026-09-30&per_page=20`

Devuelve paginación estándar de Laravel: `data`, `current_page`, `last_page`, `per_page`, `total`, `links`, etc. Máximo 100 registros por página.

## 7. QR de la estación

Flujo elegido:

1. Una tablet o PC autenticada abre `/work-locations/{id}/station` en el panel web.
2. La pantalla genera y renueva el QR.
3. La app del empleado escanea su contenido.
4. La app envía el contenido sin alterarlo como `station_token` a `/attendance/clock`.
5. El backend valida firma, vigencia, sede asignada, secuencia y reutilización.

No decodificar ni fabricar el token en React Native. Para escaneo se requiere permiso de cámara.

## 8. Geolocalización móvil

- Solicitar permiso sólo al marcar, explicando el propósito.
- Capturar coordenadas inmediatamente antes del POST.
- Enviar latitud/longitud numéricas, no cadenas formateadas.
- Mostrar estados claros: permiso rechazado, GPS deshabilitado, timeout y baja precisión.
- No decidir en la app si está dentro de la geocerca; el backend calcula Haversine y decide.
- Evitar almacenar un historial adicional de ubicación en el dispositivo.

## 9. Solicitudes de vacaciones y permisos administrativos

Estos endpoints requieren `auth:sanctum` y operan siempre sobre el empleado vinculado al token.

### Opciones y saldo

`GET /leave-requests/options`

Devuelve `vacation_days_available`, `minimum_vacation_start_date`, superior inmediato, tipos, vías de aprobación y `administrative_permissions` con `monthly_limit`, `used`, `remaining`, `duration_minutes` y `period`. Consultarlo antes de mostrar el formulario.

### Crear solicitud

`POST /leave-requests`

```json
{
  "request_type": "vacation",
  "start_date": "2026-10-05",
  "end_date": "2026-10-09",
  "approval_route": "supervisor",
  "observations": "Solicitud de vacaciones"
}
```

Valores válidos:

- `request_type`: `vacation` o `administrative_permission`.
- `approval_route`: `hr` o `supervisor`.

Para un permiso administrativo se envía `multipart/form-data` porque puede incluir evidencia:

```text
request_type: administrative_permission
permission_date: 2026-10-08
permission_start_time: 10:00
approval_route: hr
observations: Trámite administrativo
evidence: PDF/JPG/PNG/WEBP opcional, máximo 10 MB
```

Reglas controladas exclusivamente por el backend:

- La solicitud nace con estado `pending`.
- Las fechas inicial y final deben ser de lunes a viernes.
- Los días solicitados se calculan de lunes a viernes, incluyendo ambas fechas.
- Las vacaciones requieren al menos cinco días hábiles de anticipación.
- No se permiten más días de vacaciones que el saldo disponible.
- No se permiten periodos traslapados con solicitudes pendientes o aprobadas.
- Para elegir `supervisor`, el empleado debe tener superior inmediato y éste debe tener un usuario autorizado.
- El saldo se descuenta al aprobar, no al solicitar. Un rechazo no consume días.
- Cada permiso administrativo dura exactamente 2 horas.
- Cada empleado dispone de 3 permisos administrativos por mes; no son acumulables.
- Debe solicitarse con al menos 24 horas exactas de anticipación respecto de la hora de inicio.
- No se permiten dos permisos administrativos para el mismo día, aunque sus horarios sean diferentes.
- Para evitar exceder el límite, los permisos pendientes y aprobados consumen temporalmente el contador del mes. Los rechazados no cuentan.
- El contador se calcula por mes calendario y se reinicia automáticamente al comenzar uno nuevo.
- La evidencia de un permiso es opcional y se almacena de forma privada.
- Al aprobar se genera automáticamente una incidencia de asistencia.

La respuesta `201` incluye `requested_business_days`, `return_date`, `status`, aprobador y datos del empleado.

### Historial propio

`GET /leave-requests?per_page=20`

Devuelve las solicitudes del colaborador autenticado con estado `pending`, `approved` o `rejected`.

En el panel web, RR. HH. resuelve solicitudes dirigidas a `hr`; el superior asignado resuelve las dirigidas a `supervisor`. El superusuario puede revisar ambas.

### Notificaciones push al resolver solicitudes

El backend ya utiliza Expo Push Service para avisar cuando una solicitud cambia a `approved` o `rejected`. Cada usuario puede registrar varios dispositivos.

`POST /push-tokens` (requiere Bearer token):

```json
{
  "token": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]",
  "platform": "android",
  "device_uid": "identificador-estable-de-la-instalacion"
}
```

La operación es idempotente y responde `{ "registered": true }`. Acepta `platform` con `android` o `ios`.

`DELETE /push-tokens` acepta `token` o `device_uid` y responde `204`. Debe ejecutarse antes de `/auth/logout` para retirar únicamente la instalación actual.

La app debe registrar el Expo push token después del login, crear el canal Android `leave-requests` y, al tocar una notificación, navegar a `/leave-requests` y refrescar `GET /leave-requests`. El payload contiene `type`, `request_id`, `request_type`, `status` y `url`.

Los tokens push son distintos de los tokens Sanctum y no deben mostrarse completos en logs. El envío se procesa en cola después de resolver la solicitud. Expo devuelve tickets y el backend consulta receipts; `DeviceNotRegistered` deshabilita automáticamente el token.

En el servidor debe mantenerse activo un trabajador de cola:

```powershell
cd "C:\Users\Alberto Tapia\Documents\PROYECTOS\erp-rh-main"
php artisan queue:work --tries=5
```

Este comando se ejecuta en una terminal del proyecto Laravel, no dentro del proyecto React Native `RRHH`. La terminal debe permanecer abierta durante el desarrollo.

Si el worker muestra `cURL error 60`, PHP no tiene configurado un paquete de autoridades certificadoras. En esta instalación WAMP, la consola usa `C:\wamp64\bin\php\php8.3.14\php.ini` y debe tener configurados `curl.cainfo` y `openssl.cafile`. Después de modificar `php.ini` siempre hay que detener el worker con `Ctrl+C` y volver a iniciarlo.

`EXPO_ACCESS_TOKEN` es opcional y sólo se configura en el `.env` de Laravel si el proyecto Expo activa seguridad mejorada. Las notificaciones remotas en Android requieren un development build o compilación instalada; no funcionan en Expo Go.

## 10. Incidencias administrativas

Endpoints existentes:

- `GET /attendance/incidents`
- `POST /attendance/incidents`

Tipos: `vacation`, `medical_leave`, `flexibility_permission`, `administrative_permission`, `personal_permission`, `other`.

Estos endpoints son administrativos y sólo aceptan `super_admin`, `admin`, `hr`, `supervisor`, `analyst` y `assistant`. Un usuario `employee` recibe `403`. Actualmente una incidencia se crea aprobada; por eso **no debe usarse todavía como solicitud del empleado**.

Payload administrativo:

```json
{
  "employee_id": 20,
  "type": "vacation",
  "starts_at": "2026-10-01 00:00:00",
  "ends_at": "2026-10-05 23:59:59",
  "notes": "Vacaciones autorizadas"
}
```

También admite `multipart/form-data` con `document` PDF/JPG/PNG de hasta 5 MB.

## 11. Módulos móviles pendientes

No existen todavía endpoints para estos módulos. Deben diseñarse e implementarse antes de consumirlos:

### Vacantes

Propuesta:

- `GET /vacancies`
- `GET /vacancies/{vacancy}`
- `POST /vacancies/{vacancy}/apply`
- `GET /my-applications`

Definir si sólo aplican empleados o también candidatos externos.

### Recibos de nómina

Propuesta:

- `GET /payroll-receipts?year=2026&month=9`
- `GET /payroll-receipts/{receipt}`
- `GET /payroll-receipts/{receipt}/download`

Los PDFs/XML deben servirse por autorización; nunca publicar rutas directas de almacenamiento.

### Datos del colaborador

Además de `/auth/me`, probablemente harán falta:

- `GET /profile`
- `PATCH /profile`
- `GET /schedule`
- `GET /work-location`
- `GET /notifications`

## 12. Tablas relevantes del backend

| Tabla | Propósito |
|---|---|
| `users` | Cuenta, rol y vínculo `employee_id`. |
| `personal_access_tokens` | Tokens Sanctum por dispositivo. |
| `employees` | Datos del colaborador, modalidad, sede, superior inmediato y saldo vacacional. |
| `positions` | Puesto, nivel y PDF de perfil. |
| `departaments` | Departamentos. El nombre actual conserva el typo histórico. |
| `work_locations` | Sedes, coordenadas, radio y secreto QR. |
| `work_schedules` | Catálogo general de horarios, descansos aplicables y empleados asignados. |
| `work_schedule_days` | Días laborables, entrada, salida, tolerancia y límites de pausas de cada horario. |
| `attendance_records` | Todas las marcas y cálculos de asistencia. |
| `attendance_incidents` | Incidencias administrativas aprobadas. |
| `leave_requests` | Solicitudes de vacaciones/permisos, periodo, ruta de aprobación y resolución. |
| `push_tokens` | Tokens Expo por usuario e instalación, con estado activo o deshabilitado. |
| `push_notification_tickets` | Tickets y receipts de entrega devueltos por Expo. |

La app nunca se conecta directamente a MySQL. Toda lectura/escritura pasa por la API.

### Fechas y zona horaria

Laravel y los cálculos de asistencia usan `America/Mexico_City`. Los valores ISO enviados por la API pueden incluir `Z`/offset y React Native puede convertirlos para presentación local, pero la app no debe restar manualmente seis horas ni aplicar una segunda conversión. Para enviar fechas de solicitudes usar ISO 8601 con offset cuando sea una fecha-hora; para fechas civiles sin hora usar `YYYY-MM-DD`.

## 13. Errores que la app debe manejar

| HTTP | Significado esperado |
|---|---|
| `200/201/204` | Operación correcta. |
| `401` | Token ausente, inválido o revocado. |
| `403` | El rol no tiene permiso. |
| `404` | Recurso inexistente. |
| `422` | Validación, secuencia, QR, geocerca o usuario sin empleado. |
| `429` | Demasiadas solicitudes, si se activa rate limiting. |
| `500` | Error del servidor; registrar identificador técnico, no mostrar stack trace. |

Laravel devuelve normalmente errores `422` así:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "event_type": ["La marca esperada es breakfast_end."]
  }
}
```

La app debe mostrar el primer mensaje útil y conservar la posibilidad de reintentar. No reintentar automáticamente un POST de marcaje después de un timeout sin consultar primero `/attendance/status`, para evitar duplicados.

## 14. Estructura sugerida en React Native

```text
src/
  api/
    client.ts
    auth.ts
    attendance.ts
  features/
    auth/
    attendance/
    requests/
    vacancies/
    payroll/
  hooks/
  navigation/
  screens/
  storage/
    secureToken.ts
  types/
    api.ts
```

El cliente HTTP debe:

1. usar una URL base por ambiente;
2. agregar `Accept: application/json`;
3. agregar Bearer token automáticamente;
4. centralizar `401` y `422`;
5. definir timeout razonable;
6. no registrar tokens, contraseñas ni documentos en consola.

## 15. Orden recomendado de implementación móvil

1. Configuración de ambientes y cliente HTTP.
2. Login, almacenamiento seguro, `/auth/me` y logout.
3. Pantalla de estado de asistencia.
4. Cámara/QR para oficina.
5. Geolocalización para hospital/home office.
6. Historial paginado.
7. Manejo offline/timeout con reconciliación mediante `/attendance/status`.
8. Pantallas de saldo, creación e historial de solicitudes usando `/leave-requests`.
9. Vacantes.
10. Recibos de nómina y descarga segura.

## 16. Contexto para futuras sesiones de IA

Al trabajar desde el repositorio React Native, proporcionar esta información:

```text
El backend es Laravel 12 y está en:
C:\Users\Alberto Tapia\Documents\PROYECTOS\erp-rh-main

La guía de integración es:
docs/REACT_NATIVE_INTEGRATION.md

La API usa Sanctum Bearer tokens y el prefijo /api/v1.
No inventes endpoints: verifica primero routes/api.php y php artisan route:list --path=api.
No conectes la app directamente a MySQL.
El backend es la fuente de verdad para secuencia, QR, geocerca, retardos, pausas y horas extra.
Actualiza esta guía si cambia algún contrato.
```

## 17. Archivos fuente del contrato actual

- `routes/api.php`
- `app/Http/Controllers/Api/V1/AuthController.php`
- `app/Http/Controllers/Api/V1/AttendanceController.php`
- `app/Http/Controllers/Api/V1/AttendanceIncidentController.php`
- `app/Http/Controllers/Api/V1/LeaveRequestController.php`
- `app/Http/Controllers/Api/V1/PushTokenController.php`
- `app/Jobs/SendLeaveRequestDecisionPush.php`
- `app/Jobs/CheckExpoPushReceipts.php`
- `app/Http/Requests/Api/LoginRequest.php`
- `app/Http/Requests/Api/ClockRequest.php`
- `app/Services/AttendanceService.php`
- `app/Services/GeofencingService.php`
- `app/Services/StationQrService.php`
- `app/Services/LeaveRequestService.php`

Última revisión: 2026-09-20.
