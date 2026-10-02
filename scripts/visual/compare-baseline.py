#!/usr/bin/env python3
"""Compare two baseline.mjs runs. Normalises ?v=… cache busters and (optionally) an asset URL prefix map.
   compare-baseline.py <before> <after> [--map OLD=NEW ...]
Prints every difference in rendered HTML, requested URLs (status/set), chrome boxes, console errors."""
import json, re, sys, os, difflib
before, after = sys.argv[1], sys.argv[2]
maps = [a.split('=', 1) for a in sys.argv[3:] if '=' in a and not a.startswith('--')]
def norm(text):
    text = re.sub(r'\?v=\d+', '', text)                                   # cache busters
    text = re.sub(r'(name="_csrf" value=")[0-9a-f]{64}', r'\1X', text)      # per-session token
    text = re.sub(r'(name="_ts" value=")[^"]+', r'\1X', text)               # signed timestamp
    return text
def apply_maps(text):
    for old, new in maps: text = text.replace(old, new)
    return text
bad = 0
rb = json.load(open(f'{before}/report.json')); ra = json.load(open(f'{after}/report.json'))
for route in rb:
    a, b = rb[route], ra.get(route)
    if b is None: print('MISSING route', route); bad += 1; continue
    if a['status'] != b['status']: print(route, 'status', a['status'], '→', b['status']); bad += 1
    f = route.replace('/', '_')
    name = (re.sub(r'[^a-z0-9]+', '_', route, flags=re.I).strip('_') or 'home') + '.html'
    ha = norm(open(f'{before}/html/{name}').read()); hb = norm(open(f'{after}/html/{name}').read())
    if apply_maps(norm(ha)) != hb:
        d = list(difflib.unified_diff(apply_maps(ha).splitlines(), hb.splitlines(), lineterm='', n=0))
        print(route, 'HTML differs:', len(d)); print('\n'.join(d[:14])); bad += 1
    ignore = lambda rs: [r for r in rs if not re.search(r'unicons-\d+\.woff2', r)]  # unicode-range subsets load lazily
    blob = lambda r: re.sub(r'\?v=\d+', '', re.sub(r'blob:/[0-9a-f-]+', 'blob:/X', r))
    ra_req = sorted(blob(apply_maps(r)) for r in ignore(a['requests'])); rb_req = sorted(blob(r) for r in ignore(b['requests']))
    if ra_req != rb_req:
        print(route, 'requests differ:'); 
        for x in sorted(set(ra_req) - set(rb_req))[:8]: print('  -', x)
        for x in sorted(set(rb_req) - set(ra_req))[:8]: print('  +', x)
        bad += 1
    if a['errors'] != b['errors']: print(route, 'errors', a['errors'], '→', b['errors']); bad += 1
    for vp in a['boxes']:
        # 'overflow' of animated pages (home slides, 4ukraine) fluctuates with animation timing: not a layout signal
        noisy = route in ('/', '/home/_api/UI/terminal/Page/4ukraine/')
        keys = [k for k in a['boxes'][vp] if not (noisy and k == 'overflow')]
        if any(a['boxes'][vp][k] != b['boxes'][vp].get(k) for k in keys):
            diffs = {k: (a['boxes'][vp][k], b['boxes'][vp].get(k)) for k in keys if a['boxes'][vp][k] != b['boxes'][vp].get(k)}
            print(route, vp, 'boxes', diffs); bad += 1
print('IDENTICAL' if bad == 0 else f'{bad} difference group(s)')
sys.exit(1 if bad else 0)
