#!/usr/bin/env bash
# Build minified assets, translations and an installable zip.
#   tools/build.sh          -> assets/*.min.*, languages/el.*, dist/beleon-tours.zip
set -euo pipefail
cd "$(dirname "$0")/.."

npx --yes csso-cli@4.0.2 assets/css/main.css -o assets/css/main.min.css
npx --yes terser@5.31.0 assets/js/main.js -c -m --comments '/^!/' -o assets/js/main.min.js
python3 tools/build-i18n.py

rm -rf dist && mkdir -p dist
python3 - <<'PY'
import os, zipfile
skip_dirs = {'.git', 'dist', 'tools', 'node_modules'}
with zipfile.ZipFile('dist/beleon-tours.zip', 'w', zipfile.ZIP_DEFLATED) as z:
    for dp, dn, fn in os.walk('.'):
        dn[:] = sorted(d for d in dn if d not in skip_dirs)
        for f in sorted(fn):
            if f in ('.gitignore',):
                continue
            path = os.path.join(dp, f)
            z.write(path, os.path.join('beleon-tours', os.path.relpath(path, '.')))
PY
echo "Built dist/beleon-tours.zip ($(du -h dist/beleon-tours.zip | cut -f1))"
