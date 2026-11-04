# Guion de la demostración (10 minutos)

Datos: los de `php artisan db:seed` (ver README). Ensaye al menos dos veces con cronómetro.

## Antes de empezar (T-15 min)

- [ ] Servidor local en marcha (`php artisan serve`) y, si está desplegado, también cPanel.
- [ ] Pestañas abiertas:
  1. Sistema: sesión de admin en una ventana normal y de docente 1 en una ventana privada.
  2. Repositorio (historial de commits).
  3. Tablero Kanban.
  4. `docs/diagrama-er.md`.
  5. `docs/evidencia/pruebas-postgresql.txt` y la última ejecución de GitHub Actions.
- [ ] Si se repitió el ensayo, deje la base como al inicio: los estudiantes L00900009 y L00900010 sin matricular en el NRC 10234.
- [ ] Video de respaldo listo. Audio, cámara y compartir pantalla probados en Meet.
- [ ] Zoom del navegador en 110–125 % para que se lea bien.

## Recorrido

| Min | Acción | Qué decir (idea clave) |
|---|---|---|
| 0:00 | Presentación | Sistema pequeño y coherente: tres parciales, aprobación con 42/60, dos roles y auditoría. Laravel 13, MySQL 8 en producción. |
| 0:45 | Login como **admin** | Contraseñas con bcrypt, límite de intentos y regeneración de la sesión. |
| 1:15 | **Períodos**: 202451 abierto, 202450 cerrado | Estados planificado → abierto → cerrado, sin reapertura. El estado no se edita en el formulario. |
| 1:45 | **Asignaturas**: COMP-A0G11 | El código es texto único, separado de la clave primaria. |
| 2:15 | **Ofertas**: NRC 10234 en 202451 y en 202450 | El NRC es único **por período** (`UNIQUE(period, nrc)`). Oferta = asignatura + período + docente. |
| 2:45 | **Estudiantes**: L00900009 | El ID institucional es texto (conserva letras y ceros), no la clave primaria. |
| 3:15 | Ficha NRC 10234 → **matricular** L00900009 | Matricular otra vez al mismo estudiante → mensaje de duplicado (UNIQUE + captura de la excepción). |
| 4:00 | Ventana privada: **docente 1** → Inicio | Solo ve sus ofertas. |
| 4:20 | Planilla NRC 10234 → notas para L00900009: `10`, `15`, `17` | Al guardar: total 42,00, promedio 14,00, **APROBADO** aunque 1P < 14. |
| 5:00 | Mostrar las filas L00900003 (41,99) y L00900006 (incompleto) | El promedio se muestra 14,00 pero el estado es REPROBADO, porque se decide sobre el total exacto. `—` es pendiente; 0 es una nota. |
| 5:45 | Escribir `20.5` o `10.123` → error | Validación en el servidor y `CHECK` en la base. |
| 6:15 | **Autorización**: cambiar la URL a `/offerings/2/grades` → 403; `/admin/users` → 403 | La Policy usa el docente actual; ocultar botones no basta. |
| 6:45 | Admin: ficha NRC 10234 → **Corregir** L00900003 a 14 / 14 / 14 con motivo | Mismo servicio, motivo obligatorio y auditado como `correccion`. Es la única vía en períodos cerrados. |
| 7:30 | **Auditoría** | Quién, cuándo, valores anteriores y nuevos, y motivo. Se escribe en la misma transacción que la nota. |
| 8:00 | **Reporte** NRC 10234 → Imprimir | Encabezado, detalle y resumen de aprobados, reprobados e incompletos. El PDF se genera desde el navegador. |
| 9:00 | Pruebas y repositorio | 176 pruebas automatizadas en PostgreSQL; CI en PostgreSQL y MySQL 8; commits por tarea; diagrama ER. |
| 9:40 | Cierre | Integridad garantizada en tres capas: validación, base de datos y transacciones con bloqueos. |

## Si algo falla

- Si la red o cPanel fallan, use el entorno local. Si también falla el equipo, use el video.
- Si los datos quedaron alterados por un ensayo, en el **entorno local** puede restaurarlos con
  `php artisan migrate:fresh --seed`. **Este comando borra toda la base**: nunca lo ejecute en producción.
