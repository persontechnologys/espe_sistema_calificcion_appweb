# Sistema de Gestión Académica ESPE (demostración técnica)

Aplicación web para gestionar estudiantes, docentes, asignaturas, períodos académicos,
ofertas identificadas por NRC, matrículas y **tres calificaciones parciales** por matrícula,
con cálculo automático del total y el promedio, la regla de aprobación **total ≥ 42/60**,
auditoría de cambios y un reporte imprimible inspirado en el formato institucional.

> Sistema de demostración. No es un sistema ni un documento oficial de la Universidad de las
> Fuerzas Armadas - ESPE. Todos los datos de ejemplo (IDs, nombres, NRC) son ficticios.

El documento rector del proyecto (alcance, reglas y decisiones) es [CLAUDE.md](CLAUDE.md).

## Funcionalidades

| Rol | Puede |
|---|---|
| **Administrador** | Usuarios y docentes, estudiantes, asignaturas, períodos (abrir/cerrar), ofertas NRC y asignación de docentes, matrículas, reportes, auditoría y **corrección de notas con motivo obligatorio** (incluso en períodos cerrados). |
| **Docente** | Ver solo sus ofertas, registrar 1P/2P/3P de sus estudiantes mientras el período esté abierto y ver los reportes de sus ofertas. |

Reglas de calificación (CLAUDE.md §4):

- Cada parcial va de 0 a 20 y admite hasta dos decimales; se guarda como `DECIMAL(5,2)`.
- `total = 1P + 2P + 3P` es exacto, porque se calcula con enteros en centésimas y sin `float`.
- `promedio = total / 3` se redondea a dos decimales **solo para mostrarlo**.
- **APROBADO** si `total ≥ 42,00`. No hay nota mínima por parcial. El estado se decide sobre el total exacto: con 41,99 el promedio se muestra 14,00 y el estado es REPROBADO.
- Un parcial vacío se guarda como `NULL` (pendiente) y es distinto de `0,00`. Si falta algún parcial, el registro es **INCOMPLETO**.

## Requisitos

- PHP **8.4** o superior, con las extensiones `pdo_pgsql` (local) o `pdo_mysql` (producción), `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo` e `intl`.
- Composer 2.
- PostgreSQL 13 o superior en local, o MySQL **8.0.16** o superior en producción (las restricciones `CHECK` lo requieren).
- No se necesita Node.js: Bootstrap 5.3.8 y Bootstrap Icons 1.13.2 se sirven desde `public/vendor/` (sin CDN).
  El CSS y el JavaScript propios están en `public/css/app.css` y `public/js/app.js`.

`composer.json` fija `config.platform.php = 8.4.0` para que las dependencias sean compatibles con el hosting.

## Instalación local

```bash
git clone <repositorio> espe-academico && cd espe-academico
composer install
cp .env.example .env
php artisan key:generate
# Edite .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD y DEMO_PASSWORD
php artisan migrate
php artisan db:seed          # datos ficticios de demostración
php artisan serve            # http://localhost:8000
```

### Variables de entorno relevantes

| Variable | Descripción |
|---|---|
| `DB_CONNECTION` | `pgsql` en local y `mysql` en producción |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Conexión a la base de datos |
| `DEMO_PASSWORD` | Contraseña de los usuarios de demostración (mínimo 8 caracteres). Solo se usa en `db:seed` |
| `APP_ENV`, `APP_DEBUG` | En producción: `production` y `false` |
| `SESSION_SECURE_COOKIE` | `true` en producción con HTTPS |

`.env` no se versiona y `.env.example` no contiene secretos.

## Usuarios de demostración

Se crean con `php artisan db:seed`. La contraseña de todos es el valor de `DEMO_PASSWORD`.

| Correo | Rol | Notas |
|---|---|---|
| `admin@demo.test` | Administrador | |
| `docente1@demo.test` | Docente (D00000101) | NRC 10234 en los períodos 202451 (abierto) y 202450 (cerrado) |
| `docente2@demo.test` | Docente (D00000102) | NRC 10235; el docente 1 no tiene acceso a esta oferta |
| `inactivo@demo.test` | Docente inactivo | No puede iniciar sesión |

Casos preparados en el **NRC 10234, período 202451**:

| ID | 1P | 2P | 3P | Total | Promedio | Estado | Caso |
|---|---|---|---|---|---|---|---|
| L00900001 | 10,00 | 15,00 | 17,00 | 42,00 | 14,00 | APROBADO | Aprueba con 1P < 14 |
| L00900002 | 10,10 | 15,20 | 16,70 | 42,00 | 14,00 | APROBADO | Suma decimal exacta |
| L00900003 | 13,99 | 14,00 | 14,00 | 41,99 | 14,00 | REPROBADO | Promedio redondeado engañoso |
| L00900004 | 20,00 | 20,00 | 20,00 | 60,00 | 20,00 | APROBADO | Máximo |
| L00900005 | 0,00 | 12,00 | 15,00 | 27,00 | 9,00 | REPROBADO | 0 es una nota registrada |
| L00900006 | 12,00 | 14,00 | — | — | — | INCOMPLETO | 3P pendiente |
| L00900007 | — | — | — | — | — | INCOMPLETO | Sin registro de notas |
| L00900008 | 16,00 | 16,00 | 10,00 | 42,00 | 14,00 | APROBADO | Corregido por el admin (9,99 → 10,00), auditado |

Los estudiantes L00900009 y L00900010 no están matriculados en el NRC 10234, para hacer la matrícula en vivo.

## Pruebas

```bash
php artisan test
```

- La suite usa **PostgreSQL** (base `espe_test`, configurada en `phpunit.xml`). No se usa SQLite porque no reproduce `CHECK`, `DECIMAL` ni los bloqueos.
- **GitHub Actions** ([.github/workflows/tests.yml](.github/workflows/tests.yml)) ejecuta la suite en **PostgreSQL 16 y MySQL 8.0**. También migra desde una base limpia y ejecuta `migrate:refresh`.
- Evidencia de la última ejecución local: [docs/evidencia/pruebas-postgresql.txt](docs/evidencia/pruebas-postgresql.txt).

Qué se prueba: cálculo y redondeo (incluidos 41,99 y 10,10 + 15,20 + 16,70), validaciones 0–20 y de dos decimales, NULL frente a 0, restricciones de la base (UNIQUE, CHECK, RESTRICT), NRC único por período, matrículas duplicadas y en períodos cerrados, autorización (oferta ajena, docente reasignado, usuario inactivo, rutas de administración), bloqueo optimista, auditoría en la misma transacción, corrección con y sin motivo, reportes y filtros, seguridad (XSS, búsqueda sin inyección, cabeceras) y seeders.

## Arquitectura

Monolito MVC de Laravel 13 con Blade y Bootstrap 5.

| Capa | Dónde |
|---|---|
| Rutas y middleware (`auth`, `active`, `role:admin`) | `routes/web.php`, `app/Http/Middleware` |
| Validación y normalización de entrada | `app/Http/Requests` |
| Autorización por registro (docente **actual** de la oferta) | `app/Policies` |
| Cálculo académico (único) | `app/Services/Grades/GradeCalculator.php` |
| Escritura de notas: transacción, bloqueos, versión y auditoría | `app/Services/Grades/GradeService.php` |
| Modelos, relaciones y normalización de códigos | `app/Models` |
| Esquema versionado | `database/migrations` |
| Diagrama ER | [docs/diagrama-er.md](docs/diagrama-er.md) |

## Decisiones técnicas principales

Lista completa en [CLAUDE.md §18](CLAUDE.md).

- **Identificadores.** Cada tabla usa un `id` técnico como clave primaria. El ID institucional, el código de asignatura, el código de período y el NRC son texto con `UNIQUE` y se normalizan (`trim` + mayúsculas) para que PostgreSQL y MySQL se comporten igual.
- **NRC.** `UNIQUE(academic_period_id, nrc)`: es único dentro de un período y puede repetirse en otro.
- **Docente = `users.role = 'teacher'`**, con `CHECK` que obliga a tener ID institucional. Los estudiantes tienen su propia tabla porque no inician sesión.
- **Derivados no almacenados.** Total, promedio y estado se calculan a partir de los parciales (3FN; no pueden quedar desactualizados).
- **Concurrencia.**
  - `UNIQUE` en matrículas y notas.
  - Bloqueo compartido de la oferta y del período durante el guardado: no se puede cerrar el período ni reasignar la oferta a mitad de una operación.
  - `lockForUpdate()` sobre la fila de la nota.
  - **Bloqueo optimista** con la columna `grades.version`.
- **Auditoría.** Se escribe en la misma transacción que la nota (si falla, la nota no se guarda), solo admite inserciones y distingue `registro` de `correccion` (esta última con motivo).
- **Borrado.** Todas las FK son `ON DELETE RESTRICT`; los registros en uso se inactivan.
- **Seguridad.**
  - Login con límite de intentos (5 por correo+IP) y regeneración de sesión.
  - Los usuarios inactivos quedan expulsados aunque tengan una sesión abierta.
  - CSRF; escape de Blade con `{{ }}`; consultas parametrizadas.
  - Cabeceras `X-Frame-Options`, `nosniff`, `Referrer-Policy` y HSTS bajo HTTPS.
  - Páginas de error sin trazas.

## Despliegue

Guía paso a paso para cPanel: [docs/despliegue-cpanel.md](docs/despliegue-cpanel.md).
# espe_sistema_calificcion_appweb
