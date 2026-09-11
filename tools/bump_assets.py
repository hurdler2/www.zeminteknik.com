# -*- coding: utf-8 -*-
"""css/zt.css, css/style.css ve js/zt.js bağlantılarına içerik özetine dayalı ?v=... ekler.

Hosting, CSS için 1 ay / JS için 1 gün önbellek başlığı gönderiyor; bu dosyalar her
değiştiğinde sürüm eki de değişsin ki ziyaretçilerin tarayıcısı eski kopyayı kullanmasın.
Kullanım (proje kökünde):  python tools/bump_assets.py
"""
import hashlib, io, os, re, sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
ASSETS = ["css/zt.css", "css/style.css", "js/zt.js"]
SKIP_DIRS = {"__MACOSX", ".git", "cgi-bin", "tools"}

def digest(rel):
    with open(os.path.join(ROOT, rel), "rb") as f:
        return hashlib.md5(f.read()).hexdigest()[:8]

versions = {os.path.basename(a): digest(a) for a in ASSETS}
pat = re.compile(r'((?:href|src)=")([^"]*?/?(?:' + "|".join(map(re.escape, versions)) + r'))(?:\?v=[0-9a-f]+)?(")')

changed = 0
for root, dirs, files in os.walk(ROOT):
    dirs[:] = [d for d in dirs if d not in SKIP_DIRS]
    for fn in files:
        if not fn.endswith(".html"):
            continue
        p = os.path.join(root, fn)
        s = io.open(p, encoding="utf-8").read()
        s2 = pat.sub(lambda m: f'{m.group(1)}{m.group(2)}?v={versions[os.path.basename(m.group(2))]}{m.group(3)}', s)
        if s2 != s:
            io.open(p, "w", encoding="utf-8", newline="\n").write(s2)
            changed += 1

print("sürümler:", versions, "| güncellenen sayfa:", changed)
