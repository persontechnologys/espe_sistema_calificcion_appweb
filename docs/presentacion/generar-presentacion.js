// Presentación de la entrevista técnica (2 min antes de la demo + anexos de respaldo).
// Reutiliza los diseños de generar-plantilla.js (PORTADA, CONTENIDO, SEPARADOR).
// Requiere recursos/ (generar-fondos.py, generar-iconos.js).
// Uso: NODE_PATH=<node_modules con pptxgenjs> PPTX_SKILL=<herramientas pptx> node docs/presentacion/generar-presentacion.js
const path = require("path");
const pptxgen = require("pptxgenjs");

const R = (f) => path.join(__dirname, "recursos", f);
const ICON = (name, color) => R(`iconos/${name}-${color}.png`);
const OUT = path.join(__dirname, "presentacion.pptx");

// --- Tema y diseños (mismos que generar-plantilla.js) ---------------------------------
const THEME = {
    name: "Arquitectura ESPE",
    headFontFace: "Space Grotesk",
    bodyFontFace: "Inter",
    colors: {
        dk1: "0F1F17", lt1: "FFFFFF", dk2: "071A12", lt2: "F6F8F7",
        accent1: "0D713D", accent2: "A8FF53", accent3: "33A381",
        accent4: "6E4EF2", accent5: "62A92B", accent6: "5B6577",
        hlink: "0D713D", folHlink: "6E4EF2",
    },
};
const C = { ink: "071A12", green: "0D713D", lime: "A8FF53", mint: "C9DDD3", text: "0F1F17", muted: "5B6577",
    pale: "E3F2EA", white: "FFFFFF", border: "DCE5E0", soft: "8A96A3" };
const MONO = "JetBrains Mono", HEAD = "Space Grotesk";

const W = 13.333, H = 7.5, M = 0.6;
const NOMBRE = "Ing. David Criollo, Mgtr.";
const PERFIL = "Arquitectura de Software e IA  ·  Sistemas Inteligentes  ·  Docencia Universitaria";
const WEB = "dc.persontechnologys.com";
const PROYECTO = "Registro y gestión de estudiantes, asignaturas y calificaciones";

const pres = new pptxgen();
pres.layout = "LAYOUT_WIDE";
pres.theme = { headFontFace: THEME.headFontFace, bodyFontFace: THEME.bodyFontFace };
pres.author = "Vilmer David Criollo Chanchicocha";
pres.title = "Sistema de Gestión Académica";

const pie = (color, withNumber) => [
    { text: { text: WEB, options: { x: W - M - (withNumber ? 4.75 : 3.6), y: H - 0.52, w: 3.6, h: 0.3, fontFace: MONO, fontSize: 10, color, align: "right", valign: "middle", margin: 0 } } },
];
const numerar = () => pres._slides.forEach((slide, i) => {
    const light = slide._slideLayout && slide._slideLayout._name === "CONTENIDO";
    slide.addText([
        { text: NOMBRE, options: { fontFace: "Inter", fontSize: 12, bold: true, color: light ? C.text : C.white } },
        { text: "   ·   " + PERFIL, options: { fontFace: "Inter", fontSize: 10.5, color: light ? C.muted : C.mint } },
    ], { x: M, y: H - 0.54, w: 8.2, h: 0.34, valign: "middle", margin: 0, isTextBox: true, objectName: "pie-perfil" });
    if (light) {
        const pad = (n) => String(n).padStart(2, "0");
        slide.addText(`${pad(i + 1)} / ${pad(pres._slides.length)}`, { x: W - M - 1.0, y: H - 0.52, w: 1.0, h: 0.3,
            fontFace: MONO, fontSize: 10, bold: true, color: C.green, align: "right", valign: "middle", margin: 0, isTextBox: true, objectName: "numero-pagina" });
    }
});
const ph = (name, type, text, options) => ({ placeholder: { options: { name, type, margin: [0, 0, 0, 0], ...options }, text } });

pres.defineSlideMaster({
    title: "PORTADA",
    background: { path: R("fondo-portada.jpg") },
    objects: [
        ...pie(C.mint, false),
        ph("kicker", "body", "ENTREVISTA TÉCNICA", { x: M, y: 1.75, w: 8, h: 0.35, fontFace: MONO, fontSize: 13, bold: true, color: C.lime }),
        ph("title", "title", "Título", { x: M, y: 2.2, w: 8, h: 1.75, fontSize: 54, bold: true, color: C.white, align: "left", valign: "top", lineSpacingMultiple: 0.95 }),
        ph("subtitle", "body", "Subtítulo", { x: M, y: 4.05, w: 9, h: 0.5, fontSize: 19, color: C.mint, align: "left", valign: "top" }),
        ph("presenter", "body", "Nombre", { x: M, y: 5.75, w: 7, h: 0.4, fontFace: HEAD, fontSize: 18, bold: true, color: C.white }),
        ph("meta", "body", "Cargo · Fecha", { x: M, y: 6.12, w: 8, h: 0.35, fontSize: 13, color: C.mint }),
    ],
});
pres.defineSlideMaster({
    title: "CONTENIDO",
    background: { path: R("fondo-contenido.jpg") },
    margin: [0.4, M, 0.8, M],
    objects: [
        { image: { path: ICON("LuGraduationCap", "verde"), x: M, y: 0.44, w: 0.28, h: 0.28 } },
        { text: { text: PROYECTO, options: { x: M + 0.4, y: 0.42, w: 8, h: 0.32, fontFace: "Inter", fontSize: 11, color: C.muted, valign: "middle", margin: 0 } } },
        ...pie(C.muted, true),
        ph("section", "body", "00 · SECCIÓN", { x: M, y: 1.08, w: 8, h: 0.32, fontFace: MONO, fontSize: 12, bold: true, color: C.green }),
        ph("title", "title", "Título de la diapositiva", { x: M, y: 1.42, w: W - 2 * M, h: 0.8, fontSize: 34, bold: true, color: C.text, align: "left", valign: "top" }),
    ],
});
pres.defineSlideMaster({
    title: "SEPARADOR",
    background: { path: R("fondo-separador.jpg") },
    objects: [
        ...pie(C.mint, false),
        ph("number", "body", "00", { x: M, y: 1.55, w: 4, h: 1.5, fontFace: HEAD, fontSize: 110, bold: true, color: C.lime, valign: "bottom" }),
        ph("kicker", "body", "SECCIÓN", { x: M, y: 3.3, w: 8, h: 0.35, fontFace: MONO, fontSize: 13, bold: true, color: C.lime }),
        ph("title", "title", "Título", { x: M, y: 3.7, w: 8.5, h: 1.0, fontSize: 48, bold: true, color: C.white, align: "left", valign: "top" }),
        ph("subtitle", "body", "Subtítulo", { x: M, y: 4.75, w: 9, h: 0.7, fontSize: 19, color: C.mint, align: "left", valign: "top" }),
    ],
});

// --- Helpers ----------------------------------------------------------------------------
const shadow = () => ({ type: "outer", color: "0F1F17", opacity: 0.08, blur: 12, offset: 3, angle: 90 });
const T = (s, text, o) => s.addText(text, { margin: 0, isTextBox: true, ...o });
const card = (s, x, y, w, h, name, fill = C.white) => s.addShape(pres.ShapeType.roundRect, { x, y, w, h, rectRadius: 0.14,
    fill: { color: fill }, line: fill === C.white ? { color: C.border, width: 0.75 } : { color: fill, width: 0 }, shadow: shadow(), objectName: name });
const iconBox = (s, icon, x, y, size, alt, fill = C.pale, color = "verde") => {
    s.addShape(pres.ShapeType.roundRect, { x, y, w: size, h: size, rectRadius: size * 0.22, fill: { color: fill }, objectName: `icono-${alt}` });
    const p = size * 0.21;
    s.addImage({ path: ICON(icon, color), x: x + p, y: y + p, w: size - 2 * p, h: size - 2 * p, altText: alt });
};
const competencia = (s, text) => {
    const w = 0.4 + text.length * 0.082;
    s.addShape(pres.ShapeType.roundRect, { x: W - M - w, y: 1.05, w, h: 0.36, rectRadius: 0.18, fill: { color: C.pale }, objectName: "competencia" });
    T(s, text, { x: W - M - w, y: 1.05, w, h: 0.36, fontFace: MONO, fontSize: 10, bold: true, color: C.green, align: "center", valign: "middle" });
};
const line = (s, x1, y1, x2, y2, o = {}) => s.addShape(pres.ShapeType.line, {
    x: Math.min(x1, x2), y: Math.min(y1, y2), w: Math.abs(x2 - x1), h: Math.abs(y2 - y1),
    flipH: (x2 < x1) !== (y2 < y1), line: { color: C.green, width: 1.25, ...o } });
const flecha = (s, x, y, w = 0.32, color = C.green) => s.addShape(pres.ShapeType.rightArrow, { x, y, w, h: 0.3, fill: { color }, line: { color, width: 0 }, objectName: "flecha" });
const contenido = (section, title, comp) => {
    const s = pres.addSlide({ masterName: "CONTENIDO" });
    s.addText(section, { placeholder: "section" });
    s.addText(title, { placeholder: "title" });
    if (comp) competencia(s, comp);
    return s;
};
const panelDerecho = (s, y, h) => {
    const px = 7.65, pw = W - M - px;
    s.addShape(pres.ShapeType.roundRect, { x: px, y, w: pw, h, rectRadius: 0.16, fill: { color: C.ink }, shadow: shadow(), objectName: "panel-oscuro" });
    return { px, pw };
};

// 1. PORTADA ----------------------------------------------------------------------------
{
    const s = pres.addSlide({ masterName: "PORTADA" });
    s.addText("ENTREVISTA TÉCNICA · PROFESOR OCASIONAL 1 · DCCO-SS", { placeholder: "kicker" });
    s.addText("Sistema de Gestión Académica", { placeholder: "title" });
    s.addText(PROYECTO, { placeholder: "subtitle" });
    s.addText("Vilmer David Criollo Chanchicocha", { placeholder: "presenter" });
    s.addText("Universidad de las Fuerzas Armadas ESPE  ·  05 de noviembre de 2026", { placeholder: "meta" });
    let cx = M;
    ["Laravel 13", "PHP 8.4", "PostgreSQL · MySQL 8", "cPanel + HTTPS"].forEach((t) => {
        const w = 0.32 + t.length * 0.098;
        s.addShape(pres.ShapeType.roundRect, { x: cx, y: 4.95, w, h: 0.42, rectRadius: 0.21, fill: { color: C.ink, transparency: 30 }, line: { color: C.lime, width: 1 }, objectName: `chip-${t}` });
        T(s, t, { x: cx, y: 4.95, w, h: 0.42, fontFace: MONO, fontSize: 11, color: C.lime, align: "center", valign: "middle" });
        cx += w + 0.15;
    });
    s.addNotes("Buenos días. Soy David Criollo. Presento el Sistema de Gestión Académica: una aplicación web hecha en Laravel y PHP, "
        + "probada en dos bases de datos y publicada en un servidor real con conexión segura. En dos minutos explico el problema, cómo trabajé, "
        + "cómo está construido y cómo guarda los datos; después lo muestro funcionando.");
}

// 2. CONTEXTO -----------------------------------------------------------------------------
{
    const s = contenido("01 · CONTEXTO", "Problema y alcance", "VALIDACIONES Y REGLAS DE NEGOCIO");
    const lw = 6.7;
    s.addShape(pres.ShapeType.roundRect, { x: M, y: 2.4, w: lw, h: 1.3, rectRadius: 0.12, fill: { color: C.pale }, objectName: "reto" });
    s.addShape(pres.ShapeType.rect, { x: M, y: 2.4, w: 0.08, h: 1.3, fill: { color: C.green }, objectName: "reto-barra" });
    T(s, "EL RETO", { x: M + 0.35, y: 2.55, w: lw - 0.6, h: 0.3, fontFace: MONO, fontSize: 11, bold: true, color: C.green });
    T(s, "Registrar y gestionar estudiantes, asignaturas y calificaciones", { x: M + 0.35, y: 2.88, w: lw - 0.6, h: 0.72,
        fontFace: HEAD, fontSize: 20, bold: true, color: C.text, valign: "top" });
    const rw = (lw - 0.25) / 2;
    [["LuUserCog", "Administrador", "Gestiona NRC y matrículas. Corrige notas con motivo."],
     ["LuUsers", "Docente", "Solo sus NRC asignados: registra 1P, 2P y 3P."]].forEach(([icon, head, body], i) => {
        const x = M + i * (rw + 0.25), y = 3.95;
        card(s, x, y, rw, 1.65, `rol-${i + 1}`);
        iconBox(s, icon, x + 0.25, y + 0.25, 0.55, head);
        T(s, head, { x: x + 0.95, y: y + 0.25, w: rw - 1.1, h: 0.55, fontFace: HEAD, fontSize: 18, bold: true, color: C.text, valign: "middle" });
        T(s, body, { x: x + 0.25, y: y + 0.92, w: rw - 0.5, h: 0.6, fontSize: 13, color: C.muted, valign: "top", lineSpacingMultiple: 1.1 });
    });
    s.addImage({ path: ICON("LuBan", "verde"), x: M + 0.05, y: 5.85, w: 0.36, h: 0.36, altText: "Fuera del alcance" });
    T(s, [
        { text: "Fuera del alcance: ", options: { bold: true, color: C.text } },
        { text: "tareas ponderadas, asistencia, pagos, integraciones externas y app móvil.", options: { color: C.muted } },
    ], { x: M + 0.6, y: 5.75, w: lw - 0.6, h: 0.6, fontSize: 13, valign: "middle" });
    // Reglas en lenguaje simple + ejemplo
    const { px, pw } = panelDerecho(s, 2.4, 4.0);
    T(s, "REGLAS DE CALIFICACIÓN", { x: px + 0.4, y: 2.58, w: pw - 0.8, h: 0.3, fontFace: MONO, fontSize: 12, bold: true, color: C.lime });
    [["3 notas", "parciales de 0 a 20, con hasta 2 decimales"],
     ["60", "total máximo: 1P + 2P + 3P"],
     ["42 o más", "APROBADO (se mira el total)"],
     ["Vacío ≠ 0", "nota pendiente: INCOMPLETO"]].forEach(([n, label], i) => {
        const y = 2.98 + i * 0.6;
        T(s, n, { x: px + 0.4, y, w: 1.8, h: 0.52, fontFace: HEAD, fontSize: 24, bold: true, color: C.lime, valign: "middle" });
        T(s, label, { x: px + 2.25, y, w: pw - 2.5, h: 0.52, fontSize: 13, color: C.mint, valign: "middle" });
    });
    s.addShape(pres.ShapeType.roundRect, { x: px + 0.3, y: 5.48, w: pw - 0.6, h: 0.75, rectRadius: 0.1, fill: { color: "0F2A1E" }, line: { color: C.lime, width: 0.75 }, objectName: "ejemplo" });
    T(s, [
        { text: "Ejemplo  ", options: { fontFace: MONO, fontSize: 10, bold: true, color: C.lime } },
        { text: "10 + 15 + 17 = 42  →  APROBADO", options: { fontFace: HEAD, fontSize: 15, bold: true, color: C.white, breakLine: true } },
        { text: "Con 41,99 reprueba, aunque el promedio dé 14,00.", options: { fontSize: 11.5, color: C.mint } },
    ], { x: px + 0.5, y: 5.48, w: pw - 1.0, h: 0.75, valign: "middle" });
    s.addNotes("El reto fue registrar y gestionar estudiantes, asignaturas y calificaciones. Lo convertí en reglas claras: tres parciales de 0 a 20; "
        + "se aprueba con 42 de 60 en el total, aunque un parcial sea bajo. Por ejemplo, 10 más 15 más 17 da 42: aprueba. "
        + "Una nota vacía no es un cero: queda pendiente. Hay dos roles: el administrador gestiona y el docente solo califica sus cursos.");
}

// 3. METODOLOGÍA --------------------------------------------------------------------------
{
    const s = contenido("02 · METODOLOGÍA", "Kanban: flujo continuo, una tarea a la vez", "METODOLOGÍA Y BUENAS PRÁCTICAS");
    const cols = ["Backlog", "Por hacer", "En progreso", "En revisión", "En pruebas", "Terminado"];
    const ov = 0.12, cw = (W - 2 * M + 5 * ov) / 6;
    cols.forEach((c, i) => {
        const x = M + i * (cw - ov), wip = c === "En progreso", done = c === "Terminado";
        const fill = wip ? C.ink : done ? C.green : (i % 2 ? "D3E8DC" : C.pale);
        s.addShape(i === 0 ? pres.ShapeType.homePlate : pres.ShapeType.chevron, { x, y: 2.4, w: cw, h: 0.68, fill: { color: fill },
            line: { color: C.white, width: 1.5 }, objectName: `columna-${i + 1}` });
        T(s, wip ? [{ text: c, options: { breakLine: true } }, { text: "máx. 1 tarea", options: { color: C.lime, fontSize: 10 } }] : c,
            { x: x + 0.3, y: 2.4, w: cw - 0.55, h: 0.68, fontFace: MONO, fontSize: 12, bold: true, color: wip || done ? C.white : C.text, align: "center", valign: "middle" });
    });
    const incs = [
        ["LuCode", "INCREMENTO 1", "Base", "T01 a T06", "Proyecto, base de datos, cálculo de notas, acceso y pruebas"],
        ["LuUsers", "INCREMENTO 2", "Administración", "T07 a T09", "Asignaturas, períodos, personas y NRC"],
        ["LuCalculator", "INCREMENTO 3", "Matrículas y notas", "T10 a T13", "Matrículas, registro de notas y permisos"],
        ["LuFileText", "INCREMENTO 4", "Reportes y cierre", "T14 a T20", "Reporte, auditoría, seguridad y servidor"],
        ["LuSparkles", "MEJORA", "Interfaz", "T21", "Diseño nuevo, panel de inicio y colores"],
    ];
    const iw = (W - 2 * M - 4 * 0.2) / 5;
    incs.forEach(([icon, k, name, tasks, body], i) => {
        const x = M + i * (iw + 0.2), y = 3.3;
        card(s, x, y, iw, 2.55, `incremento-${i + 1}`);
        iconBox(s, icon, x + 0.22, y + 0.22, 0.5, name);
        T(s, k, { x: x + 0.85, y: y + 0.22, w: iw - 1.0, h: 0.25, fontFace: MONO, fontSize: 9.5, bold: true, color: C.green });
        T(s, tasks, { x: x + 0.85, y: y + 0.47, w: iw - 1.0, h: 0.25, fontFace: MONO, fontSize: 10, color: C.muted });
        T(s, name, { x: x + 0.22, y: y + 0.88, w: iw - 0.4, h: 0.62, fontFace: HEAD, fontSize: 16, bold: true, color: C.text, valign: "top", lineSpacingMultiple: 0.95 });
        T(s, body, { x: x + 0.22, y: y + 1.5, w: iw - 0.4, h: 0.95, fontSize: 12.5, color: C.muted, valign: "top", lineSpacingMultiple: 1.1 });
    });
    s.addShape(pres.ShapeType.roundRect, { x: M, y: 6.03, w: W - 2 * M, h: 0.56, rectRadius: 0.12, fill: { color: C.pale }, objectName: "commits" });
    s.addImage({ path: ICON("LuGitBranch", "verde"), x: M + 0.22, y: 6.13, w: 0.36, h: 0.36, altText: "Commits" });
    T(s, [
        { text: "36 entregas pequeñas en Git ", options: { bold: true, color: C.text } },
        { text: "(commits): cada una corresponde a una tarea del tablero.", options: { color: C.muted } },
    ], { x: M + 0.75, y: 6.03, w: W - 2 * M - 1.0, h: 0.56, fontSize: 14, valign: "middle" });
    s.addNotes("Trabajé con Kanban porque es un proyecto pequeño y con tiempo corto. Cada tarea pasa por estas columnas y solo hay una en progreso a la vez, "
        + "así nada queda a medias. Avancé en cuatro incrementos, cada uno funcionando al terminar, y una mejora final de interfaz. "
        + "Cada tarea tiene criterios de aceptación y cada cambio en Git corresponde a una tarea.");
}

// 4. ARQUITECTURA -------------------------------------------------------------------------
{
    const s = contenido("03 · ARQUITECTURA", "Un solo sistema, ordenado en capas (MVC)", "ARQUITECTURA · JUSTIFICACIÓN TÉCNICA");
    const capas = [
        ["LuSignpost", "Rutas", "la dirección que pide el navegador"],
        ["LuShieldCheck", "Seguridad", "¿quién es y qué puede hacer? (Policies)"],
        ["LuWorkflow", "Controladores", "coordinan cada operación"],
        ["LuClipboardCheck", "Validación", "revisa los datos que llegan"],
        ["LuCalculator", "Reglas de notas", "GradeService · GradeCalculator"],
        ["LuLayers", "Modelos", "tablas como objetos (Eloquent)"],
        ["LuDatabase", "Base de datos", "guarda y protege los datos"],
    ];
    const rw = 5.3;
    capas.forEach(([icon, name, desc], i) => {
        const y = 2.4 + i * 0.6, svc = i === 4;
        s.addShape(pres.ShapeType.roundRect, { x: M, y, w: rw, h: 0.52, rectRadius: 0.1, fill: { color: svc ? C.ink : C.white },
            line: { color: svc ? C.ink : C.border, width: 0.75 }, objectName: `capa-${i + 1}` });
        s.addImage({ path: ICON(icon, svc ? "lima" : "verde"), x: M + 0.17, y: y + 0.11, w: 0.3, h: 0.3, altText: name });
        T(s, [
            { text: name, options: { fontFace: HEAD, fontSize: 15, bold: true, color: svc ? C.lime : C.text } },
            { text: "   " + desc, options: { fontSize: 11.5, color: svc ? C.mint : C.muted } },
        ], { x: M + 0.65, y, w: rw - 0.8, h: 0.52, valign: "middle" });
    });
    // Flecha de flujo a la izquierda: la solicitud baja por las capas
    s.addShape(pres.ShapeType.downArrow, { x: M - 0.38, y: 2.5, w: 0.22, h: 4.0, fill: { color: C.pale }, line: { color: C.pale, width: 0 }, objectName: "flujo" });
    const by = 2.4 + 2 * 0.6;
    line(s, M + rw, by + 0.26, M + rw + 0.25, by + 0.26);
    s.addShape(pres.ShapeType.roundRect, { x: M + rw + 0.25, y: by - 0.1, w: 1.25, h: 0.72, rectRadius: 0.1, fill: { color: C.pale }, objectName: "pantallas" });
    s.addImage({ path: ICON("LuMonitor", "verde"), x: M + rw + 0.25 + 0.47, y: by - 0.04, w: 0.3, h: 0.3, altText: "Pantallas" });
    T(s, "Pantallas (Blade)", { x: M + rw + 0.25, y: by + 0.28, w: 1.25, h: 0.3, fontSize: 10.5, bold: true, color: C.green, align: "center", valign: "middle" });
    const { px, pw } = panelDerecho(s, 2.4, 4.12);
    T(s, "¿POR QUÉ NO MICROSERVICIOS?", { x: px + 0.4, y: 2.62, w: pw - 0.8, h: 0.3, fontFace: MONO, fontSize: 12, bold: true, color: C.lime });
    [["LuTarget", "Sistema pequeño", "un solo equipo y un solo propósito"],
     ["LuLock", "Todo o nada", "la nota y su registro se guardan juntos"],
     ["LuServer", "Un solo servidor", "se publica fácil en cPanel"],
     ["LuLayers", "Ordenado en capas", "si crece, se puede separar una parte"]].forEach(([icon, head, body], i) => {
        const y = 3.08 + i * 0.84;
        s.addImage({ path: ICON(icon, "lima"), x: px + 0.4, y: y + 0.08, w: 0.4, h: 0.4, altText: head });
        T(s, head, { x: px + 1.0, y, w: pw - 1.3, h: 0.34, fontFace: HEAD, fontSize: 17, bold: true, color: C.lime });
        T(s, body, { x: px + 1.0, y: y + 0.36, w: pw - 1.3, h: 0.34, fontSize: 13, color: C.mint });
    });
    s.addNotes("Es un solo sistema ordenado en capas, con el patrón MVC de Laravel. Cada pedido baja por las capas: primero se verifica quién es el usuario y qué puede hacer, "
        + "luego se validan los datos, y las reglas de notas están en un único lugar, GradeService y GradeCalculator. "
        + "No usé microservicios porque el sistema es pequeño, la nota y su auditoría deben guardarse juntas y se publica en un solo servidor.");
}

// 5. DATOS --------------------------------------------------------------------------------
{
    const s = contenido("04 · DATOS", "Modelo de datos: 8 tablas", "MODELADO DE BASE DE DATOS");
    const tw = 2.55, gx = (W - 2 * M - 4 * tw) / 3, th = 1.45, yTop = 2.45, yBot = 4.62;
    const X = (c) => M + c * (tw + gx);
    const tabla = (c, y, name, es, fields) => {
        const x = X(c);
        card(s, x, y, tw, th, `tabla-${name}`);
        s.addShape(pres.ShapeType.roundRect, { x, y, w: tw, h: 0.4, rectRadius: 0.14, fill: { color: C.ink }, objectName: `cabecera-${name}` });
        s.addShape(pres.ShapeType.rect, { x, y: y + 0.24, w: tw, h: 0.16, fill: { color: C.ink }, objectName: `cabecera-base-${name}` });
        T(s, name, { x: x + 0.18, y, w: tw - 0.36, h: 0.4, fontFace: MONO, fontSize: 11, bold: true, color: C.white, valign: "middle" });
        T(s, es, { x: x + 0.18, y, w: tw - 0.36, h: 0.4, fontSize: 10, color: C.mint, align: "right", valign: "middle", transparency: 15 });
        const runs = [];
        fields.forEach(([f, tag], i) => {
            runs.push({ text: f, options: { color: C.text } });
            runs.push({ text: tag ? "  " + tag : "", options: { fontFace: "Inter", color: C.green, bold: true, fontSize: 9, breakLine: i < fields.length - 1 } });
        });
        T(s, runs, { x: x + 0.18, y: y + 0.5, w: tw - 0.3, h: th - 0.56, fontFace: MONO, fontSize: 9.5, valign: "top", lineSpacingMultiple: 1.15 });
    };
    tabla(0, yTop, "users", "Usuarios", [["email", "único"], ["role", "admin o docente"], ["institutional_id", "único"], ["is_active", ""]]);
    tabla(1, yTop, "academic_periods", "Períodos", [["code", "único"], ["start_date, end_date", ""], ["status", "planificado,"], ["", "abierto o cerrado"]]);
    tabla(2, yTop, "subjects", "Asignaturas", [["code", "único"], ["name", ""], ["is_active", ""]]);
    tabla(3, yTop, "students", "Estudiantes", [["institutional_id", "único"], ["last_names", ""], ["first_names", ""], ["email", "opcional"]]);
    tabla(0, yBot, "audit_logs", "Auditoría", [["user_id, enrollment_id", ""], ["operation", "registro/corrección"], ["old / new_values", ""], ["reason", "motivo"]]);
    tabla(1, yBot, "course_offerings", "Ofertas", [["nrc", "único por período"], ["academic_period_id", ""], ["subject_id, teacher_id", ""], ["classroom, campus", "opcional"]]);
    tabla(2, yBot, "enrollments", "Matrículas", [["student_id", ""], ["course_offering_id", ""], ["", "sin repetir la pareja"]]);
    tabla(3, yBot, "grades", "Notas", [["enrollment_id", "único"], ["partial_1, 2, 3", "0 a 20"], ["DECIMAL(5,2)", "vacío = pendiente"], ["version", ""]]);
    // Relaciones con su cardinalidad escrita
    const cxm = (c, d = 0) => X(c) + tw / 2 + d, yb = yTop + th, yt = yBot;
    const rel = (x, y, top, bottom) => {
        const w = 0.78, h = 0.42;
        s.addShape(pres.ShapeType.roundRect, { x: x - w / 2, y: y - h / 2, w, h, rectRadius: 0.08, fill: { color: C.white }, line: { color: C.green, width: 0.75 }, objectName: `relacion-${top}-${bottom}` });
        T(s, [{ text: top, options: { breakLine: true } }, { text: bottom }], { x: x - w / 2, y: y - h / 2, w, h,
            fontSize: 8.5, bold: true, color: C.green, align: "center", valign: "middle", lineSpacingMultiple: 0.9 });
    };
    const ym = (yb + yt) / 2;
    line(s, cxm(0), yb, cxm(0), yt);                   rel(cxm(0), ym, "1 a", "muchos");      // users → audit_logs
    line(s, cxm(0, 0.55), yb, cxm(1, -0.6), yt);       rel((cxm(0, 0.55) + cxm(1, -0.6)) / 2, ym, "1 a", "muchos"); // users → course_offerings
    line(s, cxm(1), yb, cxm(1), yt);                   rel(cxm(1), ym, "1 a", "muchos");      // academic_periods → course_offerings
    line(s, cxm(2, -0.55), yb, cxm(1, 0.6), yt);       rel((cxm(2, -0.55) + cxm(1, 0.6)) / 2, ym, "1 a", "muchos"); // subjects → course_offerings
    line(s, cxm(3, -0.55), yb, cxm(2, 0.55), yt);      rel((cxm(3, -0.55) + cxm(2, 0.55)) / 2, ym, "1 a", "muchos"); // students → enrollments
    const yh = yBot + 0.95;
    line(s, X(1) + tw, yh, X(2), yh);                  rel((X(1) + tw + X(2)) / 2, yh, "1 a", "muchos"); // course_offerings → enrollments
    line(s, X(2) + tw, yh, X(3), yh);                  rel((X(2) + tw + X(3)) / 2, yh, "1 a", "0 o 1");  // enrollments → grades
    const yr = yBot + th + 0.25;
    line(s, cxm(2), yBot + th, cxm(2), yr); line(s, cxm(0), yr, cxm(2), yr); line(s, cxm(0), yBot + th, cxm(0), yr);
    rel((cxm(0) + cxm(2)) / 2, yr, "1 a", "muchos");     // enrollments → audit_logs
    // Nota de muchos a muchos y reglas generales
    T(s, [
        { text: "Estudiantes y Ofertas: muchos a muchos", options: { bold: true, color: C.green } },
        { text: " (lo resuelve Matrículas)", options: { color: C.muted, breakLine: true } },
        { text: "No se borra un dato en uso · ", options: { color: C.muted } },
        { text: "total y estado se calculan, no se guardan", options: { color: C.muted } },
    ], { x: 7.2, y: 1.58, w: W - M - 7.2, h: 0.62, fontSize: 11.5, align: "right", valign: "middle" });
    s.addNotes("Son ocho tablas. Un período tiene muchas ofertas, cada oferta es un NRC con su asignatura y su docente. "
        + "Estudiantes y ofertas tienen una relación de muchos a muchos, que se resuelve con la tabla de matrículas: un estudiante no puede matricularse dos veces en el mismo NRC. "
        + "Cada matrícula tiene cero o un registro de notas, y cada nota acepta de 0 a 20. No se borra nada que esté en uso, y el total y el estado se calculan siempre desde los parciales.");
}

// 6. CALIDAD Y DESPLIEGUE ------------------------------------------------------------------
{
    const s = contenido("05 · CALIDAD Y DESPLIEGUE", "Probado en dos bases de datos y publicado", "PROGRAMACIÓN · DESPLIEGUE");
    // Flujo de publicación
    const pasos = [["LuCode", "Código", "rama main"], ["LuFlaskConical", "Pruebas", "automáticas"], ["LuCloudUpload", "Publicación", "rama deploy"], ["LuServer", "Servidor", "cPanel + HTTPS"]];
    const nw = 1.42, ag = 0.3;
    pasos.forEach(([icon, head, sub], i) => {
        const x = M + i * (nw + ag), y = 2.45;
        card(s, x, y, nw, 1.62, `paso-${i + 1}`, i === 3 ? C.ink : C.white);
        iconBox(s, icon, x + (nw - 0.6) / 2, y + 0.2, 0.6, head, i === 3 ? "0F2A1E" : C.pale, i === 3 ? "lima" : "verde");
        T(s, head, { x, y: y + 0.88, w: nw, h: 0.34, fontFace: HEAD, fontSize: 15, bold: true, color: i === 3 ? C.lime : C.text, align: "center" });
        T(s, sub, { x, y: y + 1.2, w: nw, h: 0.3, fontSize: 11, color: i === 3 ? C.mint : C.muted, align: "center" });
        if (i < 3) flecha(s, x + nw + 0.02, y + 0.66, ag - 0.04);
    });
    [["LuShieldCheck", "Cada regla tiene su prueba", "42,00 aprueba, 41,99 reprueba, docente ajeno bloqueado."],
     ["LuLock", "Publicación segura", "Solo la carpeta pública es visible; conexión cifrada (HTTPS)."]].forEach(([icon, head, body], i) => {
        const y = 4.4 + i * 1.05;
        iconBox(s, icon, M, y, 0.62, head);
        T(s, head, { x: M + 0.85, y: y - 0.02, w: 6.0, h: 0.36, fontFace: HEAD, fontSize: 19, bold: true, color: C.text });
        T(s, body, { x: M + 0.85, y: y + 0.36, w: 6.1, h: 0.4, fontSize: 14, color: C.muted });
    });
    const { px, pw } = panelDerecho(s, 2.45, 3.95);
    [["212", "pruebas automáticas"], ["652", "comprobaciones"], ["2", "bases de datos: PostgreSQL y MySQL 8"]].forEach(([n, label], i) => {
        const y = 2.7 + i * 1.0;
        T(s, n, { x: px + 0.4, y, w: 1.7, h: 0.8, fontFace: HEAD, fontSize: 44, bold: true, color: C.lime, valign: "middle" });
        T(s, label, { x: px + 2.1, y, w: pw - 2.4, h: 0.8, fontSize: 15, color: C.mint, valign: "middle" });
    });
    s.addImage({ path: ICON("LuCircleCheck", "lima"), x: px + 0.4, y: 5.83, w: 0.32, h: 0.32, altText: "Todas en verde" });
    T(s, "Resultado: todas en verde, 0 fallos", { x: px + 0.85, y: 5.79, w: pw - 1.2, h: 0.4, fontSize: 13, bold: true, color: C.mint, valign: "middle" });
    s.addNotes("Para asegurar la calidad escribí 212 pruebas automáticas con 652 comprobaciones: reglas de notas, permisos y casos límite como 42 y 41,99. "
        + "Se ejecutan en PostgreSQL, que uso en local, y en MySQL 8, que es el de producción. El código pasa por pruebas antes de publicarse en el servidor, "
        + "donde solo la carpeta pública es visible y la conexión va cifrada.");
}

// 7. SEPARADOR: DEMO -----------------------------------------------------------------------
{
    const s = pres.addSlide({ masterName: "SEPARADOR" });
    s.addText("06", { placeholder: "number" });
    s.addText("AHORA", { placeholder: "kicker" });
    s.addText("Demostración en vivo", { placeholder: "title" });
    s.addText("Del registro de notas al reporte de calificaciones", { placeholder: "subtitle" });
    s.addImage({ path: ICON("LuMonitorPlay", "lima"), x: W - M - 2.6, y: 2.3, w: 2.2, h: 2.2, transparency: 15, altText: "Demostración" });
    s.addNotes("Ahora lo muestro funcionando: entro como administrador y como docente, registro los tres parciales, "
        + "vemos el cálculo y el estado, intento una operación no permitida y termino con el reporte. [Cambiar a la pestaña del sistema].");
}

// 8. ANEXO: CONCURRENCIA ------------------------------------------------------------------
{
    const s = contenido("ANEXO · CONCURRENCIA", "¿Y si dos personas editan la misma nota a la vez?", "CONCURRENCIA");
    const sw = (W - 2 * M - 2 * 0.45) / 3, sy = 2.4, sh = 2.75;
    const nota = (x, y, w, valor, sub, dark) => {
        s.addShape(pres.ShapeType.roundRect, { x, y, w, h: 0.62, rectRadius: 0.1, fill: { color: dark ? C.ink : C.pale }, objectName: `nota-${valor}` });
        T(s, [{ text: valor, options: { fontFace: HEAD, fontSize: 18, bold: true, color: dark ? C.lime : C.green } },
              { text: "   " + sub, options: { fontFace: MONO, fontSize: 10, color: dark ? C.mint : C.muted } }],
          { x, y, w, h: 0.62, align: "center", valign: "middle" });
    };
    const pasos = [
        { n: "1", head: "Los dos abren la nota", body: "Docente y administrador ven la misma nota.", draw: (x) => {
            ["Docente", "Admin"].forEach((p, j) => {
                const ix = x + 0.55 + j * 1.75;
                iconBox(s, "LuUser", ix, sy + 1.08, 0.5, p);
                T(s, p, { x: ix - 0.33, y: sy + 1.6, w: 1.15, h: 0.3, fontSize: 10.5, color: C.muted, align: "center" });
            });
            nota(x + 0.3, sy + 2.0, sw - 0.6, "15,00", "versión 3");
        } },
        { n: "2", head: "El docente guarda 16,00", body: "El sistema la acepta y sube la versión.", draw: (x) => {
            iconBox(s, "LuSave", x + (sw - 0.62) / 2, sy + 1.2, 0.62, "Guardar");
            nota(x + 0.3, sy + 2.0, sw - 0.6, "16,00", "versión 4");
        } },
        { n: "3", head: "El admin guarda 14,00", body: "Pero su copia era la versión 3: el sistema lo detiene.", draw: (x) => {
            iconBox(s, "LuCircleAlert", x + (sw - 0.62) / 2, sy + 1.2, 0.62, "Aviso", C.ink, "lima");
            nota(x + 0.3, sy + 2.0, sw - 0.6, "Aviso", "la nota cambió, revise", true);
        } },
    ];
    pasos.forEach((p, i) => {
        const x = M + i * (sw + 0.45);
        card(s, x, sy, sw, sh, `paso-${p.n}`);
        s.addShape(pres.ShapeType.ellipse, { x: x + 0.25, y: sy + 0.25, w: 0.5, h: 0.5, fill: { color: C.ink }, objectName: `numero-${p.n}` });
        T(s, p.n, { x: x + 0.25, y: sy + 0.25, w: 0.5, h: 0.5, fontFace: HEAD, fontSize: 18, bold: true, color: C.lime, align: "center", valign: "middle" });
        T(s, p.head, { x: x + 0.9, y: sy + 0.2, w: sw - 1.05, h: 0.4, fontFace: HEAD, fontSize: 15, bold: true, color: C.text, valign: "middle" });
        T(s, p.body, { x: x + 0.9, y: sy + 0.6, w: sw - 1.05, h: 0.45, fontSize: 11.5, color: C.muted, valign: "top" });
        p.draw(x);
        if (i < 2) flecha(s, x + sw + 0.07, sy + sh / 2 - 0.15, 0.31);
    });
    // Tres garantías en lenguaje simple
    s.addShape(pres.ShapeType.roundRect, { x: M, y: 5.4, w: W - 2 * M, h: 1.2, rectRadius: 0.14, fill: { color: C.ink }, objectName: "garantias" });
    const gw = (W - 2 * M) / 3;
    [["LuCopyX", "Sin duplicados", "no se matricula dos veces"],
     ["LuHistory", "Sin cambios perdidos", "cada nota tiene su versión"],
     ["LuCircleCheck", "Todo queda registrado", "nota y auditoría se guardan juntas"]].forEach(([icon, head, body], i) => {
        const x = M + i * gw + 0.35;
        s.addImage({ path: ICON(icon, "lima"), x, y: 5.68, w: 0.5, h: 0.5, altText: head });
        T(s, head, { x: x + 0.7, y: 5.55, w: gw - 1.15, h: 0.4, fontFace: HEAD, fontSize: 16, bold: true, color: C.lime, valign: "middle" });
        T(s, body, { x: x + 0.7, y: 5.93, w: gw - 1.15, h: 0.4, fontSize: 12, color: C.mint, valign: "top" });
    });
    s.addNotes("Solo si preguntan. Imaginemos que el docente y el administrador abren la misma nota al mismo tiempo; los dos ven la versión 3. "
        + "El docente guarda primero: la nota pasa a versión 4. Cuando el administrador intenta guardar, su copia todavía dice versión 3, "
        + "así que el sistema lo detiene y le avisa que la nota cambió. Nadie borra el trabajo de otro sin darse cuenta. "
        + "Si preguntan el nombre técnico: es bloqueo optimista; además la fila se bloquea durante el guardado con lockForUpdate, "
        + "los duplicados los impide una restricción UNIQUE, y la auditoría se guarda en la misma transacción.");
}

// 9. ANEXO: DOCENCIA ----------------------------------------------------------------------
{
    const s = contenido("ANEXO · DOCENCIA", "De este proyecto al aula: ABP y APS", "DOCENCIA UNIVERSITARIA");
    const cw = (W - 2 * M - 2 * 0.35) / 3;
    [["LuPuzzle", "ABP", "Aprendizaje Basado en Problemas", "Parto de un problema real: ¿cómo calificar sin errores? Nota vacía frente a cero y el caso 41,99."],
     ["LuLightbulb", "APS", "Aprendizaje Basado en Soluciones", "Los estudiantes construyen y defienden su solución por incrementos, con Kanban."],
     ["LuTarget", "EVALUACIÓN", "Evidencia verificable", "Pruebas automáticas, entregas por tarea y defensa técnica de las decisiones."]].forEach(([icon, k, head, body], i) => {
        const x = M + i * (cw + 0.35), y = 2.5;
        card(s, x, y, cw, 3.4, `docencia-${i + 1}`);
        iconBox(s, icon, x + 0.35, y + 0.35, 0.72, head);
        T(s, k, { x: x + 1.3, y: y + 0.35, w: cw - 1.6, h: 0.72, fontFace: MONO, fontSize: 13, bold: true, color: C.green, valign: "middle" });
        T(s, head, { x: x + 0.35, y: y + 1.3, w: cw - 0.7, h: 0.85, fontFace: HEAD, fontSize: 20, bold: true, color: C.text, valign: "top" });
        T(s, body, { x: x + 0.35, y: y + 2.15, w: cw - 0.7, h: 1.1, fontSize: 14, color: C.muted, valign: "top", lineSpacingMultiple: 1.15 });
    });
    s.addNotes("Solo si preguntan cómo lo enseñaría. Con ABP parto de un problema real, como calificar sin errores: por qué una nota vacía no es un cero o por qué 41,99 reprueba aunque el promedio muestre 14. "
        + "Con aprendizaje basado en soluciones, los estudiantes construyen y defienden su propia solución por incrementos, como trabajé yo. "
        + "Y la evaluación se apoya en evidencia: pruebas, entregas por tarea y defensa técnica.");
}

// 10. CIERRE -------------------------------------------------------------------------------
{
    const s = pres.addSlide({ masterName: "SEPARADOR" });
    s.addText("", { placeholder: "number" });
    s.addText("CIERRE", { placeholder: "kicker" });
    s.addText("Gracias. ¿Preguntas?", { placeholder: "title" });
    s.addText("Arquitectura, decisiones y pruebas disponibles para revisión.", { placeholder: "subtitle" });
    s.addShape(pres.ShapeType.roundRect, { x: W - M - 2.9, y: 2.15, w: 2.9, h: 3.35, rectRadius: 0.16, fill: { color: C.white }, objectName: "tarjeta-qr" });
    s.addImage({ path: R("qr-web.png"), x: W - M - 2.55, y: 2.4, w: 2.2, h: 2.2, altText: "Código QR de dc.persontechnologys.com" });
    T(s, WEB, { x: W - M - 2.9, y: 4.75, w: 2.9, h: 0.5, fontFace: MONO, fontSize: 11, bold: true, color: C.text, align: "center", valign: "middle" });
    s.addNotes("En resumen: reglas de notas claras, datos protegidos por la propia base de datos, pruebas en dos bases de datos y publicación segura. "
        + "Gracias por su atención; quedo atento a sus preguntas.");
}

numerar();

(async () => {
    await pres.writeFile({ fileName: OUT });
    const { applyTheme } = require(process.env.PPTX_SKILL + "/scripts/apply_theme.js");
    await applyTheme(OUT, THEME);
    console.log("OK", OUT);
})();
