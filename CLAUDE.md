# CLAUDE.md

## Proyecto: Sistema de Gestión Académica ESPE

> Documento rector para orientar el análisis, diseño, implementación,
> pruebas y despliegue del sistema.
>
> **Estado:** especificación aprobada. Decisiones pendientes cerradas y
> vacíos del análisis incorporados el 2026-11-04 (ver sección 18).\
> **Evaluación:** entrevista técnica virtual el jueves 2026-11-05 a las
> 08:10 (demostración de 10 minutos + 10 minutos de preguntas).\
> **Regla de trabajo:** no implementar funcionalidades, generar código
> ni modificar archivos hasta que el responsable lo solicite
> expresamente.

------------------------------------------------------------------------

## 1. Propósito del proyecto

Desarrollar una aplicación web para gestionar información académica de
estudiantes, docentes, asignaturas, ofertas académicas identificadas
mediante NRC, matrículas y calificaciones.

El sistema debe representar un flujo académico reconocible en el
contexto de la Universidad de las Fuerzas Armadas - ESPE y permitir
consultar reportes de calificaciones con una presentación similar al
reporte institucional de referencia proporcionado.

El objetivo no es construir un sistema universitario integral. El
alcance es una aplicación pequeña, coherente, segura, mantenible y
técnicamente defendible en una evaluación para el cargo de Profesor
Ocasional 1.

## 2. Instrucciones generales para Claude

1.  Leer este archivo antes de proponer cambios o ejecutar tareas
    relacionadas con el proyecto.
2.  Respetar el alcance, las reglas académicas y las decisiones técnicas
    aquí establecidas.
3.  No inventar reglas institucionales que no hayan sido confirmadas.
4.  No cambiar silenciosamente las reglas de calificación, los roles,
    las relaciones ni la arquitectura.
5.  Antes de implementar una tarea, explicar brevemente qué se hará y
    qué archivos o componentes se verán afectados, salvo que el
    responsable solicite ejecución directa.
6.  No introducir dependencias, servicios externos o complejidad
    arquitectónica sin justificar su necesidad.
7.  Priorizar claridad, seguridad, integridad de datos, mantenibilidad y
    facilidad de explicación técnica.
8.  Mantener las validaciones importantes tanto en la aplicación como en
    la base de datos cuando corresponda.
9.  No confiar exclusivamente en controles de interfaz para proteger
    operaciones o datos.
10. No almacenar secretos, contraseñas, claves ni credenciales en el
    repositorio.
11. No realizar despliegues, migraciones destructivas, borrados de datos
    ni operaciones irreversibles sin autorización expresa.
12. No afirmar que una prueba, compilación o despliegue fue exitoso si
    no se ejecutó y verificó.
13. Ante decisiones técnicas o de diseño, **tomar la mejor solución**
    sin preguntar, justificarla brevemente y registrarla en la sección
    18. Preguntar solo cuando la decisión cambie una regla académica
    institucional (por ejemplo, la regla de aprobación) o cuando existan
    dos opciones igual de válidas con consecuencias distintas para el
    responsable. (Preferencia del responsable, 2026-11-04.)
14. Mantener este documento actualizado cuando el responsable apruebe
    cambios de alcance o decisiones importantes.

## 3. Alcance funcional

### 3.1 Funcionalidades incluidas

-   Autenticación de usuarios.
-   Gestión de usuarios y roles.
-   Gestión de estudiantes.
-   Gestión de docentes.
-   Gestión de asignaturas.
-   Gestión de períodos académicos.
-   Gestión de ofertas académicas o paralelos identificados por NRC.
-   Asignación de docentes a ofertas académicas.
-   Matrícula de estudiantes en ofertas académicas.
-   Registro de tres calificaciones parciales por matrícula.
-   Cálculo automático del promedio y total acumulado.
-   Determinación del estado académico según la regla definida.
-   Consulta de calificaciones por período, asignatura, NRC y
    estudiante.
-   Reporte de calificaciones con formato inspirado en el documento de
    referencia ESPE.
-   Registro de auditoría de cambios relevantes, especialmente
    modificaciones de calificaciones.

### 3.2 Fuera del alcance inicial

No implementar salvo autorización posterior:

-   Registro de deberes, tareas, lecciones, proyectos o investigaciones.
-   Evaluaciones ponderadas configurables por actividad.
-   Un libro de calificaciones con cantidad arbitraria de componentes.
-   Gestión integral de asistencia.
-   Facturación, pagos o cartera.
-   Integración con sistemas institucionales externos.
-   Aplicación móvil nativa.
-   Arquitectura de microservicios.
-   Infraestructura distribuida innecesaria.
-   Colas, Redis o servicios de tiempo real si no existe una necesidad
    demostrada.

## 4. Reglas académicas de calificación

El sistema debe utilizar exactamente tres parciales por matrícula dentro
de una oferta académica.

  Componente                Rango
  ---------------------- --------
  Primer parcial (1P)      0 a 20
  Segundo parcial (2P)     0 a 20
  Tercer parcial (3P)      0 a 20
  Total acumulado          0 a 60
  Promedio                 0 a 20

### 4.1 Fórmulas

**Total acumulado:**

`total_acumulado = parcial_1 + parcial_2 + parcial_3`

**Promedio:**

`promedio = total_acumulado / 3`

El promedio se mostrará con dos decimales. El total acumulado también se
mostrará con dos decimales cuando corresponda.

### 4.2 Regla de aprobación

-   Aprobado: total acumulado mayor o igual a **42,00 sobre 60**.
-   Reprobado: total acumulado menor que **42,00 sobre 60**.

Un estudiante puede aprobar con 42 puntos acumulados aunque uno de sus
parciales sea inferior a 14 puntos. No existe, según la regla confirmada
para este proyecto, una nota mínima individual por parcial.

Ejemplo:

-   Parcial 1: 10,00
-   Parcial 2: 15,00
-   Parcial 3: 17,00
-   Total: 42,00 / 60
-   Promedio: 14,00 / 20
-   Estado: APROBADO

### 4.3 Validaciones de calificaciones

-   Cada parcial debe ser numérico y estar entre 0 y 20, inclusive.
-   Admitir valores decimales.
-   Rechazar valores negativos o superiores a 20.
-   No redondear cada parcial antes de sumar.
-   Calcular el total y el promedio desde los tres valores registrados.
-   Aplicar la regla de aprobación sobre el total acumulado, no sobre
    cada parcial individual.
-   No permitir que el cliente envíe un estado o promedio arbitrario
    para reemplazar el cálculo del servidor.
-   Si faltan parciales, mostrar el registro como incompleto y no
    asignar un estado definitivo de aprobación/reprobación. El
    comportamiento de reportes para registros incompletos debe
    distinguirse claramente.
-   Definir y probar explícitamente la precisión decimal y el redondeo
    de presentación.
-   Cada parcial admite como máximo **dos decimales**. Rechazar valores
    con más decimales (no truncarlos ni redondearlos en silencio).

### 4.3.1 Precisión y tipo de dato

-   Almacenar cada parcial como `DECIMAL(5,2)` en MySQL, nunca como
    `FLOAT` o `DOUBLE`.
-   No calcular el total ni comparar contra 42 con aritmética de punto
    flotante en PHP (por ejemplo, `10.1 + 15.2 + 16.7` puede producir
    `41.999999...`). Calcular con enteros en centésimas, `bcmath` o con
    el tipo `DECIMAL` de la base de datos, mediante un único servicio de
    cálculo.
-   El total acumulado es exacto (suma de valores con dos decimales).
-   El promedio se calcula con precisión completa y se redondea a dos
    decimales **solo para presentación**.
-   El estado se determina sobre el **total exacto**, nunca sobre el
    promedio redondeado.

**Caso límite conocido:** total `41,99` → promedio `13,9966...` → se
muestra `14,00` con estado **REPROBADO**. Es correcto según la regla,
porque la aprobación se evalúa sobre el total (41,99 < 42,00). Debe
existir una prueba para este caso, y el reporte debe mostrar el total
para que la decisión sea verificable.

### 4.3.2 Parcial no registrado frente a parcial con cero

-   Un parcial no registrado se almacena como `NULL`, no como `0`.
-   `0,00` es una nota válida registrada; `NULL` significa "pendiente".
-   Si al menos un parcial es `NULL`, el registro es **INCOMPLETO**: no
    se calcula total definitivo, promedio definitivo ni estado
    APROBADO/REPROBADO.
-   En reportes, los parciales pendientes se muestran como `—` y el
    estado como `INCOMPLETO`.
-   Una matrícula sin registro en `grades` también se considera
    INCOMPLETA.

### 4.4 Persistencia de resultados

Los parciales son los datos fuente. El total, el promedio y el estado
son valores derivados.

Preferir calcular los derivados desde los parciales para evitar
inconsistencias. Si por razones de rendimiento o auditoría se decide
persistirlos, debe existir un único mecanismo centralizado que los
recalcule y pruebas que garanticen que nunca queden desactualizados.

## 5. Identificadores: distinción obligatoria

No confundir las claves primarias internas de la base de datos con los
códigos institucionales visibles en los reportes.

### 5.1 Identificador interno

Cada tabla tendrá una clave primaria técnica, por ejemplo `id`,
administrada por la base de datos.

### 5.2 ID institucional de estudiante

Cada estudiante tendrá un código institucional único denominado **ID**.
Ejemplo ilustrativo tomado del formato de referencia: `L00423704`.

Este valor:

-   No será la clave primaria de la tabla.
-   Se almacenará en un campo separado, por ejemplo `institutional_id`.
-   Tendrá restricción de unicidad.
-   Se conservará como texto, no como número, para preservar letras y
    ceros iniciales.

### 5.3 ID institucional de docente

Cada docente tendrá igualmente un código institucional único denominado
**ID**. Debe almacenarse como texto y no confundirse con el `id` interno
del usuario.

### 5.4 Código de asignatura

Cada asignatura tendrá un código propio, por ejemplo `COMP-A0G11`. Se
almacenará separado del nombre de la asignatura y tendrá una restricción
de unicidad según el alcance institucional definido.

### 5.5 NRC

El **NRC** identifica una oferta académica concreta de una asignatura.
No es el código de la asignatura ni el ID del docente.

Una oferta académica debe relacionar, como mínimo:

-   Asignatura.
-   Docente responsable.
-   Período académico.
-   NRC.
-   Aula, si se decide incluirla.
-   Campus, si se decide incluirlo.

La unicidad del NRC debe definirse con el alcance institucional
correspondiente. No asumir que es único para siempre en toda la base de
datos si su unicidad real depende del período académico. Diseñar la
restricción de forma coherente con la regla que se confirme.

## 6. Roles y autorización

El sistema tendrá inicialmente dos roles:

### 6.1 Administrador

Puede administrar la información general:

-   Usuarios y roles.
-   Estudiantes.
-   Docentes.
-   Asignaturas.
-   Períodos académicos.
-   Ofertas académicas y NRC.
-   Asignaciones docentes.
-   Matrículas.
-   Consulta de calificaciones y reportes.
-   Auditoría.
-   Apertura y cierre de períodos académicos.

-   Corrección de calificaciones.

**Corrección de calificaciones por el administrador:** el registro
ordinario de notas corresponde al docente de la oferta. El
administrador puede **corregir** una calificación (por ejemplo, cuando
el docente la ingresó mal), con estas condiciones:

-   Debe indicar un **motivo obligatorio** (texto), que se guarda en la
    auditoría.
-   Se permite en períodos `open` y `closed`. Es el único mecanismo para
    modificar notas de un período cerrado (rectificación); no se
    "reabre" el período.
-   Pasa por el mismo servicio de calificaciones: mismas validaciones,
    mismo cálculo, mismo bloqueo optimista y misma auditoría.
-   La auditoría distingue el tipo de operación: `registro` (docente)
    o `correccion` (administrador).

### 6.2 Docente

Puede:

-   Consultar únicamente las ofertas académicas que tiene asignadas.
-   Consultar estudiantes matriculados en esas ofertas.
-   Registrar y actualizar los tres parciales de los estudiantes de sus
    ofertas asignadas.
-   Consultar reportes correspondientes a sus ofertas.

No puede administrar usuarios, cambiar asignaciones docentes ni
modificar ofertas que no le pertenecen.

### 6.3 Aplicación de permisos

-   La autorización debe verificarse en el servidor.
-   Usar Policies y/o middleware de Laravel.
-   No considerar suficiente ocultar botones o enlaces en Blade.
-   Verificar que el docente autenticado sea responsable de la oferta
    relacionada con la matrícula.
-   Probar explícitamente intentos de acceso a registros ajenos.
-   El rol se almacena en una columna `role` de `users` con valores
    `admin` y `teacher`, respaldada por un `enum` de PHP. No instalar
    paquetes de roles/permisos (por ejemplo, Spatie) para solo dos
    roles.
-   Un usuario con estado inactivo no puede iniciar sesión ni operar,
    aunque tenga una sesión abierta previamente.
-   La pertenencia de una oferta se evalúa con el docente **actual** de
    la oferta. Si el administrador reasigna la oferta, el docente
    anterior pierde el acceso a ella y el nuevo lo adquiere. La
    auditoría conserva quién registró cada cambio.

## 7. Modelo conceptual de datos

El diseño físico final se validará antes de crear migraciones. Como
mínimo, considerar las siguientes entidades:

### `users`

Usuarios que pueden iniciar sesión: administradores y docentes. No
existe tabla `teachers`; un docente es un usuario con rol `teacher`.

Campos: identificador interno, nombre completo, correo único,
contraseña hasheada, estado (`is_active`), rol (`role`: `admin` |
`teacher`) e ID institucional (`institutional_id`, texto, `NULL`
permitido, `UNIQUE`).

Restricciones:

-   `UNIQUE(institutional_id)`. MySQL y PostgreSQL permiten varios
    `NULL` en un índice único, por eso los administradores pueden no
    tener ID.
-   `CHECK (role <> 'teacher' OR institutional_id IS NOT NULL)`: todo
    docente debe tener ID institucional.
-   `course_offerings.teacher_id` es FK a `users.id`. Que el usuario
    asignado tenga rol `teacher` se valida en el Form Request de la
    oferta (la base de datos no puede expresarlo con una FK simple).

**Por qué los estudiantes no están en `users`:** en este alcance los
estudiantes no inician sesión. Meterlos en `users` obligaría a tener
correo y contraseña ficticios o columnas nulas de autenticación, y
mezclaría datos académicos con datos de acceso. Se mantiene la tabla
`students`. Si en el futuro los estudiantes debieran iniciar sesión, se
añadiría un `user_id` opcional en `students`.

### `students`

Estudiantes. Incluir identificador interno, ID institucional único,
nombres, apellidos y correo (opcional). No almacenar otros datos
personales.

### `subjects`

Asignaturas. Incluir identificador interno, código único y nombre.

### `academic_periods`

Períodos académicos. Incluir identificador, nombre o código del período,
fechas relevantes y estado.

El formato del período debe poder representar un período como
`202451 - PREGRADO S-II OCT 24 - MAR 25`, sin depender de una cadena
difícil de consultar.

Campos: `code` (único, ej. `202451`), `description` (ej.
`PREGRADO S-II OCT 24 - MAR 25`), `start_date`, `end_date` y `status`.
La etiqueta completa se compone al mostrarla. Se crean manualmente.

Estados del período:

  Estado         Matrículas   Notas (docente)   Corrección (admin)
  ------------- ------------ ----------------- --------------------
  `planned`      Permitidas   No permitido      No permitida
  `open`         Permitidas   Permitido         Permitida con motivo
  `closed`       Bloqueadas   Bloqueado         Permitida con motivo

Solo el administrador cambia el estado. La reapertura de un período
cerrado queda fuera del alcance.

### `course_offerings`

Ofertas académicas. Relacionan asignatura, período académico, docente y
NRC. Incluyen aula y campus como campos **opcionales informativos**.

Restricción: `UNIQUE(academic_period_id, nrc)`. El NRC es único dentro
de un período y puede repetirse en períodos distintos. El NRC se
almacena como texto.

### `enrollments`

Matrículas. Relacionan un estudiante con una oferta académica. Impedir
que el mismo estudiante se matricule dos veces en la misma oferta.

Restricción: `UNIQUE(student_id, course_offering_id)`.

La anulación de matrículas queda fuera del alcance. Una matrícula con
calificaciones registradas no puede eliminarse.

### `grades`

Calificaciones de una matrícula. Contienen únicamente los tres parciales
definidos para el proyecto (`partial_1`, `partial_2`, `partial_3`,
`DECIMAL(5,2) NULL`). Debe existir como máximo un registro de
calificaciones por matrícula.

Restricciones: `UNIQUE(enrollment_id)` y `CHECK` de rango 0–20 por
parcial como refuerzo de la validación de la aplicación (MySQL 8.0.16+
y PostgreSQL los aplican).

### Política de borrado

-   Las claves foráneas usan `ON DELETE RESTRICT`.
-   No se puede eliminar un estudiante, docente, asignatura, período u
    oferta que tenga registros dependientes.
-   Para retirar un registro en uso se marca como inactivo en lugar de
    borrarlo.
-   Mostrar un mensaje comprensible cuando un borrado sea rechazado.

### `audit_logs` o mecanismo equivalente

Registrar las operaciones relevantes sobre calificaciones: usuario que
realizó el cambio, fecha y hora, matrícula afectada y valores
anteriores/nuevos. Evitar registrar contraseñas, tokens u otros
secretos.

Campos: usuario, fecha y hora, matrícula, tipo de operación
(`registro` | `correccion`), valores anteriores, valores nuevos y motivo
(obligatorio en correcciones del administrador).

El registro de auditoría se crea en el **servicio de calificaciones**,
dentro de la **misma transacción** que guarda la nota. Si falla la
auditoría, no se guarda la nota. No depender únicamente de observers
de Eloquent, que se omiten con actualizaciones masivas. La auditoría
solo admite inserciones; no se edita ni se borra desde la aplicación.

### Relaciones conceptuales

-   Un período académico tiene muchas ofertas.
-   Una asignatura puede aparecer en muchas ofertas y períodos.
-   Un docente puede tener muchas ofertas asignadas.
-   Una oferta pertenece a una asignatura, un período y un docente.
-   Una oferta tiene muchas matrículas.
-   Un estudiante puede tener muchas matrículas.
-   Una matrícula pertenece a un estudiante y a una oferta.
-   Una matrícula tiene cero o un registro de calificaciones.
-   Un registro de calificaciones contiene tres parciales.

## 8. Reglas de integridad y negocio

Implementar y probar, como mínimo:

1.  El ID institucional del estudiante no se puede duplicar.
2.  El ID institucional del docente no se puede duplicar.
3.  El código de asignatura no se puede duplicar dentro del alcance de
    unicidad definido.
4.  Una matrícula debe referirse a un estudiante y una oferta
    existentes.
5.  No debe existir una matrícula duplicada para el mismo estudiante y
    oferta.
6.  Una calificación debe pertenecer a una matrícula válida.
7.  Una matrícula solo puede tener un conjunto de tres parciales.
8.  Los parciales deben respetar el rango de 0 a 20.
9.  El docente solo puede modificar calificaciones de sus propias
    ofertas.
10. El administrador puede consultar y administrar conforme a sus
    permisos.
11. El estado de aprobación se determina con el total acumulado de
    42/60.
12. No se debe aceptar una calificación para una matrícula de otra
    oferta.
13. Las modificaciones de calificaciones deben dejar evidencia de
    auditoría.
14. Los períodos cerrados no permiten que el docente modifique notas.
    La única modificación posible es la corrección del administrador
    con motivo obligatorio (sección 6.1). No existe reapertura de
    períodos.
15. La base de datos debe reforzar las restricciones de unicidad
    importantes; no depender únicamente de validaciones previas de la
    aplicación.
16. El NRC es único dentro de un período académico.
17. No se permiten matrículas en períodos cerrados.
18. El docente no registra notas en períodos `planned` ni `closed`.
19. Un parcial `NULL` (pendiente) es distinto de un parcial `0,00`.
20. No se eliminan registros con dependencias (`ON DELETE RESTRICT`).
21. Un usuario inactivo no puede operar en el sistema.

## 9. Reporte de calificaciones

El sistema debe producir un reporte inspirado en el formato de
referencia entregado.

### Encabezado del reporte

Considerar:

-   Universidad de las Fuerzas Armadas - ESPE.
-   Reporte de calificaciones de estudiantes.
-   Período académico.
-   Materia.
-   Código de asignatura.
-   NRC.
-   Aula.
-   Campus.
-   Docente.
-   ID institucional del docente.

### Detalle de estudiantes

Por cada estudiante mostrar:

-   ID institucional.
-   Apellidos y nombres.
-   Primer parcial (1P).
-   Segundo parcial (2P).
-   Tercer parcial (3P).
-   Promedio sobre 20, con dos decimales.
-   Total acumulado sobre 60.
-   Estado académico.

El reporte debe poder filtrarse por período, oferta/NRC y asignatura. El
diseño debe ser legible en pantalla y apto para impresión.

Formato de salida: vista HTML imprimible con estilos `@media print`. La
exportación a PDF se obtiene con "Imprimir → Guardar como PDF" del
navegador. No se añaden librerías de PDF ni Excel en el alcance
inicial.

Registros incompletos: se listan en el reporte con los parciales
pendientes como `—` y estado `INCOMPLETO`. El pie del reporte muestra
el resumen: aprobados, reprobados e incompletos.

Los datos de demostración usan IDs, nombres y NRC **ficticios**. No
usar datos de estudiantes o docentes reales.

No copiar logotipos oficiales ni afirmar que el reporte es un documento
oficial emitido por ESPE sin autorización. Presentarlo como reporte
generado por el sistema de demostración.

## 10. Arquitectura y tecnologías

### 10.1 Stack acordado

-   Backend: Laravel 13.
-   Lenguaje: PHP 8.4.
-   Base de datos: **PostgreSQL en desarrollo local** y **MySQL 8 en
    producción** (cPanel).
-   Frontend: Blade.
-   Estilos y componentes: Bootstrap 5.
-   Despliegue inicial: hosting cPanel.
-   Acceso de mantenimiento: SSH y Composer disponibles.
-   Control de versiones: Git.
-   Pruebas: PHPUnit o el conjunto de pruebas soportado por la versión
    elegida de Laravel.

Antes de instalar dependencias, comprobar compatibilidad real entre la
versión de Laravel, PHP disponible en cPanel, extensiones PHP y
Composer. No asumir que el hosting está configurado correctamente solo
porque ofrece PHP 8.4.

**Entorno local detectado (2026-11-04):** PHP 8.5.4 CLI; Composer no
instalado; PostgreSQL disponible; MySQL no instalado. Como producción
usa PHP 8.4:

-   Fijar `"php": "^8.4"` en `composer.json` y configurar
    `config.platform.php = 8.4` para que Composer resuelva dependencias
    compatibles con el hosting.
-   No usar características exclusivas de PHP 8.5.
-   Verificar que estén instaladas las extensiones `pdo_pgsql` (local) y
    `pdo_mysql` (producción).

### 10.1.1 Compatibilidad PostgreSQL (local) / MySQL (producción)

El código debe funcionar igual en ambos motores. Reglas:

-   Usar solo Eloquent y el Schema Builder de Laravel. No escribir SQL
    crudo específico de un motor.
-   **Columnas de estado y rol:** `string` + `CHECK` + `enum` de PHP. No
    usar `$table->enum()`, que se implementa distinto en cada motor.
-   **Mayúsculas en códigos únicos:** MySQL (collation `*_ci`) trata
    `COMP-A0G11` y `comp-a0g11` como iguales; PostgreSQL no. Normalizar
    en el servidor (`trim` + mayúsculas) los ID institucionales,
    códigos de asignatura, NRC y códigos de período antes de validar y
    guardar. Los correos se guardan en minúsculas.
-   **Búsquedas de texto:** `LIKE` distingue mayúsculas en PostgreSQL y
    no en MySQL. Para filtros de texto, comparar sobre `LOWER(columna)`
    con el término en minúsculas.
-   **Duplicados:** capturar
    `Illuminate\Database\UniqueConstraintViolationException`, que
    Laravel lanza en ambos motores, en lugar de revisar códigos de
    error propios de cada uno (1062 en MySQL, 23505 en PostgreSQL).
-   **Decimales:** `DECIMAL(5,2)` se comporta igual en ambos.
-   **Aislamiento:** los motores tienen valores por defecto distintos
    (ver sección 12.1). La corrección no debe depender del nivel de
    aislamiento, sino de restricciones únicas y bloqueos explícitos.
-   **Pruebas en ambos motores:** la suite se ejecuta localmente en
    PostgreSQL y, **antes del despliegue**, también en MySQL 8 mediante
    GitHub Actions (servicio MySQL en el flujo de CI). Justificación:
    es la forma de comprobar el motor de producción sin instalar MySQL
    localmente y sin ejecutar pruebas en el servidor de producción.
    No se usa SQLite para pruebas, porque no refleja `CHECK`, `DECIMAL`
    ni los bloqueos de los motores reales.

### 10.2 Estilo arquitectónico

Usar una aplicación monolítica modular con MVC de Laravel.

Responsabilidades:

-   **Modelos:** relaciones y acceso al dominio/persistencia.
-   **Controladores:** recibir solicitudes, coordinar el caso de uso y
    devolver respuestas.
-   **Form Requests:** validación de entrada.
-   **Policies y middleware:** autenticación y autorización.
-   **Servicios:** reglas de negocio que merezcan centralización, como
    registro de calificaciones y cálculo académico.
-   **Blade:** presentación, sin reglas críticas de negocio.
-   **Migraciones:** definición versionada del esquema.
-   **Seeders y factories:** datos de prueba y configuración inicial
    controlada.

No introducir Repository Pattern, CQRS, DDD táctico completo o
microservicios por moda. Añadir abstracciones solo cuando resuelvan una
necesidad concreta y explicable.

### 10.3 Organización

Seguir la estructura estándar de Laravel siempre que sea posible.
Agrupar clases por responsabilidad y mantener nombres consistentes.
Evitar controladores extensos, modelos con responsabilidades excesivas y
lógica de negocio duplicada.

## 11. Seguridad

-   Usar el sistema de autenticación seguro de Laravel.
-   Hashear contraseñas con los mecanismos del framework.
-   Proteger formularios con CSRF.
-   Validar y normalizar entradas en el servidor.
-   Aplicar autorización en cada operación protegida.
-   Evitar asignación masiva insegura; definir explícitamente los campos
    permitidos.
-   Proteger archivos de entorno y credenciales.
-   No exponer `.env`, código fuente, archivos de configuración ni
    respaldos en el directorio público.
-   Usar HTTPS en producción.
-   No mostrar trazas ni errores detallados en producción.
-   Mantener dependencias actualizadas y revisar avisos de seguridad.
-   Limitar la información personal mostrada a la estrictamente
    necesaria.
-   Limitar los intentos de inicio de sesión (rate limiting de Laravel).
-   Escapar toda salida en Blade con `{{ }}`; no usar `{!! !!}` con datos
    del usuario (prevención de XSS).
-   Usar Eloquent o consultas parametrizadas; no concatenar entradas en
    SQL (prevención de inyección SQL).
-   Regenerar la sesión al iniciar sesión e invalidarla al cerrarla.
-   No hay recuperación de contraseña por correo en el alcance inicial.
    El administrador restablece contraseñas.

## 12. Transacciones, concurrencia e integridad

-   Usar transacciones para operaciones que deban completarse como una
    unidad.
-   Crear restricciones únicas en la base de datos para prevenir
    matrículas duplicadas y duplicidad de calificaciones.
-   Manejar correctamente errores de integridad y mostrarlos de forma
    comprensible.
-   No confiar en una consulta previa del tipo "si no existe, insertar"
    como única protección frente a solicitudes simultáneas.
-   Evaluar bloqueo de filas cuando una operación concurrente pueda
    producir una inconsistencia.
-   Registrar cambios de notas de forma coherente con la operación que
    los realiza.

### 12.1 Escenarios de concurrencia y su solución

  Escenario                               Solución
  --------------------------------------- ---------------------------------------
  Dos solicitudes matriculan al mismo     `UNIQUE(student_id, course_offering_id)`
  estudiante en la misma oferta           y captura de la excepción de integridad
  Dos solicitudes crean el registro de    `UNIQUE(enrollment_id)` y
  notas de la misma matrícula             `updateOrCreate` dentro de transacción
  Dos usuarios editan la misma nota       Bloqueo optimista (ver abajo)
  (actualización perdida)
  Se cierra el período mientras un        Leer el estado del período con
  docente guarda notas                    `lockForUpdate()` dentro de la
                                          transacción de guardado

**Bloqueo optimista para notas:** el formulario envía la marca
`updated_at` (o un campo `version`) que tenía la nota al abrirse. Al
guardar, dentro de la transacción y con `lockForUpdate()`, se compara
con el valor actual. Si cambió, se rechaza el guardado y se informa al
usuario que la nota fue modificada por otra persona, en lugar de
sobrescribirla en silencio.

Niveles de aislamiento por defecto: MySQL/InnoDB usa `REPEATABLE READ`
y PostgreSQL usa `READ COMMITTED`. El diseño no depende de esa
diferencia: la integridad se garantiza con restricciones `UNIQUE`,
`lockForUpdate()` (`SELECT ... FOR UPDATE`, soportado por ambos) y el
bloqueo optimista. Saber explicarlo ante el comité.

## 13. Pruebas y calidad

Preparar pruebas para:

### Autenticación y autorización

-   Inicio y cierre de sesión.
-   Acceso de administrador.
-   Acceso de docente.
-   Docente intentando acceder a una oferta ajena.
-   Usuario inactivo o sin permisos.

### Datos académicos

-   ID institucional duplicado.
-   Código de asignatura duplicado.
-   NRC según la regla de unicidad definida.
-   Matrícula duplicada.
-   Relaciones inexistentes.

### Calificaciones

-   Valores límite 0 y 20.
-   Valores negativos y mayores a 20.
-   Decimales.
-   Total y promedio calculados correctamente.
-   Aprobación con 42 puntos.
-   Reprobación con menos de 42 puntos.
-   Aprobación aunque un parcial individual sea inferior a 14.
-   Calificaciones incompletas.
-   Docente modificando una oferta ajena.
-   Auditoría de cambios.
-   Total 41,99: promedio mostrado 14,00 y estado REPROBADO.
-   Valores con más de dos decimales rechazados.
-   Parcial `NULL` frente a parcial `0,00`.
-   Suma exacta con decimales (ej. 10,10 + 15,20 + 16,70 = 42,00 →
    APROBADO).
-   Cliente enviando promedio o estado en la solicitud: se ignoran.
-   Registro de notas del docente bloqueado en períodos `planned` y
    `closed`.
-   Conflicto de bloqueo optimista (actualización perdida).
-   Auditoría creada en la misma transacción que la nota.

### Integridad y borrado

-   NRC repetido en el mismo período: rechazado.
-   NRC repetido en otro período: permitido.
-   Matrícula en período cerrado: rechazada.
-   Borrado de estudiante u oferta con dependencias: rechazado.
-   Docente reasignado pierde acceso a la oferta anterior.
-   Usuario docente sin ID institucional: rechazado.
-   Asignar a una oferta un usuario que no es docente: rechazado.

### Corrección por el administrador

-   Corrección con motivo: aceptada y auditada como `correccion`.
-   Corrección sin motivo: rechazada.
-   Corrección en período cerrado: aceptada para el administrador.
-   Docente modificando nota en período cerrado: rechazado.

### Calidad general

-   Validación de formularios.
-   Persistencia en PostgreSQL (local) y MySQL 8 (CI antes del
    despliegue).
-   Códigos únicos que solo difieren en mayúsculas: tratados como
    duplicados en ambos motores.
-   Consultas y filtros de reportes.
-   Manejo de errores.
-   Verificación de migraciones desde una base limpia.

No declarar el proyecto listo solo porque la pantalla funciona
manualmente. Ejecutar y documentar las pruebas relevantes.

### Entregables de evidencia

-   Repositorio Git con commits pequeños y descriptivos por
    funcionalidad (no un único commit final).
-   `README.md`: propósito, requisitos, instalación, variables de
    entorno, comandos de prueba, usuarios de demostración y decisiones
    técnicas.
-   `.env.example` sin secretos.
-   Diagrama Entidad-Relación de la base de datos.
-   Evidencia de pruebas: salida de `php artisan test`.
-   Seeders con datos de demostración ficticios y controlados.

## 14. Metodología de trabajo: Kanban

Usar Kanban por ser un proyecto pequeño y con tiempo acotado. No simular
ceremonias Scrum que no aporten valor.

El tablero debe existir y ser visible (por ejemplo, GitHub Projects o
Trello), con las tareas, sus criterios de aceptación y su estado. Cada
commit debe poder relacionarse con una tarea del tablero. Usar límites
de trabajo en progreso (WIP): una tarea "En progreso" a la vez.

Columnas sugeridas:

-   Backlog.
-   Por hacer.
-   En progreso.
-   En revisión.
-   En pruebas.
-   Terminado.

Cada tarea debe tener:

-   Descripción.
-   Criterios de aceptación.
-   Dependencias, si existen.
-   Evidencia de prueba cuando corresponda.

### Incrementos propuestos

**Incremento 1: Base del sistema** - Proyecto Laravel y configuración. -
Conexión PostgreSQL (local) y CI con MySQL. - Estructura de datos inicial. - Autenticación. - Roles
y permisos base.

**Incremento 2: Administración académica** - Estudiantes. - Docentes y
usuarios. - Asignaturas. - Períodos. - Ofertas académicas y NRC. -
Asignación de docentes.

**Incremento 3: Matrículas y calificaciones** - Matrículas. - Registro
de 1P, 2P y 3P. - Cálculo de total y promedio. - Estado de aprobación. -
Restricciones de acceso del docente.

**Incremento 4: Reportes y cierre** - Reporte de calificaciones. -
Auditoría. - Pruebas funcionales y de autorización. - Revisión de
seguridad. - Despliegue en cPanel. - Preparación de la demostración.

Cada incremento debe terminar con una versión ejecutable y verificable.

## 15. Despliegue en cPanel

El despliegue objetivo es cPanel con SSH, Composer, PHP 8.4 y MySQL 8.

### Requisitos a verificar antes del despliegue

-   Versión PHP seleccionable para el dominio.
-   Extensiones PHP requeridas por Laravel.
-   Versión de Composer.
-   Acceso SSH.
-   Acceso a MySQL 8.
-   Posibilidad de configurar el Document Root del dominio o subdominio
    a la carpeta `public` del proyecto.
-   Permisos de escritura para `storage` y `bootstrap/cache`.
-   Certificado SSL.

### Principio de seguridad del despliegue

El directorio público del sitio debe apuntar a la carpeta `public` de
Laravel. El resto del proyecto debe permanecer fuera del directorio
públicamente accesible.

No colocar todo el proyecto dentro de `public_html` dejando accesibles
`.env`, `vendor`, `app` o archivos internos.

### Lista de despliegue

1.  Preparar el entorno y comprobar requisitos.
2.  Crear base de datos y usuario MySQL con privilegios mínimos
    necesarios.
3.  Subir el código mediante Git o un procedimiento controlado.
4.  Configurar variables de entorno de producción.
5.  Instalar dependencias de producción con Composer.
6.  Configurar permisos de directorios requeridos.
7.  Ejecutar migraciones después de respaldo y autorización.
8.  Ejecutar optimizaciones de Laravel apropiadas para producción.
9.  Configurar HTTPS.
10. Verificar autenticación, permisos, matrículas, calificaciones y
    reportes.
11. Verificar logs y manejo de errores.
12. Configurar y comprobar respaldos de base de datos y archivos.

No ejecutar `migrate:fresh`, `db:wipe` ni comandos destructivos en
producción.

## 16. Demostración técnica de 10 minutos

La demostración debe recorrer un caso completo y no depender de datos
improvisados.

Secuencia sugerida:

1.  Presentar el propósito y entrar al sistema.
2.  Mostrar un período académico.
3.  Mostrar una asignatura y su código.
4.  Mostrar una oferta identificada por NRC y su docente.
5.  Mostrar un estudiante con su ID institucional.
6.  Matricular al estudiante en la oferta, si el flujo de demostración
    lo permite.
7.  Registrar tres parciales.
8.  Mostrar cálculo automático del promedio y total.
9.  Mostrar el estado aprobado/reprobado.
10. Intentar una operación no permitida para demostrar autorización, si
    el tiempo lo permite.
11. Mostrar el reporte con los datos académicos.

Preparar previamente datos semilla controlados y una ruta de
demostración ensayada. No depender de una conexión externa o de
registros que deban crearse desde cero durante la exposición.

### Plan de contingencia

-   Tener la aplicación funcionando en el entorno local además de
    cPanel.
-   Tener un video grabado de la demostración completa como respaldo.
-   Tener abiertas de antemano las pestañas necesarias (sistema, tablero
    Kanban, repositorio, diagrama ER, salida de pruebas).
-   Verificar audio, cámara y compartir pantalla en Google Meet antes de
    la hora.
-   Tener preparados los datos para el caso límite (42,00 aprobado,
    41,99 reprobado, registro incompleto).

## 17. Preparación para preguntas del comité

Poder explicar con claridad:

-   Por qué se eligió Laravel y PHP.
-   Por qué se eligió MySQL.
-   Por qué se utiliza una arquitectura monolítica modular.
-   Qué responsabilidad tiene cada capa.
-   Cómo se representa la relación entre asignatura, NRC, docente y
    período.
-   Por qué el ID institucional no es la clave primaria.
-   Cómo se evita una matrícula duplicada.
-   Cómo se controla el acceso del docente.
-   Cómo se calcula el promedio y el total.
-   Cómo se garantiza la regla de aprobación.
-   Cómo se evita que dos solicitudes concurrentes generen duplicados.
-   Cómo se auditan los cambios de calificaciones.
-   Cómo se prueban las reglas de negocio.
-   Cómo se protege el proyecto en cPanel.
-   Qué cambiaría si el sistema creciera significativamente.
-   En qué forma normal está la base de datos y por qué.
-   Qué índices existen y para qué consultas sirven.
-   Qué es una transacción y qué significa ACID.
-   Qué nivel de aislamiento usa InnoDB y qué es una actualización
    perdida.
-   Diferencia entre bloqueo optimista y pesimista.
-   Cómo se previenen inyección SQL, XSS y CSRF.
-   Qué es el problema N+1 y cómo se evita con carga anticipada
    (`with()`).
-   Por qué no se almacenan promedio ni estado.
-   Por qué el estado se decide sobre el total y no sobre el promedio
    redondeado.
-   Por qué `NULL` y `0` son distintos en las calificaciones.
-   Por qué `DECIMAL` y no `FLOAT`.
-   Por qué el docente es un `user` con rol y el estudiante tiene su
    propia tabla.
-   Por qué PostgreSQL en local y MySQL en producción, y cómo se
    garantiza que el sistema funcione igual en ambos.
-   Cómo se corrige una nota mal ingresada y cómo queda registrado.
-   Por qué Kanban y no Scrum.
-   Cómo se protegería el sistema con 50 000 estudiantes (índices,
    paginación, caché, réplicas de lectura).

Las respuestas deben explicar ventajas, límites y alternativas, no
limitarse a mencionar nombres de tecnologías.

## 18. Decisiones confirmadas

Confirmadas por el responsable el 2026-11-04. Cualquier cambio debe
aprobarse y reflejarse aquí y en la sección afectada.

  \#   Tema                         Decisión
  ---- ---------------------------- ------------------------------------------
  1    Unicidad del NRC             Único por período:
                                    `UNIQUE(academic_period_id, nrc)`
  2    Cuentas del docente          Docente = `user` con rol `teacher` e
                                    `institutional_id` único; sin tabla
                                    `teachers`
  3    Datos del estudiante         ID institucional, nombres, apellidos;
                                    correo opcional
  4    Aula y campus                Opcionales, informativos
  5    Períodos                     Creación manual: código (`202451`),
                                    descripción, fechas y estado
  6    Notas tras el cierre         El docente no modifica; el admin corrige
                                    con motivo obligatorio; sin reapertura
  7    Formato del reporte          Vista HTML imprimible; PDF desde el
                                    navegador
  8    Fecha de publicación         No se registra
  9    Redondeo                     Precisión completa en el cálculo;
                                    2 decimales solo en presentación
  10   Validación de códigos        Texto único sin patrón; parciales con
                                    máximo 2 decimales
  11   Admin y notas                El administrador puede corregir notas
                                    con motivo obligatorio y auditado
  12   Estados del período          `planned`, `open`, `closed`
  13   Borrado                      `ON DELETE RESTRICT`; inactivar en lugar
                                    de borrar
  14   Anulación de matrícula       Fuera del alcance
  15   Roles                        Columna `role` en `users`; sin paquetes
                                    externos
  16   Concurrencia en notas        Bloqueo optimista + `lockForUpdate()`
  17   Motor de base de datos       PostgreSQL en local; MySQL 8 en
                                    producción; suite también en MySQL vía CI
  18   Estudiantes y `users`        Tabla `students` separada; los
                                    estudiantes no inician sesión
  19   Códigos institucionales      Normalizados (`trim` + mayúsculas)
                                    antes de validar y guardar

### 18.1 Decisiones técnicas de implementación

Tomadas durante la implementación según la regla 2.13 (mejor solución
técnica, justificada y registrada). No cambian reglas académicas.

  \#   Tema                         Decisión y justificación
  ---- ---------------------------- ------------------------------------------
  20   Restricciones `CHECK`        El Schema Builder de Laravel 13 no
                                    ofrece `check()`. Se añaden con
                                    `ALTER TABLE ... ADD CONSTRAINT ...
                                    CHECK (...)` en SQL estándar, idéntico
                                    en PostgreSQL y MySQL 8.0.16+. Es la
                                    única excepción a "solo Schema Builder".
  21   Bloqueo optimista            Columna entera `grades.version` (no
                                    `updated_at`): `updated_at` tiene
                                    resolución de segundos y no detecta dos
                                    guardados en el mismo segundo.
  22   Cálculo de notas             Enteros en centésimas dentro de
                                    `GradeCalculator`; no requiere `bcmath`
                                    en el hosting. Presentación con coma
                                    decimal (`14,00`).
  23   Frontend                     Bootstrap 5.3.8 copiado en
                                    `public/vendor/bootstrap`; sin Vite,
                                    Tailwind ni Node en cPanel y sin CDN
                                    (la demo no depende de internet).
  24   Autenticación                Controlador y Form Request propios sobre
                                    `Auth` de Laravel, sin Breeze/Fortify:
                                    solo se necesita login/logout. Límite:
                                    5 intentos por correo+IP, bloqueo 60 s.
  25   Tablas omitidas              Sin `password_reset_tokens` (no hay
                                    recuperación por correo) ni `jobs`
                                    (`QUEUE_CONNECTION=sync`, sin colas).
  26   Base de pruebas              PostgreSQL `espe_test` en local;
                                    GitHub Actions ejecuta la suite en
                                    PostgreSQL 16 y MySQL 8.0 (matriz).
  27   Carga diferida               `Model::preventLazyLoading()` fuera de
                                    producción: un N+1 falla en pruebas.
  28   Bloqueos al guardar notas    Oferta y período con bloqueo compartido
                                    (`sharedLock()`, `FOR SHARE`); fila de
                                    `grades` con `lockForUpdate()`. Cerrar el
                                    período o reasignar la oferta exige
                                    bloqueo exclusivo y espera al guardado;
                                    varios docentes pueden guardar a la vez.
  29   Planilla del docente         Un formulario por fila (atributo `form`)
                                    con su propia versión: un conflicto solo
                                    afecta a esa matrícula. Se acepta coma o
                                    punto decimal.
  30   Quitar matrícula             Solo sin notas y con período no cerrado,
                                    para corregir errores de digitación. No
                                    es la "anulación" (fuera del alcance).
  31   Datos de demostración        `DemoSeeder` registra las notas mediante
                                    `GradeService` (auditoría real); la
                                    contraseña viene de `DEMO_PASSWORD`.
  32   Cabeceras de seguridad       `X-Frame-Options`, `nosniff`,
                                    `Referrer-Policy` y HSTS bajo HTTPS;
                                    páginas de error propias, sin trazas.

## 19. Criterios de aceptación global

El sistema se considerará listo para la demostración cuando:

-   Los roles de administrador y docente funcionen.
-   El docente solo pueda operar sobre sus ofertas asignadas.
-   Se gestionen estudiantes, docentes, asignaturas, períodos y NRC.
-   Se registren matrículas sin duplicidad.
-   Se ingresen exactamente tres parciales de 0 a 20.
-   El total acumulado y el promedio se calculen correctamente.
-   La aprobación se determine con 42/60 o más.
-   El reporte muestre ID institucional, asignatura, código, NRC,
    docente, parciales, promedio, total y estado.
-   Los cambios de calificaciones queden auditados.
-   Las pruebas principales estén ejecutadas y documentadas.
-   La aplicación funcione en el entorno cPanel objetivo con HTTPS y
    configuración segura.
-   El responsable pueda explicar y justificar las decisiones técnicas.

------------------------------------------------------------------------

## 20. Regla final de alcance

Este proyecto debe demostrar capacidad profesional de análisis, diseño,
programación, validación, seguridad, pruebas y despliegue.

**No sacrificar integridad académica ni calidad técnica por añadir
funcionalidades.** Es preferible un sistema acotado, probado y bien
explicado que una aplicación extensa con reglas ambiguas o
inconsistentes.

Antes de ampliar el alcance, confirmar la necesidad con el responsable
del proyecto.
