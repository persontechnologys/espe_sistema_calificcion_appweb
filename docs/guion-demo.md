# Guion de la presentación y la demostración (10 minutos)

Jueves 05/11/2026, 08:10, Google Meet. **2 min de diapositivas + 8 min de demostración**, seguidos de
10 min de preguntas. Datos: los de `php artisan db:seed` (ver README). Ensaye al menos dos veces con
cronómetro.

## Antes de empezar (T-15 min)

- [ ] Servidor local en marcha (`php artisan serve`) y el sitio de cPanel abierto con HTTPS.
- [ ] Pestañas abiertas, en este orden:
  1. Presentación en PDF (pantalla completa) y las notas del orador a mano.
  2. Sistema: sesión de **admin** en una ventana normal y de **docente 1** en una ventana privada.
  3. Tablero Kanban (`docs/kanban.md`) y el historial de commits del repositorio.
  4. `docs/diagrama-er.md`.
  5. `docs/evidencia/pruebas-postgresql.txt` y la última ejecución de GitHub Actions.
- [ ] Base como al inicio: L00900009 y L00900010 **sin matricular** en el NRC 10234.
- [ ] Audio, cámara y compartir pantalla probados en Meet. Zoom del navegador al 110–125 %.

Usuarios: `admin@demo.test`, `docente1@demo.test` (NRC 10234), `docente2@demo.test`. Contraseña:
la de `DEMO_PASSWORD`.

## Parte 1 · Diapositivas (0:00 – 2:00)

Una idea por diapositiva. El texto completo está en las notas del orador.

| Tiempo | Diapositiva | Idea clave |
|---|---|---|
| 0:00 | 1 · Portada | Quién soy y qué presento: aplicación web en Laravel, probada en dos bases de datos y publicada con HTTPS. |
| 0:15 | 2 · Problema y alcance | Tres parciales de 0 a 20; aprueba con 42/60 **en el total**; vacío no es cero; dos roles. |
| 0:40 | 3 · Kanban | Una tarea en progreso a la vez; cuatro incrementos y una mejora de interfaz; cada commit es una tarea. |
| 1:00 | 4 · Arquitectura | Un solo sistema en capas; las reglas de notas en un único servicio; por qué no microservicios. |
| 1:20 | 5 · Datos | 8 tablas; Estudiantes y Ofertas, muchos a muchos, resuelto con Matrículas; nada en uso se borra. |
| 1:40 | 6 · Calidad y despliegue | 212 pruebas en PostgreSQL y MySQL 8; código → pruebas → publicación → servidor. |
| 1:55 | 7 · Demostración en vivo | "Ahora lo muestro funcionando." Cambiar a la pestaña del sistema. |

Las diapositivas 8 (concurrencia) y 9 (ABP y APS) son **de respaldo**: solo se muestran si preguntan.

## Parte 2 · Demostración (2:00 – 10:00), por competencias

### A. Modelado de base de datos (2:00 – 3:00) · admin

| Tiempo | Acción | Qué decir |
|---|---|---|
| 2:00 | **Períodos**: 202451 abierto, 202450 cerrado | Estados planificado → abierto → cerrado, sin reapertura. |
| 2:20 | **Asignaturas**: COMP-A0G11 | El código es texto único, separado de la clave interna. |
| 2:35 | **Ofertas**: NRC 10234 en 202451 y en 202450 | El NRC es único **dentro del período**; una oferta une asignatura, período y docente. |
| 2:50 | **Estudiantes**: L00900009 | El ID institucional es texto (conserva letras y ceros), no la clave primaria. |

### B. Validaciones y reglas de negocio (3:00 – 5:30)

| Tiempo | Acción | Qué decir |
|---|---|---|
| 3:00 | Admin: ficha NRC 10234 → **matricular** L00900009 | Matricularlo otra vez → mensaje de duplicado: lo impide la base de datos, no solo el formulario. |
| 3:40 | Ventana privada: **docente 1** → Inicio | Solo ve sus propias ofertas. |
| 3:55 | Planilla NRC 10234 → L00900009: `10`, `15`, `17` | Total 42,00, promedio 14,00, **APROBADO**, aunque 1P sea menor que 14. |
| 4:30 | Mostrar L00900003 (41,99) y L00900006 (incompleto) | El promedio se ve 14,00 pero REPROBADO: se decide sobre el total exacto. `—` es pendiente; 0 es una nota. |
| 5:05 | Escribir `20.5` o `10.123` → error | Se valida en el servidor y la base tiene un `CHECK` de 0 a 20. |

### C. Programación, arquitectura y seguridad (5:30 – 6:00) · docente 1

| Tiempo | Acción | Qué decir |
|---|---|---|
| 5:30 | Cambiar la URL a `/offerings/2/grades` → 403; `/admin/users` → 403 | El permiso se revisa en el servidor con el docente actual de la oferta; ocultar botones no basta. |

### D. Corrección y auditoría (6:00 – 7:15) · admin

| Tiempo | Acción | Qué decir |
|---|---|---|
| 6:00 | Ficha NRC 10234 → **Corregir** L00900003 a 14 / 14 / 14 con motivo | El motivo es obligatorio; pasa por el mismo servicio y validaciones. Es la única vía en períodos cerrados. |
| 6:45 | **Auditoría** | Quién, cuándo, valores anteriores y nuevos, y motivo. Se guarda junto con la nota: o se guardan las dos o ninguna. |

### E. Reporte (7:15 – 8:15)

| Tiempo | Acción | Qué decir |
|---|---|---|
| 7:15 | **Reporte** NRC 10234 → Imprimir | Encabezado, detalle por estudiante y resumen de aprobados, reprobados e incompletos. El PDF sale del navegador. |

### F. Metodología, despliegue y justificación técnica (8:15 – 9:40)

| Tiempo | Acción | Qué decir |
|---|---|---|
| 8:15 | Tablero Kanban e historial de commits | Tareas con criterios de aceptación; una en progreso; cada commit corresponde a una tarea. |
| 8:45 | Evidencia de pruebas y GitHub Actions | 212 pruebas y 652 comprobaciones; se ejecutan en PostgreSQL y en MySQL 8, el motor de producción. |
| 9:15 | Sitio en cPanel con el candado HTTPS | Solo la carpeta pública es visible; cabeceras de seguridad y errores sin detalles técnicos. |

### Cierre (9:40 – 10:00)

"Reglas de notas claras, datos protegidos por la propia base de datos, pruebas en dos motores y
publicación segura. Quedo atento a sus preguntas." Volver al PDF, a la diapositiva 10 (QR).

## Mapa de competencias

| Competencia del correo | Diapositiva | En la demo | Respaldo para preguntas |
|---|---|---|---|
| Modelado de base de datos | 5 | A | `docs/diagrama-er.md` |
| Programación | 4 y 6 | B, C | Código de `GradeService` y `GradeCalculator` |
| Validaciones y reglas de negocio | 2 | B, D | Casos 42,00 / 41,99 / incompleto |
| Metodología y buenas prácticas | 3 | F | Kanban, commits; diapositiva 9 si preguntan por docencia |
| Arquitectura, despliegue y concurrencia | 4, 6 | C, F | Diapositiva 8 (concurrencia) |
| Justificación técnica | 4 | Todas | `docs/preguntas-comite.md` |

## Si algo falla

- Si la red o cPanel fallan, use el entorno local. Si también falla el equipo, use el video.
- Si el tiempo se acorta, omita el bloque F y menciónelo en una frase en el cierre.
- Si los datos quedaron alterados por un ensayo, en el **entorno local** puede restaurarlos con
  `php artisan migrate:fresh --seed`. **Este comando borra toda la base**: nunca lo ejecute en producción.
