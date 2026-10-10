"""Generate premium OG social preview image (1200x630) matching the site identity:
dark cosmic background, solar-system sun + planet + orbit rings, glass panel,
TechSupport branding. Pure PIL, no network calls."""
import math, random
from PIL import Image, ImageDraw, ImageFont, ImageFilter

W, H = 1200, 630
random.seed(42)

# Base deep-space gradient
img = Image.new("RGB", (W, H))
px = img.load()
for y in range(H):
    t = y / H
    r = int(3 + 8 * t)
    g = int(5 + 10 * t)
    b = int(12 + 22 * t)
    for x in range(W):
        px[x, y] = (r, g, b)

d = ImageDraw.Draw(img, "RGBA")

# Ambient washes (cyan/blue/violet tints)
def wash(cx, cy, rad, color, alpha):
    layer = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    dw = ImageDraw.Draw(layer)
    for i in range(rad, 0, -6):
        a = int(alpha * (1 - i / rad) ** 1.6)
        dw.ellipse([cx - i, cy - i, cx + i, cy + i], fill=color + (a,))
    img.alpha_composite(layer) if False else None
    return layer

# Simpler: draw concentric translucent ellipses directly
def glow(cx, cy, rad, rgb, peak):
    for i in range(rad, 0, -5):
        a = int(peak * (1 - i / rad) ** 2)
        d.ellipse([cx - i, cy - i * 0.85, cx + i, cy + i * 0.85], fill=rgb + (a,))

glow(880, 300, 320, (37, 99, 235), 46)
glow(200, 480, 260, (92, 124, 250), 30)
glow(600, 120, 200, (168, 85, 247), 26)

# Starfield
for _ in range(420):
    x = random.randrange(W)
    y = random.randrange(H)
    b = random.randrange(90, 230)
    r = random.randrange(1, 3)
    tint = random.choice([(255, 255, 255), (165, 243, 252), (147, 197, 253), (216, 180, 254)])
    f = b / 255
    d.ellipse([x - r, y - r, x + r, y + r], fill=tuple(int(c * f) for c in tint) + (b,))

# Grid lines (subtle cyber grid)
for gx in range(0, W, 50):
    d.line([(gx, 0), (gx, H)], fill=(255, 255, 255, 7))
for gy in range(0, H, 50):
    d.line([(0, gy), (W, gy)], fill=(255, 255, 255, 7))

# Sun (right side)
sun = (965, 295)
glow(*sun, 190, (253, 230, 138), 90)
d.ellipse([sun[0] - 62, sun[1] - 62, sun[0] + 62, sun[1] + 62], fill=(255, 251, 235, 255))
d.ellipse([sun[0] - 62, sun[1] - 62, sun[0] + 62, sun[1] + 62], outline=(253, 230, 138, 255), width=3)
d.ellipse([sun[0] - 78, sun[1] - 78, sun[0] + 78, sun[1] + 78], outline=(253, 230, 138, 110), width=2)

# Orbit rings around sun (elliptical)
for rx, ry, op in [(150, 52, 70), (210, 74, 50), (270, 96, 34)]:
    d.ellipse([sun[0] - rx, sun[1] - ry, sun[0] + rx, sun[1] + ry], outline=(148, 197, 255, op), width=2)

# Planets on orbits
planets = [(sun[0] - 150, sun[1] - 8, 14, (148, 163, 184)), (sun[0] + 190, sun[1] + 30, 20, (96, 165, 250)), (sun[0] + 60, sun[1] - 88, 11, (192, 132, 252))]
for x, y, r, col in planets:
    glow(x, y, int(r * 3.2), col, 60)
    d.ellipse([x - r, y - r, x + r, y + r], fill=col + (255,))
    d.ellipse([x - r, y - r, x + r, y + r], outline=(255, 255, 255, 160), width=1)
    d.ellipse([x - r, y - int(r * 0.55), x - int(r * 0.4), y - int(r * 0.2)], fill=(255, 255, 255, 120))

# Satellite nodes + connection lines
nodes = [(700, 180), (760, 430), (620, 330), (1080, 130), (1010, 470)]
for nx, ny in nodes:
    d.line([(nx, ny), sun], fill=(148, 163, 184, 70), width=1)
    d.ellipse([nx - 5, ny - 5, nx + 5, ny + 5], fill=(56, 189, 248, 255))
    d.ellipse([nx - 11, ny - 11, nx + 11, ny + 11], outline=(56, 189, 248, 120), width=1)

# Glass panel (left)
panel = [70, 110, 640, 520]
d.rounded_rectangle(panel, radius=28, fill=(10, 14, 24, 205), outline=(255, 255, 255, 60), width=2)
# top highlight
d.rounded_rectangle([70, 110, 640, 200], radius=28, fill=(255, 255, 255, 14))
d.rectangle([70, 160, 640, 520], fill=(10, 14, 24, 0))  # keep lower part clean
d.rounded_rectangle(panel, radius=28, outline=(255, 255, 255, 60), width=2)

# Logo mark (rounded square + shield-ish check)
d.rounded_rectangle([110, 150, 168, 208], radius=14, fill=(37, 99, 235, 255))
d.rounded_rectangle([110, 150, 168, 208], radius=14, outline=(255, 255, 255, 90), width=2)
d.text((124, 158), "TS", font=ImageFont.truetype("C:/Windows/Fonts/arialbd.ttf", 30), fill=(255, 255, 255, 255))

fb = ImageFont.truetype("C:/Windows/Fonts/arialbd.ttf", 30)
fm = ImageFont.truetype("C:/Windows/Fonts/arial.ttf", 19)
fh = ImageFont.truetype("C:/Windows/Fonts/arialbd.ttf", 54)
fs = ImageFont.truetype("C:/Windows/Fonts/arial.ttf", 26)
ft = ImageFont.truetype("C:/Windows/Fonts/arialbd.ttf", 20)
fc = ImageFont.truetype("C:/Windows/Fonts/arial.ttf", 18)

dd = ImageDraw.Draw(img)
dd.text((185, 152), "TechSupport", font=fb, fill=(255, 255, 255, 255))
dd.text((185, 184), "S O L U T I O N S", font=fm, fill=(103, 232, 249, 255))

dd.text((110, 240), "Secure Your Business.", font=fh, fill=(255, 255, 255, 255))
dd.text((110, 312), "Build Smarter Technology.", font=fh, fill=(125, 211, 252, 255))

dd.text((110, 392), "Enterprise IT Support  •  Cybersecurity  •  Cloud", font=fs, fill=(203, 213, 225, 255))

# Status pill
dd.rounded_rectangle([110, 440, 420, 478], radius=19, outline=(52, 211, 153, 255), width=2, fill=(6, 40, 28, 255))
dd.ellipse([126, 454, 138, 466], fill=(52, 211, 153, 255))
dd.text((148, 447), "SYSTEMS OPERATIONAL  •  24/7", font=ft, fill=(167, 243, 208, 255))

dd.text((110, 488), "muhibchy.github.io/Tech-Support", font=fc, fill=(100, 116, 139, 255))

img.save("public/og-cover.jpg", "JPEG", quality=88)
print("saved public/og-cover.jpg")
