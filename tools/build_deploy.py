# -*- coding: utf-8 -*-
"""Hosting'e yüklenecek paketi üretir: ../zeminteknik_yayin/ klasörü ve ../zeminteknik_yayin.zip

- Geliştirme artıklarını (.git, __MACOSX, .DS_Store, tools/, *.zip, README.md, .gitignore, boş cgi-bin) dışarıda bırakır.
- Zip içindeki Unix izinlerini klasör 755 / dosya 644 olarak yazar; Windows'ta üretilen zip'ler
  aksi halde cPanel'de 777/666 olarak açılır (güvenlik ve bazı sunucularda PHP 500 hatası).
Kullanım (proje kökünde):  python tools/build_deploy.py
"""
import io, os, shutil, sys, zipfile

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
PARENT = os.path.dirname(ROOT)
DST = os.path.join(PARENT, "zeminteknik_yayin")
ZIP = DST + ".zip"
SKIP_DIRS = {"__MACOSX", "cgi-bin", ".git", "tools"}
SKIP_FILES = {".DS_Store", "README.md", ".gitignore"}

def wanted(src, rel):
    if os.path.basename(src) in SKIP_FILES or src.lower().endswith(".zip"):
        return False
    if rel == "hizmetler/projeler/index.html" and not io.open(src, encoding="utf-8", errors="ignore").read().strip():
        return False          # içi boş, hatalı eski dosya
    return True

if os.path.isdir(DST):
    shutil.rmtree(DST)
count = 0
for root, dirs, files in os.walk(ROOT):
    dirs[:] = [d for d in dirs if d not in SKIP_DIRS]
    for f in files:
        src = os.path.join(root, f)
        rel = os.path.relpath(src, ROOT).replace("\\", "/")
        if not wanted(src, rel):
            continue
        dst = os.path.join(DST, rel)
        os.makedirs(os.path.dirname(dst), exist_ok=True)
        shutil.copy2(src, dst)
        count += 1

if os.path.exists(ZIP):
    try:
        os.remove(ZIP)
    except PermissionError:
        sys.exit("zeminteknik_yayin.zip başka bir programda açık; kapatıp tekrar çalıştırın.")

with zipfile.ZipFile(ZIP, "w", zipfile.ZIP_DEFLATED) as z:
    for root, dirs, files in os.walk(DST):
        dirs.sort(); files.sort()
        for d in dirs:
            zi = zipfile.ZipInfo(os.path.relpath(os.path.join(root, d), DST).replace("\\", "/") + "/")
            zi.external_attr = (0o40755 << 16) | 0x10
            z.writestr(zi, "")
        for f in files:
            p = os.path.join(root, f)
            zi = zipfile.ZipInfo.from_file(p, os.path.relpath(p, DST).replace("\\", "/"))
            zi.compress_type = zipfile.ZIP_DEFLATED
            zi.external_attr = 0o100644 << 16
            with open(p, "rb") as fh:
                z.writestr(zi, fh.read())

print(f"{count} dosya -> {ZIP} ({os.path.getsize(ZIP) // 1024 // 1024} MB), izinler: klasör 755 / dosya 644")
