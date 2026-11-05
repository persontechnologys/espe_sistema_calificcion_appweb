# Repaso para las preguntas del comité (especificación §17)

Respuestas breves, con ventaja, límite y alternativa, y con referencias al código para mostrarlo en pantalla.

## Tecnología y arquitectura

**¿Por qué Laravel y PHP?**
- **Ventajas:** el hosting (cPanel) ofrece PHP y Composer de forma nativa. Laravel trae autenticación, CSRF, validación, Policies, migraciones y pruebas integradas, así que hay menos código propio que proteger.
- **Límite:** el modelo de ejecución es por solicitud (sin procesos persistentes).
- **Alternativas:** Django o Spring. Habrían exigido otro tipo de hosting.

**¿Por qué MySQL? ¿Y por qué PostgreSQL en local?**
- **MySQL:** es el motor que ofrece el hosting de producción.
- **PostgreSQL en local:** era el motor disponible en el equipo. Además es más estricto, así que destapa errores que MySQL toleraría.
- **Cómo se garantiza que funcione igual en ambos:**
  - Solo Eloquent y el Schema Builder; los `CHECK` en SQL estándar.
  - Códigos normalizados a mayúsculas (MySQL `_ci` frente a PostgreSQL sensible a mayúsculas).
  - Búsquedas con `LOWER()`.
  - Captura de `UniqueConstraintViolationException`, igual en ambos motores.
  - La suite se ejecuta en los dos motores en CI.
- **Por qué no SQLite en pruebas:** no reproduce `CHECK`, `DECIMAL` ni los bloqueos.

**¿Por qué un monolito modular?**
- **Ventajas:** un equipo, un dominio pequeño y un hosting compartido. Un monolito tiene transacciones ACID locales y un solo despliegue.
- **Problema de los microservicios aquí:** añadirían red, consistencia eventual y operación sin ningún beneficio.
- **Cómo se mantiene modular:** el cálculo y la escritura de notas están aislados en `app/Services/Grades`.

**Responsabilidad de cada capa.**

| Capa | Responsabilidad | Ejemplo |
|---|---|---|
| Rutas y middleware | Quién puede entrar | `role:admin`, `active` |
| Form Requests | Validar y normalizar | `GradeRequest`, `CourseOfferingRequest` |
| Policies | Autorizar sobre un registro concreto | `EnrollmentPolicy::recordGrade` |
| Servicios | Reglas de negocio | `GradeCalculator`, `GradeService` |
| Modelos | Relaciones, casts y mutators | Normalización de códigos |
| Blade | Solo presentación | Sin reglas de negocio |

**¿Qué cambiaría con 50 000 estudiantes?**
- Paginación (ya existe).
- Índices compuestos según las consultas reales; revisarlos con `EXPLAIN`.
- Caché de reportes de períodos cerrados (son inmutables).
- Réplica de lectura para los reportes.
- Colas para generar PDF masivos.
- El modelo de datos no cambia.

## Modelo de datos

**Asignatura, NRC, docente y período.**
- `course_offerings` es la oferta concreta: FK a `subjects`, `academic_periods` y `users` (el docente), más el NRC.
- La restricción `UNIQUE(academic_period_id, nrc)` hace que el NRC sea único por período.
- Las matrículas cuelgan de la oferta, no de la asignatura.

**¿Por qué el ID institucional no es la clave primaria?**
- Es un dato del negocio: puede cambiar, tener letras o ceros iniciales, y su formato no lo controla el sistema.
- La clave primaria técnica (`id`) es estable, compacta y hace eficientes las FK.
- El ID institucional se guarda como texto con su propio `UNIQUE`.

**Forma normal.**
- El esquema está en 3FN: cada atributo depende solo de la clave de su tabla.
- Total, promedio y estado no se guardan, porque dependen de los parciales (serían dependencias transitivas).
- El nombre de la asignatura está solo en `subjects`, no repetido en cada oferta.

**Índices y para qué sirven.**
- Los `UNIQUE` sirven también como índices de búsqueda: ID institucional, código, `(period, nrc)`, `(student, offering)` y `enrollment_id` en `grades`.
- `course_offerings(teacher_id)`: "mis ofertas" del docente.
- `enrollments(course_offering_id)`: planilla y reporte de un NRC.
- `students(last_names, first_names)`: ordenar listados y reportes.
- `audit_logs(enrollment_id)` y `audit_logs(created_at)`: historial.

**¿Por qué el docente es un `user` y el estudiante tiene su propia tabla?**
- El docente inicia sesión, así que necesita credenciales; el estudiante no.
- Meter a los estudiantes en `users` obligaría a usar correos y contraseñas ficticios y mezclaría datos de acceso con datos académicos.
- Si mañana los estudiantes debieran iniciar sesión, bastaría añadir `students.user_id` opcional.

## Calificaciones

**¿Cómo se calcula el total y el promedio?**
- `GradeCalculator` convierte cada parcial a centésimas enteras (10,10 → 1010).
- Suma con enteros (exacto) y compara contra 4200.
- El promedio se redondea a dos decimales solo para mostrarlo.
- Código: [app/Services/Grades/GradeCalculator.php](../app/Services/Grades/GradeCalculator.php).

**¿Por qué `DECIMAL` y no `FLOAT`?**
- `FLOAT` es binario: 10,1 no se representa exactamente.
- `10.1 + 15.2 + 16.7` puede dar `41.999…`, y un estudiante con 42 reprobaría.
- `DECIMAL(5,2)` guarda exactamente dos decimales.
- La prueba `suma_exacta_de_decimales_sin_error_de_punto_flotante` lo demuestra.

**¿Por qué el estado se decide sobre el total y no sobre el promedio redondeado?**
- La regla es "total ≥ 42". Con 41,99 el promedio es 13,9966…, que se muestra 14,00.
- Si se decidiera sobre el promedio mostrado, aprobaría alguien que no cumple la regla.
- El reporte muestra el total para que la decisión sea verificable.

**¿Por qué `NULL` y `0` son distintos?**
- `0,00` es una nota registrada (el estudiante rindió y obtuvo cero).
- `NULL` es pendiente.
- Si se confundieran, un parcial pendiente reprobaría al estudiante antes de tiempo. Con `NULL` el registro queda INCOMPLETO.

**¿Por qué no se almacenan el promedio ni el estado?**
- Son derivados: guardarlos crea dos fuentes de verdad que pueden divergir (por ejemplo, si se corrige un parcial y no se recalcula).
- Calcularlos es barato (tres sumas).
- Si se necesitaran por rendimiento, se persistirían desde el único servicio, con pruebas de consistencia.

**¿Cómo se garantiza la regla de aprobación?**
- Un único punto de cálculo, con pruebas de los casos límite (42,00, 41,99, 1P < 14, 0 y NULL).
- El servidor ignora cualquier promedio o estado que envíe el cliente (hay una prueba).
- `CHECK` en la base limita cada parcial a 0–20.

**¿Cómo se corrige una nota mal ingresada?**
- El administrador usa "Corregir": motivo obligatorio y el mismo `GradeService` (mismas validaciones, bloqueo y versión).
- La auditoría registra `correccion` con los valores anteriores y nuevos.
- Funciona también en períodos cerrados, sin reabrirlos.

## Concurrencia y transacciones

**¿Qué es una transacción y qué significa ACID?**
Es una unidad de trabajo que se completa entera o no se aplica. Ejemplo: guardar la nota y su auditoría.
- **A (atomicidad):** todo o nada.
- **C (consistencia):** las restricciones siempre se cumplen.
- **I (aislamiento):** las transacciones concurrentes no se ven a medias.
- **D (durabilidad):** lo confirmado persiste.

**¿Cómo se evita una matrícula duplicada, incluso con dos solicitudes a la vez?**
- Con `UNIQUE(student_id, course_offering_id)`: la segunda inserción falla en la base y se captura para mostrar "ya está matriculado".
- Una consulta previa ("¿existe?") no basta, porque las dos solicitudes podrían pasarla a la vez.

**Aislamiento de InnoDB y actualización perdida.**
- InnoDB usa `REPEATABLE READ` por defecto; PostgreSQL, `READ COMMITTED`.
- **Actualización perdida:** A y B leen la nota, A guarda y B guarda encima sin haber visto el cambio de A.
- El diseño no depende del nivel de aislamiento: usa `SELECT … FOR UPDATE` sobre la fila de la nota, que lee la versión más reciente en ambos motores.

**¿Bloqueo optimista o pesimista?**
- **Pesimista** (`FOR UPDATE`): bloquea la fila mientras dura la transacción. Se usa durante el guardado, que dura milisegundos.
- **Optimista** (`grades.version`): no bloquea mientras el usuario tiene el formulario abierto, que pueden ser minutos. Al guardar se compara la versión; si cambió, se rechaza y se informa.
- Se combinan los dos: el optimista cubre el tiempo humano y el pesimista, el instante de escritura.
- Se usa una versión entera y no `updated_at` porque `updated_at` tiene resolución de segundos.

**¿Qué pasa si se cierra el período mientras un docente guarda?**
- El guardado toma un bloqueo compartido (`FOR SHARE`) sobre el período y la oferta.
- Cerrar el período necesita un bloqueo exclusivo, así que espera; o, si llegó antes, el guardado ve el período ya cerrado y lo rechaza.
- Varios docentes pueden guardar a la vez, porque los bloqueos compartidos no se excluyen entre sí.

**¿Cómo se auditan los cambios?**
- `GradeService` inserta en `audit_logs` dentro de la misma transacción que la nota: si la auditoría falla, la nota no se guarda (hay una prueba).
- No se depende de observers de Eloquent, que se saltan con actualizaciones masivas.
- El modelo prohíbe editar o borrar registros de auditoría.

## Seguridad

**¿Cómo se controla el acceso del docente?**
- Middleware `role:admin` para el área de administración.
- Policies que comparan `offering.teacher_id` con el usuario, leyendo el docente **actual**: si se reasigna la oferta, el anterior pierde el acceso.
- `scopeBindings` hace que una matrícula de otra oferta en la URL devuelva 404.
- El mismo control se repite dentro de `GradeService`.
- Hay pruebas de cada intento de acceso ajeno.

**¿Cómo se previenen la inyección SQL, el XSS y el CSRF?**
- **Inyección SQL:** Eloquent usa consultas preparadas; en la búsqueda, la única expresión cruda usa nombres de columna fijos y el término como parámetro.
- **XSS:** Blade escapa todo con `{{ }}` y no se usa `{!! !!}` (hay una prueba con `<script>`).
- **CSRF:** `@csrf` en todos los formularios; un POST sin token devuelve 419.

**¿Cómo se protege el proyecto en cPanel?**
- El Document Root apunta a `public/`; `.env` queda fuera de la web y con permisos 600.
- `APP_DEBUG=false`, HTTPS forzado y cookie segura.
- Usuario MySQL con privilegios mínimos.
- Respaldos antes de migrar; nunca `migrate:fresh` en producción.

**¿Qué es el problema N+1 y cómo se evita?**
- Ocurre cuando se carga una lista y luego se hace una consulta por cada elemento (por ejemplo, la nota de cada estudiante).
- Se evita con `with(['student', 'grade'])`.
- En desarrollo, `Model::preventLazyLoading()` hace fallar cualquier carga diferida, así que un N+1 rompe las pruebas.

## Proceso

**¿Por qué Kanban y no Scrum?**
- Es una persona, en un plazo corto y con un flujo continuo de tareas.
- Las ceremonias de Scrum (sprints, retrospectivas, roles) no aportarían valor aquí.
- Kanban da visibilidad y limita el trabajo en curso (una tarea a la vez).
- Cada commit corresponde a una tarea.

**¿Cómo se prueban las reglas de negocio?**
- Pruebas unitarias del calculador.
- Pruebas de integración del servicio contra PostgreSQL real: transacciones, versión y auditoría.
- Pruebas HTTP de cada caso de uso y de cada intento no autorizado.
- Pruebas de las restricciones de la base, saltándose la aplicación.
- La CI repite todo en MySQL 8.
