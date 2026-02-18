# SJ Armory

Sistema web de gestion de armamento, asignaciones operativas, transferencias, documentos, trazabilidad y auditoria.

## 1. Alcance funcional del sistema

El proyecto cubre de extremo a extremo:

- Gestion de armas.
- Gestion de clientes.
- Gestion de puestos e instalaciones del cliente.
- Gestion de trabajadores (escoltas y supervisores).
- Asignacion operativa de armas a cliente responsable.
- Asignacion interna de arma a puesto o trabajador.
- Transferencia de armas entre responsables.
- Carga y administracion de fotos y documentos por arma.
- Generacion automatica de documento de renovacion en `.docx`.
- Mapa operativo con ubicacion de armas.
- Reporteria y auditoria.
- Alertas documentales por vencimiento.
- Gestion de cartera de clientes por responsable.
- Control de acceso por rol y nivel.

## 2. Stack tecnologico

Backend:

- PHP `^8.1`
- Laravel `^10.10`
- Laravel Sanctum
- Eloquent ORM
- PHPUnit 10
- Laravel Breeze (auth scaffold)
- PhpOffice/PhpWord (generacion de documento de renovacion)

Frontend:

- Vite 5
- Tailwind CSS 3 + `@tailwindcss/forms`
- Alpine.js
- Axios
- Leaflet + `leaflet.markercluster`

Servicios externos:

- Nominatim OpenStreetMap (geocoding y reverse geocoding)
- ArcGIS/Esri + OSM tiles para mapas

## 3. Arquitectura general

- Patron MVC de Laravel.
- Logica de negocio distribuida en:
  - Controladores (flujo de use cases).
  - Servicios (`WeaponAssignmentService`, `WeaponDocumentService`, `GeocodingService`).
  - Politicas (`WeaponPolicy`, `ClientPolicy`, `PostPolicy`, `WorkerPolicy`).
- Auditoria centralizada:
  - Trait `Auditable` para eventos CRUD de modelos auditables.
  - Eventos manuales para acciones de negocio (asignaciones, transferencias, fotos, documentos, login, etc.).
- Persistencia principal en MySQL.
- Archivos binarios en discos Laravel:
  - `public` para fotos.
  - `local` para documentos/permiso/renovacion.

## 4. Roles, permisos y niveles

### Roles (`users.role`)

- `ADMIN`
  - Acceso total.
  - Puede crear/editar/eliminar armas, clientes, puestos, trabajadores, usuarios.
  - Gestiona carteras.
  - Gestiona reportes y alertas.
  - Puede asignar y transferir.

- `RESPONSABLE`
  - Ve solo armas/clientes de su cartera.
  - Puede operar segun su nivel.
  - Puede recibir/aceptar transferencias dirigidas a su usuario.

- `AUDITOR`
  - Acceso de consulta.
  - Puede ver reportes y alertas.
  - No administra entidades operativas.

### Niveles de responsabilidad (`responsibility_levels.level`)

Definidos hoy por seed:

- Nivel 1: `Asignado con gestion` (operativo con gestion).
- Nivel 2: `Asignado solo lectura`.

### Reglas de policy relevantes

- `WeaponPolicy`
  - `viewAny`: ADMIN, RESPONSABLE, AUDITOR.
  - `view`: ADMIN; RESPONSABLE solo si es responsable activo del arma; AUDITOR si.
  - `assignToClient`: ADMIN o RESPONSABLE nivel 1 del arma.
  - `create/update/delete`: solo ADMIN.

- `ClientPolicy`
  - `viewAny`: ADMIN, RESPONSABLE, AUDITOR.
  - `view`: ADMIN/AUDITOR o RESPONSABLE si cliente en su cartera.
  - `create/update/delete`: solo ADMIN.

- `PostPolicy` y `WorkerPolicy`
  - Consulta para ADMIN/RESPONSABLE/AUDITOR.
  - Alta/edicion/baja solo ADMIN.

## 5. Modulos y comportamientos de negocio

### 5.1 Armas

Archivo principal: `app/Http/Controllers/WeaponController.php`

- CRUD de armas.
- Codigo interno autogenerado (`SJ-0001`, `SJ-0002`, ...).
- Carga inicial y actualizacion de:
  - Foto de permiso (obligatoria al crear arma).
  - Fotos tecnicas del arma por tipo de descripcion.
- Sincronizacion automatica de documentos:
  - Documento de permiso (`is_permit = true`).
  - Documento de renovacion (`is_renewal = true`).
- Toggle de impronta mensual (solo ADMIN).
- Listado con filtro libre y paginacion.
- Soporte de render parcial AJAX para tabla/paginacion (`expectsJson`).

Tipos de arma permitidos en validacion actual:

- `Escopeta`
- `Pistola`
- `Revolver`
- `Uzi`

Tipos de propiedad:

- `company_owned`
- `leased`
- `third_party`

### 5.2 Asignacion a cliente (destino operativo)

Controlador: `app/Http/Controllers/WeaponClientAssignmentController.php`  
Servicio: `app/Services/WeaponAssignmentService.php`

Reglas:

- Solo una asignacion activa por arma.
- Si existe activa, se cierra (`end_at`, `is_active = null`) y se crea nueva.
- Si cambia cliente, limpia asignaciones internas activas (puesto/trabajador).
- Para RESPONSABLE no admin:
  - Solo clientes de su cartera.
- Para ADMIN:
  - Se selecciona responsable del cliente destino.

### 5.3 Asignacion interna (puesto o trabajador)

Controlador: `app/Http/Controllers/WeaponInternalAssignmentController.php`

Reglas:

- Requiere destino operativo activo (cliente asignado).
- Solo se puede asignar uno de dos:
  - `post_id` o `worker_id`.
- Solo una asignacion interna activa por arma:
  - puesto activo o trabajador activo.
- Si ya existe activa y no se marca reemplazo, muestra advertencia.
- Para RESPONSABLE:
  - Debe ser nivel 1 y responsable activo del arma.
  - Debe pertenecer a su cartera.
  - Si es trabajador, debe estar a su cargo.
- Permite retiro manual de asignacion interna activa.

### 5.4 Transferencias

Controlador: `app/Http/Controllers/WeaponTransferController.php`

Estados:

- `pending`
- `accepted`
- `rejected`
- `cancelled` (constante disponible; flujo actual usa pending/accepted/rejected)

Flujo:

- Solicitud masiva (`bulkStore`) de una o varias armas.
- Al solicitar:
  - Cierra asignaciones internas activas.
  - Retira asignacion de cliente activa.
  - Crea registro de transferencia pendiente.
- Aceptacion:
  - Valida cartera del destinatario.
  - Asigna nuevo cliente responsable.
  - Opcionalmente asigna puesto o trabajador.
- Rechazo:
  - Marca estado rechazado.

### 5.5 Clientes

Controlador: `app/Http/Controllers/ClientController.php`

- CRUD.
- RESPONSABLE ve solo clientes de su cartera.
- Geocodificacion automatica por direccion/ciudad/departamento.
- Opcion de coordenadas manuales desde mapa (`coords_source = map`).
- No permite borrar cliente con armas activas asignadas.

### 5.6 Puestos

Controlador: `app/Http/Controllers/PostController.php`

- CRUD.
- `index` para ADMIN/RESPONSABLE/AUDITOR.
- Crear/editar/borrar solo ADMIN.
- Geocodificacion equivalente a clientes.
- Filtros por cliente y texto.

### 5.7 Trabajadores

Controlador: `app/Http/Controllers/WorkerController.php`

- CRUD.
- `index` para ADMIN/RESPONSABLE/AUDITOR.
- Crear/editar/borrar solo ADMIN.
- Roles de trabajador:
  - `ESCOLTA`
  - `SUPERVISOR`
- Filtros por cliente, rol, responsable y texto.

### 5.8 Documentos de arma

Controlador: `app/Http/Controllers/WeaponDocumentController.php`

- Carga de archivo al disco `local`.
- Registro en `files` y `weapon_documents`.
- Descarga por ruta protegida.
- Si es documento de renovacion, se regenera al descargar.
- Eliminacion de documento + archivo fisico.

Observaciones permitidas en carga documental:

- `En Armerillo`
- `En Mantenimiento`
- `Para Mantenimiento`
- `Hurtada`
- `Perdida`
- `Dar de Baja`

Estados permitidos:

- `Sin novedad`
- `En proceso`

### 5.9 Fotos de arma

Controlador: `app/Http/Controllers/WeaponPhotoController.php`

- Carga y reemplazo por tipo de foto.
- Al actualizar o borrar, elimina archivo anterior.
- Sincroniza renovacion despues de cambios.

Descripciones tecnicas soportadas (`WeaponPhoto::DESCRIPTIONS`):

- `lado_derecho`
- `lado_izquierdo`
- `canon_disparador_marca`
- `serie`
- `aseo` (impronta)

### 5.10 Mapa operativo

Controlador: `app/Http/Controllers/MapController.php`  
Frontend: `resources/js/map.js`

- Vista `/mapa` para ADMIN/RESPONSABLE/AUDITOR.
- Endpoint JSON `/mapa/armas`.
- Coordenadas priorizadas por:
  1. Puesto activo.
  2. Cliente del trabajador activo.
  3. Cliente activo.
- Cluster con contador.
- Buscador por serie/cliente.
- Icono personalizado:
  - `public/images/map/Icono_Ubicacion.png`

### 5.11 Geocoding y reverse geocoding

Servicio: `app/Services/GeocodingService.php`  
Controller: `app/Http/Controllers/GeocodingController.php`

- Geocoding directo (Nominatim search).
- Reverse geocoding (Nominatim reverse).
- Timeouts cortos y fallback a `null` en error.

### 5.12 Carteras de responsables

Controlador: `app/Http/Controllers/ResponsiblePortfolioController.php`

- ADMIN asigna clientes a responsables (`user_clients`).
- Impide quitar cliente si hay armas activas con ese responsable.
- Transferencia de cartera entre responsables.
- Al transferir cartera:
  - mueve pivote cartera.
  - actualiza `responsible_user_id` en asignaciones activas.

### 5.13 Reportes y alertas

Controlador: `app/Http/Controllers/ReportController.php`  
Controlador: `app/Http/Controllers/AlertsController.php`

Reportes:

- Armas por cliente.
- Armas sin destino.
- Historial por arma (asignaciones + documentos).
- Auditoria filtrable por rango (30/90 dias) y modulo.

Alertas:

- Documentos vencidos.
- Documentos por vencer (30/60/90 dias).

## 6. Auditoria

Tabla: `audit_logs`

Se registran, entre otros:

- Login / logout.
- Password update / reset request / reset completed.
- Profile update / delete.
- CRUD de clientes, puestos, trabajadores, usuarios, armas.
- Cambio de estado de usuario.
- Carga/actualizacion de fotos.
- Carga de documentos.
- Asignaciones cliente e internas.
- Cierres de asignaciones por transferencia/cambio cliente.
- Solicitud, aceptacion y rechazo de transferencias.
- Cambios de cartera.

## 7. Modelo de datos (tablas)

### Catalogos y seguridad

- `users`
- `positions`
- `responsibility_levels`
- `user_clients` (pivot cartera)
- `password_reset_tokens`
- `personal_access_tokens`
- `failed_jobs`

### Nucleo operativo

- `clients`
- `posts`
- `workers`
- `weapons`
- `weapon_client_assignments`
- `weapon_post_assignments`
- `weapon_worker_assignments`
- `weapon_transfers`

### Archivos y trazabilidad

- `files`
- `weapon_photos`
- `weapon_documents`
- `audit_logs`

### Restricciones importantes

- Unicidad de `users.email`.
- Unicidad de `weapons.internal_code` y `weapons.serial_number`.
- Unicidad de `user_clients (user_id, client_id)`.
- Unicidad de foto por tipo en arma: `weapon_photos (weapon_id, description)`.
- Unicidad de activa por arma en asignaciones:
  - `weapon_client_assignments (weapon_id, is_active)`
  - `weapon_post_assignments (weapon_id, is_active)`
  - `weapon_worker_assignments (weapon_id, is_active)`

## 8. Rutas principales (web)

Las rutas estan en `routes/web.php` y `routes/auth.php`.

Grupos funcionales:

- Auth:
  - `login`, `register`, `forgot-password`, `reset-password`, `verify-email`, `logout`.
- Perfil:
  - `profile.edit/update/destroy`.
- Administracion:
  - `users.*`, `users.status`.
- Operacion:
  - `weapons.*`
  - `weapons.client_assignments.store`
  - `weapons.internal_assignments.store/retire`
  - `weapons.photos.*`
  - `weapons.documents.*`
  - `weapons.permit`, `weapons.permit.update`
  - `weapons.imprints.toggle`
- Maestros:
  - `clients.*`, `posts.*`, `workers.*`.
- Transferencias:
  - `transfers.index`, `transfers.bulk`, `transfers.accept`, `transfers.reject`.
- Cartera:
  - `portfolios.index/edit/update/transfer`.
- Reportes y alertas:
  - `reports.*`, `alerts.documents`.
- Mapa:
  - `maps.index`, `maps.weapons`.
- Locale:
  - `locale.switch`.

`php artisan route:list` actualmente reporta 87 rutas.

## 9. Frontend y UX

Entradas Vite (`vite.config.js`):

- `resources/css/app.css`
- `resources/js/app.js`
- `resources/js/map.js`
- `resources/js/location-picker.js`

Caracteristicas:

- Navegacion responsive por rol en `resources/views/layouts/navigation.blade.php`.
- Idioma con cambio de session (`es`, `en`).
- Modales de seleccion de ubicacion con mapa y buscador textual.
- Cluster de mapa con icono personalizado y contador.
- Tailwind escanea vistas y JS:
  - `./resources/views/**/*.blade.php`
  - `./resources/js/**/*.js`

## 10. Archivos y almacenamiento

Discos Laravel (`config/filesystems.php`):

- `local` (privado): `storage/app`
- `public` (publico): `storage/app/public` via `public/storage`

Rutas usadas por el dominio:

- Fotos arma: `storage/app/public/weapons/{weapon_id}/photos`
- Permiso arma: `storage/app/weapons/{weapon_id}/permits`
- Documentos arma: `storage/app/weapons/{weapon_id}/documents`
- Renovacion autogenerada: `storage/app/weapons/{weapon_id}/documents/renovacion_{internal_code}.docx`
- Plantilla de renovacion: `resources/templates/PLANTILLA_REVALIDACION.docx`
- Icono de mapa: `public/images/map/Icono_Ubicacion.png`

## 11. Internacionalizacion

- Locale por defecto: `es` (`config/app.php`).
- Fallback: `es`.
- Middleware de locale: `App\Http\Middleware\SetLocale`.
- Archivos:
  - `resources/lang/es/*`
  - `resources/lang/en.json`

## 12. Instalacion local

1. `composer install`
2. `npm install`
3. Copiar `.env.example` a `.env`
4. Configurar base de datos en `.env`
5. `php artisan key:generate`
6. `php artisan migrate --seed`
7. `php artisan storage:link`
8. `npm run build` (o `npm run dev`)
9. `php artisan serve`

## 13. Variables de entorno relevantes

Base:

- `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `FILESYSTEM_DISK`
- `SESSION_DRIVER`, `SESSION_LIFETIME`
- `CACHE_DRIVER`
- `QUEUE_CONNECTION`
- `MAIL_*`
- `AWS_*` (si se usa S3)

Importante para entorno real:

- Ajustar `APP_URL` al dominio/IP real.
- Revisar `SESSION_DOMAIN`, `SESSION_SECURE_COOKIE` si se publica por HTTPS.

## 14. Seeders y datos iniciales

`DatabaseSeeder` ejecuta:

- `PositionSeeder`
- `ResponsibilityLevelSeeder`
- `AdminUserSeeder`

`AdminUserSeeder` crea/actualiza dos usuarios ADMIN por email.  
Se recomienda cambiar passwords y correos en produccion inmediatamente despues del primer despliegue.

## 15. Pruebas automatizadas

Suite actual en `tests/`:

- Unit basica.
- Feature de autenticacion y perfil (Breeze).

Comando:

- `php artisan test`

Nota: actualmente no hay suite dedicada para reglas de negocio de armas/asignaciones/transferencias, aunque la logica ya esta implementada en controladores/servicios.

## 16. Operacion y despliegue (resumen)

Checklist minimo de produccion:

1. Configurar `.env` de produccion.
2. `composer install --no-dev --optimize-autoloader`
3. `npm ci && npm run build`
4. `php artisan migrate --force`
5. `php artisan storage:link`
6. `php artisan config:cache`
7. `php artisan route:cache`
8. `php artisan view:cache`
9. Configurar backups y rotacion de logs.

## 17. Estructura del proyecto (resumen)

- `app/Http/Controllers`: casos de uso web.
- `app/Services`: logica de negocio reusable.
- `app/Policies`: autorizacion por entidad.
- `app/Models`: modelo de dominio y relaciones.
- `database/migrations`: esquema.
- `database/seeders`: datos iniciales.
- `resources/views`: UI Blade.
- `resources/js`: frontend modular (app, mapa, location-picker).
- `resources/css`: estilos Tailwind.
- `public/images`: assets publicos (incluye icono de mapa).
