#!/usr/bin/env python3
"""Extract translatable strings (text domain beleon-tours) from the theme's PHP files.
Usage: python3 tools/extract-strings.py > languages/strings.json"""
import json, os, re, sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
STR = r"'((?:[^'\\]|\\.)*)'"
PATTERNS = [
    (re.compile(r"\b(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*" + STR + r"\s*,\s*'beleon-tours'"), 'single'),
    (re.compile(r"\b(?:_x|esc_html_x|esc_attr_x)\(\s*" + STR + r"\s*,\s*" + STR + r"\s*,\s*'beleon-tours'"), 'ctx'),
    (re.compile(r"\b_n\(\s*" + STR + r"\s*,\s*" + STR + r"\s*,[^,]+,\s*'beleon-tours'"), 'plural'),
]
unesc = lambda s: s.replace("\\'", "'").replace('\\\\', '\\')
out = {}
for dp, dn, fn in os.walk(ROOT):
    if '.git' in dp or 'tools' in dp:
        continue
    for f in sorted(fn):
        if not f.endswith('.php'):
            continue
        src = open(os.path.join(dp, f), encoding='utf-8').read()
        for rx, kind in PATTERNS:
            for m in rx.finditer(src):
                if kind == 'single':
                    out.setdefault(unesc(m.group(1)), None)
                elif kind == 'ctx':
                    out.setdefault(unesc(m.group(2)) + '\x04' + unesc(m.group(1)), None)
                else:
                    out.setdefault(unesc(m.group(1)) + '\x00' + unesc(m.group(2)), None)
json.dump(sorted(out), sys.stdout, ensure_ascii=False, indent=0)
