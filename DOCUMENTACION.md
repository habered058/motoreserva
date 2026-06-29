# Documentación Técnica — MotoReserva
> Generado el 2026-06-29 mediante inspección directa del código fuente.

---

# 1. Resumen del proyecto

MotoReserva es una aplicación web de gestión de reservas para talleres de servicio de motocicletas. Permite a clientes registrados agendar servicios de mantenimiento (cambio de aceite, revisión general, etc.), asignando automáticamente a un técnico disponible según el horario configurado y las reservas existentes. El sistema cuenta con tres interfaces diferenciadas por rol: administrador, técnico y cliente.

La aplicación está completamente construida sobre Filament 5 (tres paneles separados) con acceso controlado mediante roles y permisos gestionados por Spatie Permission y Filament Shield. No expone API REST ni vistas Blade propias; toda la interfaz vive dentro de los paneles Filament.

---

# 2. Tecnologías y dependencias utilizadas

Fuente: `composer.json` y `package.json`.

### Framework

| Paquete | Versión |
|---|---|
| php | ^8.2 |
| laravel/framework | ^12.0 |
| laravel/tinker | ^2.10.1 |

### Panel administrativo

| Paquete | Versión |
|---|---|
| filament/filament | 5.0 |

### Roles y permisos

| Paquete | Versión |
|---|---|
| spatie/laravel-permission | ^6.25 |
| bezhansalleh/filament-shield | ^4.2 |

### Base de datos

Motor por defecto: **SQLite** (configurable a MySQL/PostgreSQL vía `.env`). La conexión en tests usa `sqlite::memory:`.

### Frontend (Node / Vite)

| Paquete | Versión |
|---|---|
| vite | ^7.0.7 |
| tailwindcss | ^4.0.0 |
| @tailwindcss/vite | ^4.0.0 |
| laravel-vite-plugin | ^2.0.0 |
| axios | ^1.11.0 |
| concurrently | ^9.0.1 |

### Testing

| Paquete | Versión |
|---|---|
| phpunit/phpunit | ^11.5.50 |
| fakerphp/faker | ^1.23 |
| mockery/mockery | ^1.6 |
| nunomaduro/collision | ^8.6 |

### Otras (dev)

| Paquete | Versión |
|---|---|
| laravel/pail | ^1.2.2 |
| laravel/pint | ^1.24 |
| laravel/sail | ^1.41 |

---

# 3. Modelo de datos

### `User` — tabla `users`

Archivo: `app/Models/User.php`. Migración: `0001_01_01_000000_create_users_table.php`.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint (PK) | auto-increment |
| name | varchar | |
| email | varchar | unique |
| email_verified_at | timestamp | nullable |
| password | varchar | hidden en serialización |
| remember_token | varchar | nullable, hidden |
| created_at / updated_at | timestamp | |

**Relaciones declaradas:**
- `tecnico()` → `HasOne(Tecnico::class)` — ver `app/Models/User.php`
- `reservas()` → `HasMany(Reserva::class, 'cliente_id')` — ver `app/Models/User.php`

**Traits:** `HasFactory`, `Notifiable`, `HasRoles` (Spatie). Implementa `FilamentUser`.

---

### `Servicio` — tabla `servicios`

Archivo: `app/Models/Servicio.php`. Migración: `2026_06_29_191619_create_servicios_table.php`.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint (PK) | |
| nombre | varchar | |
| duracion_minutos | unsigned int | |
| precio | unsigned int | en COP |
| created_at / updated_at | timestamp | |

**Relaciones declaradas:**
- `reservas()` → `HasMany(Reserva::class)` — ver `app/Models/Servicio.php`

---

### `Tecnico` — tabla `tecnicos`

Archivo: `app/Models/Tecnico.php`. Migración: `2026_06_29_191621_create_tecnicos_table.php`.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint (PK) | |
| user_id | bigint (FK → users) | cascade delete |
| especialidad | varchar | |
| created_at / updated_at | timestamp | |

**Relaciones declaradas:**
- `user()` → `BelongsTo(User::class)` — ver `app/Models/Tecnico.php`
- `horarios()` → `HasMany(HorarioTecnico::class)` — ver `app/Models/Tecnico.php`
- `reservas()` → `HasMany(Reserva::class)` — ver `app/Models/Tecnico.php`

---

### `HorarioTecnico` — tabla `horario_tecnicos`

Archivo: `app/Models/HorarioTecnico.php`. Migración: `2026_06_29_191622_create_horario_tecnicos_table.php`.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint (PK) | |
| tecnico_id | bigint (FK → tecnicos) | cascade delete |
| dia_semana | tinyint | 0 = domingo, 6 = sábado |
| hora_inicio | time | |
| hora_fin | time | |
| created_at / updated_at | timestamp | |

**Relaciones declaradas:**
- `tecnico()` → `BelongsTo(Tecnico::class)` — ver `app/Models/HorarioTecnico.php`

---

### `Reserva` — tabla `reservas`

Archivo: `app/Models/Reserva.php`. Migración: `2026_06_29_191624_create_reservas_table.php`.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint (PK) | |
| cliente_id | bigint (FK → users) | cascade delete |
| tecnico_id | bigint (FK → tecnicos) | nullable, null on delete |
| servicio_id | bigint (FK → servicios) | cascade delete |
| fecha | date | cast a `date` en el modelo |
| hora | time | |
| marca_moto | varchar | |
| modelo_moto | varchar | |
| placa | varchar | |
| estado | varchar | default `'pendiente'` |
| created_at / updated_at | timestamp | |

**Constantes de estado definidas en el modelo:**
- `ESTADO_PENDIENTE = 'pendiente'`
- `ESTADO_EN_PROCESO = 'en_proceso'`
- `ESTADO_COMPLETADA = 'completada'`
- `ESTADO_CANCELADA = 'cancelada'`

**Relaciones declaradas:**
- `cliente()` → `BelongsTo(User::class, 'cliente_id')` — ver `app/Models/Reserva.php`
- `tecnico()` → `BelongsTo(Tecnico::class)` — ver `app/Models/Reserva.php`
- `servicio()` → `BelongsTo(Servicio::class)` — ver `app/Models/Reserva.php`

**Scopes:**
- `activas()` — filtra registros cuyo estado no es `ESTADO_CANCELADA`
- `delCliente($userId)` — filtra por `cliente_id`

---

# 4. Roles y permisos implementados

### Roles creados

Fuente: `database/seeders/RolesSeeder.php`.

| Rol | Descripción |
|---|---|
| `admin` | Administrador del sistema; se asigna al usuario `admin@motoreserva.test` |
| `tecnico` | Técnico de taller |
| `cliente` | Cliente registrado |

### Permisos y su asignación

Fuente: `database/seeders/ShieldPermissionsSeeder.php`. El rol `admin` recibe todos los permisos generados para los recursos: `servicio`, `tecnico`, `reserva`, `user`.

Acciones por recurso: `view_any`, `view`, `create`, `update`, `delete`, `delete_any`.

Los roles `tecnico` y `cliente` no reciben permisos vía Shield; su acceso está controlado directamente por las Policies.

### Restricciones aplicadas en código

**`app/Policies/ReservaPolicy.php`:**

| Método | admin | tecnico | cliente |
|---|---|---|---|
| `viewAny` | ✅ | ✅ | ✅ |
| `view` | todas | solo asignadas a él | solo propias |
| `create` | ❌ | ❌ | ✅ |
| `update` | todas | solo asignadas, estado `en_proceso`/`completada` | propias en `pendiente` |
| `delete` | ✅ | ❌ | ❌ |
| `restore` | ✅ | ❌ | ❌ |
| `forceDelete` | ✅ | ❌ | ❌ |

**`app/Policies/RolePolicy.php`:** Sigue el patrón de Filament Shield; cada método verifica el permiso correspondiente (`view_any_role`, `create_role`, etc.).

El control de acceso a los paneles Filament se realiza también en `User::canAccessPanel()`, que verifica el rol del usuario para determinar a qué panel puede ingresar.

---

# 5. Funcionalidades implementadas

### Panel Admin (`app/Filament/Resources/`)

| Funcionalidad | Archivo(s) |
|---|---|
| Listar y editar reservas (sin crear desde admin) | `app/Filament/Resources/Reservas/ReservaResource.php` |
| CRUD completo de servicios | `app/Filament/Resources/Servicios/ServicioResource.php` |
| CRUD completo de técnicos, incluyendo gestión de horarios semanales desde una RelationManager | `app/Filament/Resources/Tecnicos/TecnicoResource.php`, `RelationManagers/HorariosRelationManager.php` |
| CRUD de usuarios con asignación de roles | `app/Filament/Resources/Users/UserResource.php` |

### Panel Cliente (`app/Filament/Cliente/Resources/`)

| Funcionalidad | Archivo(s) |
|---|---|
| Ver catálogo de servicios disponibles (solo lectura) | `app/Filament/Cliente/Resources/CatalogoServiciosResource.php` |
| Crear reserva con asignación automática de técnico | `app/Filament/Cliente/Resources/MisReservas/Pages/CreateMiReserva.php` |
| Ver listado de reservas propias | `app/Filament/Cliente/Resources/MisReservasResource.php` |
| Cancelar una reserva propia en estado `pendiente` | `app/Filament/Cliente/Resources/MisReservasResource.php` (acción `cancelar`) |

### Panel Técnico (`app/Filament/Tecnico/Resources/`)

| Funcionalidad | Archivo(s) |
|---|---|
| Ver reservas asignadas al técnico autenticado | `app/Filament/Tecnico/Resources/MisAsignacionesResource.php` |
| Cambiar estado de reserva: `pendiente` → `en_proceso` (acción *Iniciar*) | `app/Filament/Tecnico/Resources/MisAsignacionesResource.php` |
| Cambiar estado de reserva: `en_proceso` → `completada` (acción *Completar*) | `app/Filament/Tecnico/Resources/MisAsignacionesResource.php` |

---

# 6. Lógica de negocio relevante

## Asignación automática de técnicos

Archivo: `app/Services/AsignadorTecnico.php`.

**Punto de entrada:** método estático `encontrarDisponible(int $servicioId, string $fecha, string $hora): ?Tecnico`.

**Parámetros:**
- `$servicioId` — ID del servicio solicitado (se consulta su `duracion_minutos`)
- `$fecha` — fecha de la reserva en formato string (`Y-m-d`)
- `$hora` — hora de inicio deseada en formato string (`H:i`)

**Reglas que aplica:**

1. Calcula el slot de tiempo: `$inicio = Carbon::parse("$fecha $hora")`, `$fin = $inicio + duracion_minutos del servicio`.
2. Determina el día de la semana (`$diaSemana = $inicio->dayOfWeek`, donde 0 = domingo).
3. Consulta todos los `Tecnico` que tengan al menos un `HorarioTecnico` cuyo `dia_semana` coincida con el calculado y cuya ventana (`hora_inicio` ≤ `$inicio` y `hora_fin` ≥ `$fin`) contenga el slot completo.
4. Itera sobre esos técnicos y llama a `tieneConflicto()` para cada uno.
5. Retorna el **primer** técnico sin conflicto, o `null` si todos están ocupados.

**Método auxiliar `tieneConflicto(int $tecnicoId, string $fecha, Carbon $inicio, Carbon $fin): bool`:**
- Busca en `reservas` donde `tecnico_id = $tecnicoId`, `fecha = $fecha`, y `estado != ESTADO_CANCELADA`.
- Para cada reserva encontrada, parsea su `hora` junto con la duración de su servicio para obtener su propio slot, y verifica solapamiento con el slot solicitado (`$inicio < finReserva && $fin > inicioReserva`).
- Retorna `true` en cuanto detecta la primera superposición.

**Cuando no hay técnico disponible:** `encontrarDisponible()` retorna `null`. En `CreateMiReserva.php` esto desencadena un `ValidationException` con el mensaje de error visible al cliente, impidiendo la creación de la reserva.

**Invocación:** se llama en `app/Filament/Cliente/Resources/MisReservas/Pages/CreateMiReserva.php`, en el hook `mutateFormDataBeforeCreate()` (o equivalente), antes de persistir el registro. Si retorna un técnico válido, se asignan automáticamente `tecnico_id` y `cliente_id`; el `estado` inicial es `ESTADO_PENDIENTE`.

---

# 7. Pruebas automatizadas

### Suite Unit — `tests/Unit/`

**`AsignadorTecnicoTest.php`** (4 tests):

| Nombre del test | Qué valida |
|---|---|
| `test_asigna_tecnico_disponible` | Que `encontrarDisponible()` retorna un técnico cuando hay uno libre |
| `test_descarta_tecnico_ocupado_y_asigna_al_libre` | Que se omite un técnico con reserva conflictiva y se asigna el libre |
| `test_devuelve_null_cuando_ninguno_disponible` | Que retorna `null` si todos los técnicos tienen conflicto |
| `test_no_descarta_tecnico_con_reserva_cancelada` | Que una reserva cancelada no cuenta como conflicto |

Usa el trait `RefreshDatabase` (base de datos en memoria). Métodos helper internos: `crearTecnicoConHorario()`, `crearReserva()`, `proximaFechaDeDia()`.

**`ExampleTest.php`** (1 test):
- `test_that_true_is_true` — test de marcador de posición generado por Laravel.

### Suite Feature — `tests/Feature/`

**`ExampleTest.php`** (1 test):
- `test_the_application_returns_a_successful_response` — verifica que `GET /` responde con HTTP 200.

### Resultado de ejecución

> **Nota:** Los tests no fueron ejecutados en este entorno durante la generación de este documento. Para obtener el resultado actual ejecutar:
> ```bash
> php artisan test
> ```
> Configuración de test: `phpunit.xml` con `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `APP_ENV=testing`.

---

# 8. Decisiones técnicas y desviaciones del plan original

1. **`ReservaResource::canCreate()` retorna `false`:** El panel de administración no permite crear reservas directamente. Las reservas solo se crean a través del panel cliente. Esta decisión centraliza la lógica de asignación de técnico.

2. **Tres paneles Filament separados en lugar de uno con control de visibilidad:** Se crearon paneles distintos (`admin`, `cliente`, `tecnico`) en lugar de un único panel con ítems condicionales. Esto aisla completamente la experiencia de cada rol.

3. **`precio` como `unsigned int`:** El precio se almacena como entero (pesos colombianos sin decimales), simplificando el manejo de moneda local.

4. **`AsignadorTecnico` como clase estática:** El servicio de asignación se implementó con métodos estáticos en lugar de inyección de dependencias, optando por simplicidad sobre testabilidad formal mediante mocks.

5. **Sin API REST:** Toda la interfaz vive en Filament; `routes/web.php` solo tiene la ruta raíz que retorna la vista `welcome`.

No se identificaron otras desviaciones documentadas respecto a un plan original previo.

---

# 9. Problemas conocidos o limitaciones actuales

1. **Sin validación de que `hora_fin > hora_inicio` en `HorarioTecnico`:** El formulario permite ingresar horarios incoherentes (ej. `hora_fin` anterior a `hora_inicio`) sin advertencia.

2. **Sin paginación personalizada ni búsqueda en el catálogo de servicios del cliente:** `CatalogoServiciosResource` lista todos los servicios sin filtros.

3. **`ExampleTest` genérico sin eliminar:** Ambas suites tienen un `ExampleTest` de marcador de posición que no aporta cobertura real.

4. **`tecnico_id` nullable en reservas:** Si un técnico es eliminado, sus reservas quedan con `tecnico_id = null`, lo que puede provocar errores de visualización en el panel técnico si no se maneja el caso `null`.

5. **Sin notificaciones por email:** No hay configuración de `MAIL_MAILER` real ni listeners que notifiquen al cliente o técnico sobre cambios de estado.

6. **`AsignadorTecnico` no contempla zona horaria:** Las comparaciones de hora usan `Carbon::parse()` sin zona horaria explícita; en entornos con `APP_TIMEZONE` diferente de UTC podría asignar incorrectamente.

7. **Rol del usuario no verificado al asignar `Tecnico` en `TecnicoResource`:** El formulario permite asociar cualquier `User` como técnico, sin verificar que ese usuario tenga el rol `tecnico`.

---

# 10. Instrucciones de instalación y ejecución

### Requisitos previos
- PHP >= 8.2
- Composer
- Node.js >= 18 y npm
- SQLite (incluido en PHP) o MySQL/PostgreSQL si se prefiere

### Pasos

```bash
# 1. Clonar o descomprimir el proyecto
cd motoreserva

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias Node
npm install

# 4. Configurar el entorno
cp .env.example .env
php artisan key:generate
```

Editar `.env` si se desea usar MySQL en lugar de SQLite:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=motoreserva
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# 5. Ejecutar migraciones y seeders
php artisan migrate --seed

# 6. Compilar assets (solo necesario la primera vez o en producción)
npm run build

# 7. Levantar el servidor de desarrollo
php artisan serve
```

La aplicación estará disponible en `http://localhost:8000`.

### Usuarios de prueba (creados por los seeders)

| Email | Contraseña | Rol | Panel de acceso |
|---|---|---|---|
| admin@motoreserva.test | password | admin | `/admin` |
| tecnico@motoreserva.test | password | tecnico | `/tecnico` |
| cliente@motoreserva.test | password | cliente | `/cliente` |

### Ejecutar tests

```bash
php artisan test
```

Los tests usan SQLite en memoria (`DB_DATABASE=:memory:`) definido en `phpunit.xml`; no requieren base de datos separada.
