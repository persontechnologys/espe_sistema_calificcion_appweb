// Iconos Lucide (react-icons/lu) rasterizados a PNG de 256 px en recursos/iconos/.
const fs = require("fs"), path = require("path");
const React = require("react"), { renderToStaticMarkup } = require("react-dom/server");
const sharp = require("sharp");
const lu = require("react-icons/lu");

const OUT = path.join(__dirname, "recursos", "iconos");
fs.mkdirSync(OUT, { recursive: true });
const ICONS = ["LuGraduationCap", "LuLayers", "LuDatabase", "LuShieldCheck", "LuGitBranch", "LuServer",
    "LuWorkflow", "LuCpu", "LuGlobe", "LuSparkles", "LuMonitorPlay", "LuMessageCircleQuestion",
    // Presentación
    "LuSquareKanban", "LuUsers", "LuUserCog", "LuLock", "LuCalculator", "LuBan", "LuLightbulb", "LuPuzzle",
    "LuRocket", "LuFlaskConical", "LuTarget", "LuHistory", "LuCircleCheck", "LuCircleX", "LuBookOpen", "LuSchool",
    "LuUser", "LuSignpost", "LuClipboardCheck", "LuMonitor", "LuCopyX", "LuSave", "LuCircleAlert", "LuCode", "LuCloudUpload", "LuFileText"];
const COLORS = { verde: "#0D713D", lima: "#A8FF53", blanco: "#FFFFFF" };

(async () => {
    for (const name of ICONS) {
        if (!lu[name]) { console.log("no existe", name); continue; }
        for (const [cname, color] of Object.entries(COLORS)) {
            const svg = renderToStaticMarkup(React.createElement(lu[name], { size: 256, color, strokeWidth: 1.75 }));
            await sharp(Buffer.from(svg)).png().toFile(path.join(OUT, `${name}-${cname}.png`));
        }
    }
    console.log("iconos:", fs.readdirSync(OUT).length);
})();
