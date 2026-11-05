# Rediseño UI/UX — Sistema de Gestión Académica

> Versión revisada del prompt original (ver historial de Git). Cambios principales:
> sin logo oficial de la ESPE (decisión del responsable), sin jQuery ni bibliotecas
> de diálogos o notificaciones (Bootstrap 5 ya lo cubre), sin cálculo de notas en JavaScript,
> trabajo en una rama propia y verificación explícita de pruebas y del despliegue.

## 0. Contexto y límites (leer primero)

- El documento rector es `CLAUDE.md`. Léelo completo antes de empezar; prevalece sobre este archivo.
- Stack actual: Laravel 13, PHP ^8.4, Blade, Bootstrap **5.3.8** local en `public/vendor/bootstrap`
  (sin Vite, Node ni CDN; decisión 23). Producción: cPanel con la rama `deploy`.
- **Solo cambia la presentación.** No modifiques rutas, controladores, modelos, Form Requests,
  Policies, servicios, migraciones ni reglas académicas. Si algo visual exige tocar un
  controlador (por ejemplo, un dato para el dashboard), debe ser solo lectura, con `with()` o
  `withCount()`, sin N+1 y con prueba.
- Trabaja en la rama **`ui-redesign`**, creada desde `main`, con commits pequeños por módulo.
  No se fusiona a `main` ni a `deploy` hasta que las pruebas pasen y el responsable lo apruebe.
  La versión desplegada actual es la de respaldo para la entrevista.
- Registra cada decisión de diseño relevante en `CLAUDE.md` §18.1 (a partir del número 35).

## 1. Auditoría breve (máx. 10 líneas en tu primera respuesta)

Revisa `resources/views/**`, el layout `layouts/app.blade.php`, los parciales en `partials/`
y el JavaScript inline del layout. Enumera los problemas visuales y de usabilidad que
corregirás, y empieza a implementar sin esperar aprobación.

## 2. Dependencias: mínimas y locales

| Necesidad | Solución | Por qué |
|---|---|---|
| Estilos y componentes | Bootstrap 5.3.8 (ya instalado) | No duplicar ni actualizar |
| Iconos | **Bootstrap Icons 1.13.x**, copiado a `public/vendor/bootstrap-icons` (CSS + fuentes woff/woff2 + LICENSE) | Única dependencia nueva |
| Confirmaciones | **Un modal Bootstrap reutilizable** que sustituye `data-confirm` | jQuery Confirm no se mantiene desde 2019 |
| Notificaciones | **Bootstrap Toast** alimentado por los mensajes flash de Laravel | Toastr y Noty están abandonados; Notyf sobra |
| Validación en el cliente | Validación nativa de HTML5 (`required`, `maxlength`, `type`, `pattern`) + estilos de Bootstrap (`.is-invalid`, `.was-validated`) + JS propio y pequeño | Sin jQuery ni jquery-validation |
| Tipografía | Pila de fuentes del sistema (`system-ui, "Segoe UI", Roboto, …`) | Sin Google Fonts: la demo no depende de internet |

**No** añadas jQuery, SweetAlert2, Toastr, Noty, jquery-validation, React, Vue ni Tailwind.
Copia los archivos de Bootstrap Icons con `npm pack bootstrap-icons@1.13` en una carpeta
temporal fuera del proyecto, igual que se hizo con Bootstrap. No añadas `package.json`.

Todo el JavaScript va en **un archivo propio**, `public/js/app.js`, cargado con `defer`. Sin
scripts inline (deja preparado el terreno para una CSP). Todo el CSS va en `public/css/app.css`,
organizado con variables CSS. Cárgalos con `asset()` y un parámetro de versión para invalidar la caché.

## 3. Sistema de diseño

Define en `public/css/app.css` las variables y sobrescribe las de Bootstrap (`--bs-primary`, etc.):

- **Paleta institucional neutra** (no la del logo de ESPE):
  - Azul marino `#0B2545` (sidebar), azul `#13315C` / `#1D4E89` (primario), acento `#2A7DE1` (foco, enlaces).
  - Grises neutros `#F4F6FA` (fondo), `#E3E8EF` (bordes), `#5B6577` (texto secundario).
  - Semánticos: éxito `#1E7B4F`, advertencia `#B7791F`, peligro `#B42318`, información `#1D4E89`.
  - Contraste mínimo AA (4,5:1) en texto.
- Escala tipográfica (títulos de página, de tarjeta y texto), espaciado basado en 4 px,
  radios de 8 px en tarjetas y 6 px en controles, y una sola sombra suave.
- Estados de botón (hover, foco visible, deshabilitado, cargando con spinner).
- Estilo único para tablas, tarjetas, badges, modales y toasts.
- `prefers-reduced-motion`: sin animaciones para quien lo pida.

## 4. Identidad y textos (decisión del responsable: sin logo oficial)

- **No** uses `public/espe.jpeg` ni ningún escudo o logotipo de la ESPE. No se versiona ni se despliega.
- Marca del sistema: **"Sistema de Gestión Académica"**, con un monograma propio (por ejemplo,
  un SVG inline simple con "SGA" o un ícono `bi-mortarboard`) en el login y el sidebar.
- Atribución discreta en el pie del login y del layout: **"Desarrollado por Person Technologies"**,
  con enlace a https://dc.persontechnologys.com/ (`rel="noopener"`, `target="_blank"`).
- **Elimina** de la interfaz "demo", "Demostración técnica", "Sistema de demostración" y
  "no es un documento/sistema oficial".
- **Sustitúyelos** solo donde haga falta veracidad, con texto sobrio y positivo:
  - Pie del reporte: "Reporte generado por el Sistema de Gestión Académica el dd/mm/aaaa hh:mm por <usuario>."
  - No afirmes en ningún sitio que el sistema o el reporte son oficiales, aprobados o emitidos por la ESPE.
- El encabezado del reporte conserva "UNIVERSIDAD DE LAS FUERZAS ARMADAS - ESPE" como **dato
  del contexto académico**, en texto y sin escudo (CLAUDE.md §9).
- Actualiza CLAUDE.md §9: cambia "generado por el sistema de demostración" por "generado por
  el Sistema de Gestión Académica", y añade la decisión "identidad propia, sin logo oficial; atribución a Person Technologies".
- Actualiza `APP_NAME` en `.env.example` a `"Sistema de Gestión Académica"`.

## 5. Login (split screen)

- **Columna izquierda** (≥ 992 px): fondo azul marino con un degradado sutil y un patrón
  geométrico ligero en CSS o SVG (sin imágenes externas), el monograma, "Sistema de Gestión
  Académica" y una frase breve sobre la gestión de calificaciones. En móviles se reduce a una cabecera compacta.
- **Columna derecha:** "Bienvenido", "Ingresa tus credenciales para continuar", correo,
  contraseña con botón mostrar/ocultar (`aria-label` y `aria-pressed`), "Recordarme",
  botón con spinner y deshabilitado al enviar, y errores del servidor (`@error`) visibles.
  El error de inactivo y el de demasiados intentos deben leerse bien.
- No cambies `LoginController` ni `LoginRequest`.

## 6. Layout autenticado: sidebar + topbar

- Un **componente Blade** por pieza: `x-layout.sidebar`, `x-layout.topbar`, `x-layout.breadcrumbs`,
  `x-ui.toast-stack`, `x-ui.confirm-modal`, `x-ui.empty-state`, `x-ui.status-badge`,
  `x-ui.page-header` y `x-form.input` (reutiliza y sustituye los parciales actuales).
- **Sidebar**, con secciones sin submenús (son pocas opciones):
  - Administrador:
    - *Inicio*.
    - *Académico*: Períodos, Asignaturas, Ofertas (NRC).
    - *Personas*: Estudiantes, Usuarios y docentes.
    - *Calificaciones*: Reportes, Auditoría.
  - Docente: *Inicio* (mis ofertas) y *Reportes*.
  - Ítem activo resaltado, con `aria-current="page"`.
  - Abajo: nombre, rol y "Cerrar sesión" (formulario POST con CSRF).
  - Escritorio: fijo y contraíble a íconos. El estado se guarda en `localStorage`; en modo
    contraído se muestran tooltips.
  - Móvil (< 992 px): Offcanvas con botón hamburguesa; se cierra al navegar.
- **Topbar:** título de la página, breadcrumbs y menú de usuario (nombre, rol, cerrar sesión). Nada más.
- El menú se oculta por rol, pero **la autorización sigue en el servidor** (middleware y Policies).
  Prueba que el docente no ve los enlaces de administración y que, aun así, recibe 403 si los visita.
- `@media print`: sidebar, topbar, toasts y botones ocultos. El reporte se imprime igual que hoy.

## 7. Dashboard con datos reales

**Administrador:**
- Tarjetas: Estudiantes activos, Docentes activos, Asignaturas activas, Ofertas del período
  abierto, Matrículas y **Registros incompletos** (matrículas sin notas o con algún parcial NULL).
  Calcúlalas con consultas agregadas (`count`, `whereNull`, `doesntHave`), no en PHP sobre colecciones.
- Período abierto actual (código, descripción, fechas), con un estado vacío si no hay ninguno.
- Ofertas recientes y "Actividad reciente": los últimos 5 registros de auditoría, con
  `with('user', 'enrollment.student', 'enrollment.courseOffering')`.
- Accesos rápidos: Nuevo estudiante, Nueva oferta y Reportes.

**Docente:**
- Tarjetas de sus ofertas con el NRC, la asignatura, el período con su estado, los matriculados y el
  progreso "X de Y registros completos" (calculado en la consulta).
- Botones "Calificaciones" (solo con el período abierto; en otro caso, "Ver") y "Reporte".

No inventes datos. Prueba que el dashboard no genera N+1 (`preventLazyLoading` ya está activo).

## 8. Listados y formularios

- **Tablas:** encabezado diferenciado, filas cómodas, hover sutil, números alineados a la
  derecha con `font-variant-numeric: tabular-nums`, badges de estado con texto (no solo color),
  y acciones como botones de ícono con `aria-label` y tooltip. Usa `.table-responsive` y, en
  móvil, oculta columnas secundarias con utilidades `d-none d-md-table-cell`. Estado vacío con
  ícono, mensaje y acción principal. Paginación de Bootstrap 5, tal como está.
- **Búsqueda y filtros:** en una barra de la tarjeta del listado, con "Limpiar" visible solo si hay filtros.
- **Formularios:** agrupados en tarjetas o `fieldset` con título (Identificación institucional,
  Datos personales, Acceso, Oferta…), `*` en los campos obligatorios, ayudas con `form-text`,
  errores del servidor bajo cada campo, botón principal y "Cancelar" secundario, y spinner al guardar.
- **Validación en el cliente:** solo complementaria. Bloquea el envío con campos vacíos u
  obligatorios mal formados y marca `.is-invalid`. Nunca elimines ni relajes reglas del servidor.
- **No conviertas los formularios en modales.** Los modales se usan solo para confirmar.

## 9. Planilla de calificaciones (prioridad alta)

- Conserva la estructura actual: un formulario por fila (atributo `form`) con su `version`
  oculta. Es la base del bloqueo optimista (decisión 29).
- Columnas: ID, Apellidos y nombres, 1P, 2P, 3P, Promedio /20, Total /60, Estado (badge con texto).
  Bajo el título, una leyenda: "0 a 20, máx. 2 decimales · vacío = pendiente · aprobado con total ≥ 42,00".
- Entrada: `inputmode="decimal"`, `pattern="^\d{1,2}([.,]\d{1,2})?$"`, `aria-label` con el
  parcial y el estudiante. Fuera de rango o con más de dos decimales se marca `.is-invalid`
  al instante, con un mensaje corto bajo la fila.
- **Sin cálculo de total, promedio ni estado en JavaScript.** Esos valores vienen solo del
  servidor (`GradeCalculator`, que es la única fuente de verdad). Tras guardar, la fila muestra el
  resultado del servidor y un toast "Notas guardadas".
- Las filas con cambios sin guardar se marcan con un indicador y un botón "Guardar" destacado.
  Si hay filas modificadas, `beforeunload` avisa antes de salir de la página.
- Enter dentro de una fila guarda esa fila. Tab recorre 1P → 2P → 3P → Guardar.
- El período no abierto se muestra en modo solo lectura (inputs deshabilitados) con un aviso
  claro. El conflicto de versión y los errores del servidor se muestran en la fila, como hoy.
- La corrección del administrador: modal de confirmación que muestra los valores anteriores y
  nuevos y el motivo antes de enviar. El motivo sigue siendo obligatorio en el servidor.

## 10. Confirmaciones y notificaciones

- **Un único modal de confirmación** (`x-ui.confirm-modal`) que se activa con atributos en el
  formulario: `data-confirm-title`, `data-confirm-message` y `data-confirm-item` (el registro
  afectado, escapado con `{{ }}`) y `data-confirm-variant="danger"`. Sin `confirm()` ni `alert()`
  nativos. El botón de confirmar recibe el foco y Escape cancela.
- **Toasts:**
  - `session('status')` → éxito.
  - `$errors->has('general')` → error.
  - Errores de validación → toast "Revise los campos marcados" además de los mensajes en línea.
  - Abajo a la derecha, con autocierre de 5 s (los de error no se cierran solos) y `role="status"` o `role="alert"`.
- Rediseña también `resources/views/errors/*` (403, 404, 419, 429, 500, 503) con el mismo
  sistema visual, sin trazas ni mensajes técnicos.

## 11. Accesibilidad y responsive

- Foco visible en todo, labels asociados, navegación completa por teclado (sidebar, modal, planilla),
  y estados con texto además de color.
- Sin scroll horizontal en 360, 768, 1024 y 1440 px, salvo dentro de `.table-responsive`.

## 12. Pruebas y verificación (obligatorio)

- `php artisan test` debe pasar completo al final de **cada** commit.
- Algunas pruebas comprueban textos que cambiarán: "no es un documento oficial" (ReportTest),
  "Iniciar sesión" (AuthenticationTest) y "el registro de notas está bloqueado" (TeacherGradingTest).
  Actualiza **solo** esas aserciones al texto nuevo, con la misma intención. No elimines pruebas ni debilites aserciones.
- Añade pruebas:
  - El login y el layout no contienen "demo", "demostración" ni "oficial", y sí "Person Technologies".
  - El docente no ve los enlaces de administración en el sidebar; el administrador sí.
  - El pie del reporte contiene "generado por el Sistema de Gestión Académica".
  - El dashboard del administrador muestra el conteo correcto de registros incompletos.
  - `espe.jpeg` no se referencia en ninguna vista.
  - No hay `{!!` en `resources/views`.
- Verifica con `php artisan serve` y `curl` que las páginas principales responden 200 con
  ambos roles, y que `bootstrap-icons.css` y sus fuentes, `app.css` y `app.js` cargan (200).
- No puedes ver el navegador: pide al responsable capturas a 1440 px y 375 px del login, el
  dashboard, la planilla y el reporte en vista de impresión, y ajusta según lo que vea.
- Ejecuta `vendor/bin/pint` antes de cada commit.

## 13. Entrega

Al terminar:
- Lista de commits, archivos principales y la biblioteca añadida (Bootstrap Icons, con su versión).
- Decisiones registradas en CLAUDE.md §18.1.
- Resultado real de las pruebas.
- Qué no pudiste verificar.
- Pasos para llevarlo a producción, **solo tras la aprobación del responsable**:
  1. Fusionar `ui-redesign` en `main`.
  2. `git checkout deploy && git merge main && git push && git checkout main && composer install`.
  3. *Update from Remote* en cPanel.

  No se cambian dependencias de Composer, así que el `vendor/` de `deploy` no se regenera.
