# SJ Armory

Sistema de gestion de armamento y trazabilidad para una armeria: armas, custodia, asignacion a clientes, documentos, fotos, reportes y auditoria.

## Funcionalidades clave

- CRUD de armas y clientes.
- Custodia activa por arma (solo una activa por vez).
- Asignacion activa por arma (solo una activa por vez).
- Carga y gestion de fotos (disco public) y documentos (disco local).
- Reportes por custodio, por cliente, sin destino, historial y auditoria.
- Alertas por vencimiento de documentos y revalidaciones.

## Roles y permisos

- ADMIN: acceso total, gestiona armas, clientes, custodia, estado operativo, carteras, reportes y alertas.
- RESPONSABLE: ve armas bajo su custodia y clientes en su cartera; puede asignar destino segun su nivel.
- AUDITOR: acceso de solo lectura.

## Niveles de responsabilidad

Definidos en `database/seeders/ResponsibilityLevelSeeder.php`:

- Nivel 1: solo ver.
- Nivel 2: asignar a cliente sin destino activo.
- Nivel 3: reasignar y retirar destino.
- Nivel 4: igual que nivel 3.

## Flujo de negocio principal

- Custodia: al asignar una nueva custodia, se cierra la custodia activa anterior y se registra en auditoria.
- Asignaciones: solo se permite asignar si el arma esta en estados operativos permitidos y el cliente pertenece a la cartera del responsable.
- Auditoria: eventos automaticos para modelos auditables y registros manuales para acciones criticas (fotos, documentos, custodia, asignaciones, cartera).

## Archivos y almacenamiento

- Fotos: `storage/app/public/weapons/{weapon_id}/photos` (disco `public`).
- Documentos: `storage/app/weapons/{weapon_id}/documents` (disco `local`).

## Instalacion

1) `composer install`
2) `npm install`
3) Copiar `.env.example` a `.env` y configurar la base de datos.
4) `php artisan key:generate`
5) `php artisan migrate --seed`
6) `php artisan storage:link`
7) `npm run build` (o `npm run dev` para desarrollo)
8) `php artisan serve`

## Seeders y credenciales

- Se crean posiciones basicas y niveles de responsabilidad.
- Se crea un usuario admin. Variables usadas (con valores por defecto).
- `SEED_ADMIN_EMAIL` (default: `admin@example.com`)
- `SEED_ADMIN_PASSWORD` (default: `password`)
- `SEED_ADMIN_NAME` (default: `Administrador`)

## Rutas principales

- Autenticacion: `routes/auth.php`
- Armamento y clientes: `routes/web.php`
- Reportes y alertas: `routes/web.php`

## Notas

- La asignacion a clientes valida nivel de responsabilidad y cartera.
- La custodia y la asignacion solo permiten un registro activo por arma.

