"""Fondos de la presentación (1920x1080) a partir de docs/fotos/.
Uso: python docs/presentacion/generar-fondos.py docs/fotos docs/presentacion/recursos
"""
import sys
from PIL import Image, ImageDraw, ImageFilter, ImageOps

src, out = sys.argv[1], sys.argv[2]
W, H, PX = 1920, 1080, 1920 / 13.333   # píxeles por pulgada
INK = (7, 26, 18)          # verde casi negro
GREEN = (13, 113, 61)      # verde del logo ESPE

# Logo sin fondo: relleno desde la esquina, solo el fondo conectado (no perfora el escudo).
logo = Image.open(f"{src}/espe_logo.jpeg").convert("RGB")
lw, lh = logo.size
work = Image.new("RGB", (lw + 2, lh + 2), logo.getpixel((0, 0))); work.paste(logo, (1, 1))
ImageDraw.floodfill(work, (0, 0), (255, 0, 255), thresh=28)
alpha = Image.new("L", (lw, lh), 255); wp, ap = work.load(), alpha.load()
for y in range(lh):
    for x in range(lw):
        if wp[x + 1, y + 1] == (255, 0, 255): ap[x, y] = 0
logo = logo.convert("RGBA"); logo.putalpha(alpha.filter(ImageFilter.GaussianBlur(0.6)))
logo.save(f"{out}/espe-logo.png", optimize=True)
RATIO = lw / lh

photo = Image.open(f"{src}/fondo_edificio_espe.jpeg").convert("L")
pw, ph = photo.size; th = int(pw * 9 / 16); top = int((ph - th) * 0.3)
photo = photo.crop((0, top, pw, top + th)).resize((W, H), Image.LANCZOS)

def duotone(dark, light):
    return ImageOps.colorize(ImageOps.autocontrast(photo, cutoff=1), dark, light).convert("RGBA")

def mask_h(x0, x1, a0, a1):
    """Máscara horizontal: opacidad a0 hasta x0, sube lineal hasta a1 en x1 (fracciones del ancho)."""
    m = Image.new("L", (W, H)); d = m.load()
    row = [int(255 * (a0 if x < x0 * W else a1 if x > x1 * W else a0 + (a1 - a0) * (x - x0 * W) / ((x1 - x0) * W))) for x in range(W)]
    return Image.frombytes("L", (W, H), bytes(row) * H)

def grid(img, color, step=0.5):
    layer = Image.new("RGBA", (W, H), (0, 0, 0, 0)); d = ImageDraw.Draw(layer)
    s = step * PX; x = 0.0
    while x < W: d.line([(x, 0), (x, H)], fill=color, width=1); x += s
    y = 0.0
    while y < H: d.line([(0, y), (W, y)], fill=color, width=1); y += s
    img.alpha_composite(layer)

def pill_logo(img, x, y, h):
    """Logo pequeño en una píldora blanca ajustada (fondos oscuros)."""
    d = ImageDraw.Draw(img); pad_x, pad_y = 0.12, 0.07; w = h * RATIO
    d.rounded_rectangle([x * PX, y * PX, (x + w + 2 * pad_x) * PX, (y + h + 2 * pad_y) * PX], radius=0.08 * PX, fill=(255, 255, 255, 255))
    img.alpha_composite(logo.resize((int(w * PX), int(h * PX)), Image.LANCZOS), (int((x + pad_x) * PX), int((y + pad_y) * PX)))

def plain_logo(img, x, y, h):
    img.alpha_composite(logo.resize((int(h * RATIO * PX), int(h * PX)), Image.LANCZOS), (int(x * PX), int(y * PX)))

def save(img, name):
    img.convert("RGB").save(f"{out}/{name}", quality=88, optimize=True, progressive=True)

# Portada: fondo tinta; la foto en duotono verde, ampliada y desplazada para que el edificio
# central quede en la mitad derecha, se funde hacia la izquierda.
SCALE, BUILDING_X, TARGET_X = 1.2, 0.47, 0.72
big = duotone(INK, (70, 150, 108)).resize((int(W * SCALE), int(H * SCALE)), Image.LANCZOS)
shift_x = int(TARGET_X * W - BUILDING_X * big.width); shift_y = int((H - big.height) * 0.35)
layer = Image.new("RGBA", (W, H), INK + (255,)); layer.paste(big, (shift_x, shift_y))
cover = Image.new("RGBA", (W, H), INK + (255,))
cover.paste(layer, (0, 0), mask_h(0.40, 0.72, 0.0, 0.72))
grid(cover, (255, 255, 255, 10))
pill_logo(cover, 0.6, 0.5, 0.38); save(cover, "fondo-portada.jpg")

# Separador: foto completa muy tenue sobre tinta.
sec = Image.new("RGBA", (W, H), INK + (255,))
sec.paste(duotone(INK, (40, 110, 78)), (0, 0), Image.new("L", (W, H), 60))
grid(sec, (255, 255, 255, 10))
pill_logo(sec, 0.6, 0.5, 0.38); save(sec, "fondo-separador.jpg")

# Contenido: claro, foto casi imperceptible a la derecha y cuadrícula de plano técnico.
light = Image.new("RGBA", (W, H), (246, 248, 247, 255))
light.paste(duotone((120, 140, 130), (246, 248, 247)), (0, 0), mask_h(0.35, 1.0, 0.0, 0.16))
grid(light, (13, 113, 61, 14))
plain_logo(light, 13.333 - 0.6 - 0.3 * RATIO, 0.42, 0.3); save(light, "fondo-contenido.jpg")
print("ok")
