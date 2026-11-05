// Plantilla de la presentación de la entrevista técnica (16:9, 13.333" x 7.5").
// Requiere los fondos (generar-fondos.py) y los iconos (generar-iconos.js) en recursos/.
// Uso: NODE_PATH=<node_modules con pptxgenjs> PPTX_SKILL=<herramientas pptx> node docs/presentacion/generar-plantilla.js
const path = require("path");
const pptxgen = require("pptxgenjs");

const R = (f) => path.join(__dirname, "recursos", f);
const ICON = (name, color) => R(`iconos/${name}-${color}.png`);
const OUT = path.join(__dirname, "plantilla.pptx");

// Concepto: "arquitectura de software". Tinta verde casi negra + verde ESPE + lima de dc.persontechnologys.com.
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
    pale: "E3F2EA", white: "FFFFFF", border: "DCE5E0" };
const MONO = "JetBrains Mono";

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

// Pie común del diseño: sitio web. El nombre y el perfil se escriben en firmar() como una sola línea con dos estilos.
const pie = (color, withNumber) => [
    { text: { text: WEB, options: { x: W - M - (withNumber ? 4.75 : 3.6), y: H - 0.52, w: 3.6, h: 0.3, fontFace: MONO, fontSize: 10, color, align: "right", valign: "middle", margin: 0 } } },
];
// Pie: nombre en negrita (12 pt) y perfil (10.5 pt) en la misma línea. Número "02 / 05" en las de contenido.
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

// 1. PORTADA ------------------------------------------------------------------
pres.defineSlideMaster({
    title: "PORTADA",
    background: { path: R("fondo-portada.jpg") },
    objects: [
        ...pie(C.mint, false),
        ph("kicker", "body", "// ENTREVISTA TÉCNICA", { x: M, y: 1.75, w: 8, h: 0.35, fontFace: MONO, fontSize: 13, bold: true, color: C.lime }),
        ph("title", "title", "Título", { x: M, y: 2.2, w: 8, h: 1.75, fontSize: 54, bold: true, color: C.white, align: "left", valign: "top", lineSpacingMultiple: 0.95 }),
        ph("subtitle", "body", "Subtítulo", { x: M, y: 4.05, w: 9, h: 0.5, fontSize: 19, color: C.mint, align: "left", valign: "top" }),
        ph("presenter", "body", "Nombre", { x: M, y: 5.75, w: 7, h: 0.4, fontFace: "Space Grotesk", fontSize: 18, bold: true, color: C.white }),
        ph("meta", "body", "Cargo · Fecha", { x: M, y: 6.12, w: 8, h: 0.35, fontSize: 13, color: C.mint }),
    ],
});

// 2. CONTENIDO -----------------------------------------------------------------
pres.defineSlideMaster({
    title: "CONTENIDO",
    background: { path: R("fondo-contenido.jpg") },
    margin: [0.4, M, 0.8, M],
    objects: [
        { image: { path: ICON("LuGraduationCap", "verde"), x: M, y: 0.44, w: 0.28, h: 0.28 } },
        { text: { text: PROYECTO, options: { x: M + 0.4, y: 0.42, w: 8, h: 0.32, fontFace: "Inter", fontSize: 11, color: C.muted, valign: "middle", margin: 0 } } },
        ...pie(C.muted, true),
        ph("section", "body", "00 / SECCIÓN", { x: M, y: 1.08, w: 8, h: 0.32, fontFace: MONO, fontSize: 12, bold: true, color: C.green }),
        ph("title", "title", "Título de la diapositiva", { x: M, y: 1.42, w: W - 2 * M, h: 0.8, fontSize: 34, bold: true, color: C.text, align: "left", valign: "top" }),
    ],
});

// 3. SEPARADOR -----------------------------------------------------------------
pres.defineSlideMaster({
    title: "SEPARADOR",
    background: { path: R("fondo-separador.jpg") },
    objects: [
        ...pie(C.mint, false),
        ph("number", "body", "00", { x: M, y: 1.55, w: 4, h: 1.5, fontFace: "Space Grotesk", fontSize: 110, bold: true, color: C.lime, valign: "bottom" }),
        ph("kicker", "body", "// SECCIÓN", { x: M, y: 3.3, w: 8, h: 0.35, fontFace: MONO, fontSize: 13, bold: true, color: C.lime }),
        ph("title", "title", "Título", { x: M, y: 3.7, w: 8.5, h: 1.0, fontSize: 48, bold: true, color: C.white, align: "left", valign: "top" }),
        ph("subtitle", "body", "Subtítulo", { x: M, y: 4.75, w: 9, h: 0.7, fontSize: 19, color: C.mint, align: "left", valign: "top" }),
    ],
});

// Diapositivas de ejemplo --------------------------------------------------------
pres.addSection({ title: "Plantilla" });
const shadow = () => ({ type: "outer", color: "0F1F17", opacity: 0.08, blur: 12, offset: 3, angle: 90 });

// Portada
const s1 = pres.addSlide({ masterName: "PORTADA", sectionTitle: "Plantilla" });
s1.addText("// ENTREVISTA TÉCNICA · PROFESOR OCASIONAL 1 · DCCO-SS", { placeholder: "kicker" });
s1.addText("Sistema de Gestión Académica", { placeholder: "title" });
s1.addText(PROYECTO, { placeholder: "subtitle" });
s1.addText("Vilmer David Criollo Chanchicocha", { placeholder: "presenter" });
s1.addText("Universidad de las Fuerzas Armadas ESPE  ·  05 de noviembre de 2026", { placeholder: "meta" });
let cx = M;
["Laravel 13", "PHP 8.4", "PostgreSQL · MySQL 8", "cPanel + HTTPS"].forEach((t) => {
    const w = 0.32 + t.length * 0.098;
    s1.addShape(pres.ShapeType.roundRect, { x: cx, y: 4.95, w, h: 0.42, rectRadius: 0.21, fill: { color: C.ink, transparency: 30 }, line: { color: C.lime, width: 1 }, objectName: `chip-${t}` });
    s1.addText(t, { x: cx, y: 4.95, w, h: 0.42, fontFace: MONO, fontSize: 11, color: C.lime, align: "center", valign: "middle", margin: 0, isTextBox: true });
    cx += w + 0.15;
});

// Contenido A: tres tarjetas con icono
const s2 = pres.addSlide({ masterName: "CONTENIDO", sectionTitle: "Plantilla" });
s2.addText("03 / ARQUITECTURA", { placeholder: "section" });
s2.addText("Tarjetas con icono para ideas paralelas", { placeholder: "title" });
const cards = [
    ["LuLayers", "Capas claras", "Controladores delgados, Form Requests, Policies y servicios de dominio."],
    ["LuDatabase", "Integridad en la base", "Claves únicas, CHECK de 0 a 20 y ON DELETE RESTRICT."],
    ["LuShieldCheck", "Seguro por diseño", "Autorización en el servidor, CSRF, escape de salida y límite de intentos."],
];
const cw = (W - 2 * M - 2 * 0.35) / 3;
cards.forEach(([icon, head, body], i) => {
    const x = M + i * (cw + 0.35), y = 2.55;
    s2.addShape(pres.ShapeType.roundRect, { x, y, w: cw, h: 3.0, rectRadius: 0.14, fill: { color: C.white }, line: { color: C.border, width: 0.75 }, shadow: shadow(), objectName: `tarjeta-${i + 1}` });
    s2.addShape(pres.ShapeType.roundRect, { x: x + 0.35, y: y + 0.35, w: 0.72, h: 0.72, rectRadius: 0.16, fill: { color: C.pale }, objectName: `icono-fondo-${i + 1}` });
    s2.addImage({ path: ICON(icon, "verde"), x: x + 0.5, y: y + 0.5, w: 0.42, h: 0.42, altText: head });
    s2.addText(head, { x: x + 0.35, y: y + 1.3, w: cw - 0.7, h: 0.5, fontFace: "Space Grotesk", fontSize: 21, bold: true, color: C.text, margin: 0, isTextBox: true });
    s2.addText(body, { x: x + 0.35, y: y + 1.85, w: cw - 0.7, h: 1.4, fontSize: 15, color: C.muted, valign: "top", margin: 0, lineSpacingMultiple: 1.15, isTextBox: true });
});

// Contenido B: lista con iconos + panel oscuro de cifras
const s3 = pres.addSlide({ masterName: "CONTENIDO", sectionTitle: "Plantilla" });
s3.addText("05 / CALIDAD", { placeholder: "section" });
s3.addText("Lista a la izquierda, cifras a la derecha", { placeholder: "title" });
[["LuGitBranch", "Commits por funcionalidad", "Cada commit se relaciona con una tarea del tablero."],
 ["LuWorkflow", "Integración continua", "La suite corre en PostgreSQL y en MySQL 8."],
 ["LuServer", "Despliegue controlado", "Rama deploy, Document Root acotado y HTTPS en cPanel."]].forEach(([icon, head, body], i) => {
    const y = 2.55 + i * 1.2;
    s3.addShape(pres.ShapeType.roundRect, { x: M, y, w: 0.62, h: 0.62, rectRadius: 0.14, fill: { color: C.pale }, objectName: `fila-icono-${i + 1}` });
    s3.addImage({ path: ICON(icon, "verde"), x: M + 0.13, y: y + 0.13, w: 0.36, h: 0.36, altText: head });
    s3.addText(head, { x: M + 0.85, y: y - 0.02, w: 5.9, h: 0.36, fontFace: "Space Grotesk", fontSize: 19, bold: true, color: C.text, margin: 0, isTextBox: true });
    s3.addText(body, { x: M + 0.85, y: y + 0.36, w: 5.9, h: 0.45, fontSize: 15, color: C.muted, margin: 0, isTextBox: true });
});
const px = 7.65, pw = W - M - px;
s3.addShape(pres.ShapeType.roundRect, { x: px, y: 2.45, w: pw, h: 3.75, rectRadius: 0.16, fill: { color: C.ink }, shadow: shadow(), objectName: "panel-cifras" });
[["212", "pruebas automatizadas"], ["2", "motores de base de datos"], ["0", "consultas N+1"]].forEach(([n, label], i) => {
    const y = 2.75 + i * 1.12;
    s3.addText(n, { x: px + 0.4, y, w: 1.6, h: 0.8, fontFace: "Space Grotesk", fontSize: 44, bold: true, color: C.lime, valign: "middle", margin: 0, isTextBox: true });
    s3.addText(label, { x: px + 2.0, y, w: pw - 2.3, h: 0.8, fontSize: 15, color: C.mint, valign: "middle", margin: 0, isTextBox: true });
});

// Separador
const s4 = pres.addSlide({ masterName: "SEPARADOR", sectionTitle: "Plantilla" });
s4.addText("06", { placeholder: "number" });
s4.addText("// AHORA", { placeholder: "kicker" });
s4.addText("Demostración en vivo", { placeholder: "title" });
s4.addText("Del registro de notas al reporte de calificaciones", { placeholder: "subtitle" });
s4.addImage({ path: ICON("LuMonitorPlay", "lima"), x: W - M - 2.6, y: 2.3, w: 2.2, h: 2.2, transparency: 15, altText: "Demostración" });

// Cierre con QR del sitio web
const s5 = pres.addSlide({ masterName: "SEPARADOR", sectionTitle: "Plantilla" });
s5.addText("", { placeholder: "number" });
s5.addText("// CIERRE", { placeholder: "kicker" });
s5.addText("Gracias. ¿Preguntas?", { placeholder: "title" });
s5.addText("Arquitectura, decisiones y pruebas disponibles para revisión.", { placeholder: "subtitle" });
s5.addShape(pres.ShapeType.roundRect, { x: W - M - 2.9, y: 2.15, w: 2.9, h: 3.35, rectRadius: 0.16, fill: { color: C.white }, objectName: "tarjeta-qr" });
s5.addImage({ path: R("qr-web.png"), x: W - M - 2.55, y: 2.4, w: 2.2, h: 2.2, altText: "Código QR de dc.persontechnologys.com" });
s5.addText(WEB, { x: W - M - 2.9, y: 4.75, w: 2.9, h: 0.5, fontFace: MONO, fontSize: 11, bold: true, color: C.text, align: "center", valign: "middle", margin: 0, isTextBox: true });

numerar();

(async () => {
    await pres.writeFile({ fileName: OUT });
    const { applyTheme } = require(process.env.PPTX_SKILL + "/scripts/apply_theme.js");
    await applyTheme(OUT, THEME);
    console.log("OK", OUT);
})();
