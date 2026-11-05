# PROMPT PARA CLAUDE CODE: REDISEÑO PROFESIONAL UI/UX — SISTEMA DE GESTIÓN ACADÉMICA ESPE

Actúa como un **Senior UI/UX Designer, Senior Frontend Engineer y especialista en Laravel Blade, Bootstrap 5 y diseño de sistemas administrativos profesionales**.

Quiero que realices un rediseño integral de la interfaz de usuario y experiencia de usuario (UI/UX) de este proyecto.

No quiero simplemente cambiar colores, agregar sombras o modificar algunos botones. Quiero que transformes la aplicación en un sistema académico moderno, elegante, intuitivo, consistente y visualmente profesional, con una experiencia similar a la de un software administrativo comercial de alta calidad.

El resultado debe transmitir confianza, calidad de desarrollo y profesionalismo.

## 1. ANTES DE IMPLEMENTAR: AUDITORÍA DEL PROYECTO

Primero inspecciona el proyecto existente:

- Revisa `CLAUDE.md`.
- Identifica la versión actual de Laravel y PHP.
- Inspecciona las vistas Blade.
- Revisa las hojas de estilos y archivos JavaScript.
- Identifica si ya se utiliza Bootstrap, jQuery u otras bibliotecas.
- Analiza el layout principal, menú, formularios, tablas, modales y componentes existentes.
- Identifica inconsistencias visuales, problemas de usabilidad y componentes duplicados.

No modifiques la lógica de negocio ni las reglas académicas durante el rediseño.

Conserva las rutas, controladores, modelos, relaciones, validaciones del servidor, Policies y funcionalidades existentes.

Después de la auditoría, realiza el rediseño de manera ordenada, sin detenerte en una propuesta teórica si ya tienes autorización para trabajar sobre el proyecto.

## 2. STACK FRONTEND Y BIBLIOTECAS

Utiliza las versiones estables más recientes y compatibles con el proyecto.

### Framework CSS

- Bootstrap 5, preferentemente la última versión estable de la rama 5.x.
- Bootstrap Icons para iconografía.
- CSS personalizado organizado mediante variables CSS.
- JavaScript moderno.
- jQuery únicamente cuando una biblioteca o funcionalidad lo justifique.

Comprueba la compatibilidad de las versiones antes de instalar o actualizar dependencias.

No mezcles diferentes versiones de Bootstrap ni cargues dos veces sus archivos JavaScript.

### Bibliotecas de interacción

Evalúa e incorpora las siguientes soluciones cuando aporten valor:

**Validación frontend**
- jQuery Validation (`jquery-validation`), si la arquitectura actual justifica el uso de jQuery.
- Adaptación visual de los mensajes de validación a Bootstrap 5.
- Validación en tiempo real cuando sea apropiado.
- Mensajes en español.
- Indicadores visuales de campos válidos e inválidos.

**Confirmaciones y diálogos**
- jQuery Confirm: https://craftpip.github.io/jquery-confirm/
- Evalúa también alternativas modernas y mantenidas, como SweetAlert2.
- Selecciona una solución principal, evitando duplicar bibliotecas para hacer lo mismo.

**Notificaciones**
- Notyf, Noty o Toastr, evaluando mantenimiento y compatibilidad.
- También puedes utilizar Bootstrap Toast para notificaciones sencillas.

Implementa notificaciones consistentes para:
- Operación realizada correctamente.
- Registro creado.
- Registro actualizado.
- Registro eliminado.
- Error de validación.
- Error inesperado.
- Operación no autorizada.
- Advertencias.

Las notificaciones deben integrarse con los mensajes flash de Laravel.

**Otros componentes**
- Bootstrap Modal.
- Bootstrap Tooltip.
- Bootstrap Popover.
- Dropdowns.
- Offcanvas para navegación móvil.
- Spinners y estados de carga.
- Componentes de paginación.
- Estados vacíos para tablas sin información.

No agregues bibliotecas por moda. Prioriza compatibilidad, mantenimiento, rendimiento y una experiencia consistente.

## 3. SISTEMA VISUAL (DESIGN SYSTEM)

Crea un sistema de diseño coherente para toda la aplicación.

Define:

- Paleta de colores.
- Tipografía.
- Jerarquía visual.
- Tamaños de títulos.
- Espaciados.
- Bordes y radios.
- Sombras.
- Estados de botones.
- Estados de formularios.
- Colores semánticos.
- Estilo de tablas.
- Estilo de tarjetas.
- Estilo de modales.
- Estilo de notificaciones.

### Dirección visual

Quiero una interfaz:

- Moderna.
- Profesional.
- Limpia.
- Elegante.
- Minimalista, pero no vacía.
- Con buena jerarquía visual.
- Con espacios bien distribuidos.
- Sin saturación de colores.
- Sin exceso de sombras.
- Sin elementos decorativos innecesarios.

Utiliza una combinación visual institucional, con azul oscuro, azul institucional, blanco y grises neutros. Los colores de éxito, advertencia y error deben ser semánticos y consistentes.

Evita interfaces que parezcan plantillas administrativas genéricas sin personalidad.

## 4. LOGIN: SPLIT SCREEN LOGIN

Rediseña completamente la pantalla de inicio de sesión.

Quiero utilizar el patrón:

**Split Screen Login / Two-Column Login Layout.**

La pantalla debe dividirse en dos columnas.

### Columna izquierda: identidad visual

Debe incluir:

- Logo ESPE ubicado en `public/espe.jpeg`.
- Nombre del sistema: Sistema de Gestión Académica.
- Mensaje institucional breve.
- Una composición visual moderna y profesional.
- Fondo con degradado o una combinación elegante de colores institucionales.
- Elementos gráficos sutiles relacionados con educación, tecnología y gestión académica.

### Columna derecha: formulario

Debe incluir:

- Título: Bienvenido.
- Subtítulo: Ingresa tus credenciales para continuar.
- Campo de correo electrónico o usuario, según el mecanismo de autenticación existente.
- Campo de contraseña.
- Botón para mostrar u ocultar contraseña.
- Opción para recordar sesión, si está implementada.
- Botón principal de inicio de sesión.
- Indicador de procesamiento.
- Mensajes de error claros y elegantes.
- Estados de foco y validación.

### Diseño responsive

En escritorio:
- Dos columnas equilibradas.
- Composición visual amplia.
- Formulario centrado verticalmente.

En tablets:
- Ajustar proporciones y espacios.

En móviles:
- Diseño de una sola columna.
- Formulario cómodo de utilizar.
- Logo visible.
- Sin desplazamientos horizontales.

No alteres el mecanismo de autenticación de Laravel.

## 5. DASHBOARD: SIDEBAR IZQUIERDO

Implementa un diseño administrativo profesional con:

**Dashboard con Sidebar Izquierdo (Left Sidebar Navigation).**

### Sidebar

Debe contener:

- Logo ESPE.
- Nombre del sistema.
- Navegación organizada por secciones.
- Iconos Bootstrap Icons.
- Indicador visual del menú activo.
- Submenús desplegables cuando corresponda.
- Perfil o identidad del usuario.
- Opción de cerrar sesión.

La navegación debe adaptarse a los permisos del usuario.

Administrador y docente deben visualizar únicamente las opciones correspondientes a sus permisos.

### Comportamiento

En escritorio:
- Sidebar fijo o persistente.
- Posibilidad de contraerlo.
- Contenido principal con espacio adecuado.

En móviles:
- Sidebar mediante Offcanvas.
- Botón hamburguesa.
- Cierre al seleccionar una opción.

### Barra superior (Topbar)

Debe contener:

- Título de la sección actual.
- Breadcrumbs.
- Nombre del usuario autenticado.
- Rol del usuario.
- Menú de perfil.
- Cierre de sesión.

No sobrecargues la barra superior.

## 6. DASHBOARD PRINCIPAL

Diseña un dashboard útil, no simplemente decorativo.

Incluye tarjetas de indicadores cuando los datos estén disponibles:

- Total de estudiantes.
- Total de docentes.
- Total de asignaturas.
- Ofertas académicas activas.
- Matrículas registradas.
- Calificaciones pendientes o incompletas.

Incluye secciones como:

- Resumen académico.
- Período académico actual.
- Ofertas académicas recientes.
- Actividad reciente, si existe información real.
- Accesos rápidos según los permisos.

No inventes estadísticas ni datos. Utiliza información real obtenida desde Laravel.

Si no hay datos, muestra estados vacíos bien diseñados.

## 7. FORMULARIOS PROFESIONALES

Rediseña todos los formularios del sistema.

Incluye:

- Labels claros.
- Placeholders útiles.
- Indicadores de campos obligatorios.
- Ayudas contextuales.
- Validaciones visuales.
- Mensajes debajo de los campos.
- Estados de carga.
- Botones con jerarquía visual clara.
- Distribución lógica de campos.
- Espaciado consistente.

Para campos relacionados, utiliza agrupaciones visuales.

Ejemplos:

- Datos personales del estudiante.
- Identificación institucional.
- Información de asignaturas.
- Datos de ofertas académicas.
- Información de matrículas.
- Registro de calificaciones.

### Validación

La validación frontend es complementaria, nunca reemplaza la validación de Laravel.

Los formularios deben mostrar los errores enviados por el servidor mediante `@error`, `old()` y los mecanismos apropiados de Laravel.

No elimines ni debilites las reglas de validación existentes.

## 8. TABLAS Y LISTADOS

Las tablas deben tener apariencia profesional.

Implementa:

- Encabezados diferenciados.
- Filas con espaciado adecuado.
- Hover sutil.
- Columnas correctamente alineadas.
- Badges para estados.
- Acciones con iconos y tooltips.
- Paginación visualmente consistente.
- Buscador.
- Filtros cuando sean útiles.
- Estados vacíos.
- Indicadores de carga.

En dispositivos pequeños, utiliza tablas responsive o una presentación alternativa cuando sea necesario.

No reduzcas excesivamente el tamaño de las fuentes para intentar hacer caber todas las columnas.

## 9. MÓDULO DE CALIFICACIONES: ATENCIÓN ESPECIAL

Este módulo es fundamental.

Su interfaz debe ser especialmente clara, porque representa las reglas académicas definidas para el proyecto.

Cada estudiante debe mostrar:

- ID institucional.
- Apellidos y nombres.
- Primer parcial (1P).
- Segundo parcial (2P).
- Tercer parcial (3P).
- Promedio sobre 20.
- Total acumulado sobre 60.
- Estado académico.

Las calificaciones deben mostrarse con dos decimales.

El estado debe distinguirse mediante badges:

- Aprobado.
- Reprobado.
- Incompleto.

Utiliza una presentación visual que facilite la introducción de las tres notas.

Considera:
- Entrada numérica clara.
- Límites visibles de 0 a 20.
- Validación inmediata.
- Navegación sencilla entre campos.
- Guardado claro.
- Indicador de cambios pendientes.
- Confirmación antes de abandonar cambios sin guardar, cuando corresponda.

El cálculo debe respetar estrictamente las reglas del proyecto:

- Tres parciales.
- Cada parcial de 0 a 20.
- Total acumulado sobre 60.
- Promedio sobre 20.
- Aprobación desde 42/60.

No cambies estas reglas ni las implementes únicamente en JavaScript.

## 10. MODALES, CONFIRMACIONES Y TOOLTIPS

Utiliza modales para operaciones que se beneficien de una interacción contextual, por ejemplo:

- Crear o editar registros sencillos.
- Consultar detalles.
- Confirmar operaciones.
- Mostrar información complementaria.

No conviertas todos los formularios en modales. Para formularios extensos, conserva páginas independientes.

Utiliza tooltips para explicar iconos o acciones poco evidentes.

Las confirmaciones de eliminación deben:

- Explicar claramente qué registro se eliminará.
- Identificar el elemento afectado.
- Permitir cancelar.
- Evitar eliminaciones accidentales.

No utilices `alert()` ni `confirm()` nativos del navegador en la experiencia normal de la aplicación.

## 11. IDENTIDAD VISUAL Y MARCA

Utiliza el logo institucional disponible en:

`public/espe.jpeg`

Requisitos:

- Mantener su proporción.
- No deformarlo.
- No cambiar sus colores.
- Utilizar un tamaño adecuado.
- Evitar que pierda legibilidad.
- Optimizar su presentación en login y sidebar.

La identidad del desarrollador debe estar presente de forma elegante.

Desarrollado por:

**Person Technologies**

Sitio web:

https://dc.persontechnologys.com/

Puedes incluir esta atribución discretamente en el footer del sistema y en la pantalla de inicio de sesión.

El sistema debe presentarse con el nombre:

**Sistema de Gestión Académica**

Elimina de la interfaz todos los textos innecesarios que lo presenten como una demostración, prototipo o ejercicio académico, incluyendo expresiones como:

- "Demostración técnica".
- "Sistema de demostración".
- "Demo".
- "No es un documento oficial".
- "No es un sistema oficial".
- Mensajes similares que resten profesionalismo a la interfaz.

No sustituyas esos textos por afirmaciones de aprobación, certificación, propiedad institucional o carácter oficial que no hayan sido autorizadas. La presentación debe ser profesional y sobria, sin declaraciones institucionales engañosas.

## 12. EXPERIENCIA DE USUARIO

Aplica buenas prácticas de UX:

- Navegación intuitiva.
- Acciones principales fácilmente identificables.
- Jerarquía visual.
- Feedback inmediato.
- Prevención de errores.
- Mensajes comprensibles.
- Estados de carga.
- Estados vacíos.
- Confirmación de operaciones sensibles.
- Consistencia entre módulos.

Evita:
- Botones sin función.
- Enlaces vacíos.
- Iconos ambiguos.
- Formularios excesivamente largos sin agrupación.
- Mensajes técnicos dirigidos al usuario final.
- Popups innecesarios.
- Animaciones excesivas.
- Elementos que se mueven sin aportar valor.

Toda interacción debe tener una respuesta visual clara.

## 13. ACCESIBILIDAD Y RESPONSIVE DESIGN

Cumple buenas prácticas de accesibilidad:

- Contraste suficiente.
- Labels asociados a campos.
- Navegación por teclado.
- Estados de foco visibles.
- Mensajes de error comprensibles.
- Iconos acompañados de texto o etiquetas accesibles cuando corresponda.
- No depender únicamente del color para comunicar estados.

Comprueba los siguientes tamaños:

- Escritorio grande.
- Laptop.
- Tablet.
- Móvil.

No debe existir scroll horizontal accidental.

## 14. ORGANIZACIÓN DEL CÓDIGO

Mantén el código limpio y reutilizable.

Centraliza los componentes visuales compartidos:

- Layout principal.
- Sidebar.
- Topbar.
- Breadcrumbs.
- Alertas.
- Toasts.
- Modales.
- Botones.
- Formularios.
- Tablas.
- Badges.
- Estados vacíos.

Evita duplicar HTML, CSS y JavaScript en todas las vistas.

Organiza correctamente los assets y respeta la estructura existente de Laravel.

No introduzcas un framework frontend adicional como React o Vue. La interfaz debe continuar utilizando Blade y Bootstrap 5.

## 15. RENDIMIENTO

- Evita cargar bibliotecas duplicadas.
- Utiliza assets locales o un sistema de compilación consistente.
- No cargues scripts innecesarios en todas las páginas.
- Optimiza CSS y JavaScript.
- Evita consultas adicionales desde las vistas.
- No generes problemas N+1 al mostrar información académica.
- Mantén una experiencia fluida en hosting compartido cPanel.

## 16. CRITERIOS DE ACEPTACIÓN

El rediseño estará completo cuando:

1. El login tenga diseño Split Screen profesional.
2. El dashboard tenga sidebar izquierdo moderno y responsive.
3. El menú respete los roles y permisos.
4. Todos los módulos compartan el mismo sistema visual.
5. Los formularios tengan validaciones visuales y mensajes claros.
6. Las tablas sean modernas, legibles y responsive.
7. Las confirmaciones y notificaciones tengan un diseño consistente.
8. Los modales y tooltips funcionen correctamente.
9. El módulo de calificaciones sea intuitivo y respete las reglas académicas.
10. El logo ESPE se utilice correctamente desde `public/espe.jpeg`.
11. La atribución a Person Technologies sea discreta y profesional.
12. No queden textos de demostración ni mensajes que resten profesionalismo.
13. No se rompa ninguna funcionalidad existente.
14. La aplicación funcione correctamente en escritorio y móvil.
15. No existan errores de consola ni dependencias incompatibles.
16. Las pruebas existentes continúen pasando.

## 17. FORMA DE TRABAJO

Trabaja con criterio profesional.

Primero inspecciona el proyecto y detecta los componentes que deben rediseñarse.

Después implementa el sistema visual compartido y aplícalo progresivamente a los módulos.

No hagas cambios superficiales aislados: mantén una identidad visual uniforme en toda la aplicación.

No elimines funcionalidades para simplificar el rediseño.

No modifiques reglas académicas ni controles de autorización.

Al finalizar:

- Resume los cambios realizados.
- Enumera los archivos principales modificados.
- Indica las bibliotecas añadidas y sus versiones.
- Explica las decisiones de diseño relevantes.
- Informa las pruebas ejecutadas y sus resultados.
- Identifica cualquier funcionalidad que no haya podido verificarse.

**OBJETIVO FINAL:**

Quiero que el sistema tenga la apariencia, consistencia y experiencia de usuario de un producto de software profesional, desarrollado cuidadosamente por Person Technologies, con una identidad académica clara y una interfaz moderna de alta calidad.

No quiero una plantilla administrativa genérica. Quiero una aplicación con personalidad visual, excelente usabilidad y calidad de producto.