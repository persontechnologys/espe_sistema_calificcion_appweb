# Tablero Kanban

Tarjetas listas para crear en GitHub Projects o Trello. Columnas: Backlog · Por hacer ·
En progreso (WIP = 1) · En revisión · En pruebas · Terminado.

Para relacionar cada commit con su tarjeta, cree un *issue* por tarjeta (`T01`…) y enlácelo con los hashes indicados.

Incrementos: 1 Base (T01–T06) · 2 Administración (T07–T09) · 3 Matrículas y notas (T10–T13) ·
4 Reportes y cierre (T14–T20) · Rediseño de interfaz (T21).

## Terminado

| # | Tarea | Criterios de aceptación | Evidencia | Commits |
|---|---|---|---|---|
| T01 | Proyecto Laravel 13 | `php ^8.4` con `platform.php=8.4`; arranca en local | `php artisan --version` | d8241e5, cae3e7c |
| T02 | Esquema de datos | 8 tablas con UNIQUE, CHECK y RESTRICT; migran y revierten desde base limpia | `SchemaConstraintsTest`, `migrate:refresh` | 395c262 |
| T03 | Cálculo de notas | Total exacto en centésimas; 41,99 → REPROBADO; NULL → INCOMPLETO | `GradeCalculatorTest` (22) | 92d1615 |
| T04 | Modelos y normalización | Códigos en mayúsculas; `$fillable` explícito; auditoría de solo inserción | `SchemaConstraintsTest` | 75b34e9 |
| T05 | Autenticación | Límite de intentos; inactivos expulsados; `role:admin` | `AuthenticationTest`, `RoleMiddlewareTest` | f0ac93b |
| T06 | CI en dos motores y diagrama ER | Matriz PostgreSQL/MySQL; ER en Mermaid | `.github/workflows/tests.yml` | 8d2e459 |
| T07 | Asignaturas y períodos | CRUD; abrir y cerrar solo hacia adelante | `Subject…Test`, `AcademicPeriod…Test` | f2801c1, 5594b7b |
| T08 | Estudiantes, usuarios y docentes | Docente con ID obligatorio; el admin restablece contraseñas | `Student…Test`, `User…Test` | 929e727 |
| T09 | Ofertas NRC | NRC único por período; solo docente activo; reasignación | `CourseOffering…Test` | a44f37d |
| T10 | GradeService | Transacción, bloqueos, versión y auditoría; corrección con motivo | `GradeServiceTest` (18) | bcfcddf |
| T11 | Policies | Docente actual; oferta ajena → 403 | `TeacherGradingTest` | 3df43f8 |
| T12 | Matrículas | Duplicado y período cerrado rechazados | `EnrollmentTest` | 2786c26 |
| T13 | Planilla y corrección | Errores por fila; conflicto de versión informado | `TeacherGradingTest`, `AdminCorrectionTest` | b95beb0 |
| T14 | Reporte | Encabezado, detalle, resumen; imprimible; filtros | `ReportTest` | 8dd3369 |
| T15 | Vista de auditoría | Filtros por operación, estudiante y NRC; solo admin | `AuditLogViewTest` | 499d576 |
| T16 | Seeders de demo | Casos 42,00 / 41,99 / incompleto / corrección | `DemoSeederTest` | 6d9fee0 |
| T17 | Revisión de seguridad | Sin `{!! !!}`; cabeceras; CSRF 419; `composer audit` limpio | `SecurityTest` | afe0e6e |
| T18 | Documentación | README, guía de cPanel, guion de demo, evidencia de pruebas | `docs/` | 87e7f3c |
| T19 | Repositorio y CI | GitHub Actions en verde en PostgreSQL **y MySQL 8** | `.github/workflows/tests.yml` | 8d2e459 |
| T20 | Despliegue en cPanel | Rama `deploy` con vendor de producción; `/init` con token; Document Root y `.htaccess`; HTTPS | `docs/despliegue-cpanel.md` §9 | e4ca574, 29dc9f9, dc821a4 |
| T21 | Rediseño de interfaz | Sistema de diseño, sidebar, dashboard, planilla, reportes; paleta verde con contraste AA | Especificación, decisiones 35–45 | a135b79 … daca4c7 |
