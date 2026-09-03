#!/usr/bin/env python3
"""
Scans a JS card file for candidate user-facing strings to translate.

Heuristic, not authoritative -- flags string literals that *look* like
prose (contain a space, or are a capitalized standalone word) and are not
obviously code (CSS selectors/classes, object/data keys, units, URLs,
event names, console.* messages). Every candidate is printed with its line
number and surrounding line for a human to confirm or reject in one pass,
rather than re-reading the whole file hunting for them by eye.

Usage: python3 extract_strings.py <path/to/cardX.js>
"""
import re
import sys

STRING_RE = re.compile(r'''(['"])((?:\\.|(?!\1).)*)\1''')

# Cheap exclusion filters -- things that match "quoted text" syntactically
# but are code, not user-facing prose.
CODE_LOOKALIKE = re.compile(r'''
    ^[.#][\w-]              # CSS selector: .foo / #bar
    | ^[a-z][a-zA-Z0-9]*$   # bare camelCase/lowercase identifier, no space
    | ^[A-Z_][A-Z0-9_]*_[A-Z0-9_]*$  # true CONSTANT_CASE (has an underscore
                                     # between words, e.g. MY_CONST) -- a
                                     # single bare all-caps word like
                                     # 'STORMY' or 'FAIR' is deliberately
                                     # NOT excluded here, since that's
                                     # exactly as likely to be a real
                                     # display label (gauge/scale text is
                                     # often styled all-caps) as a code
                                     # constant, and this scanner is
                                     # recall-oriented by design -- a
                                     # missed real string is the expensive
                                     # failure, not an extra line to
                                     # glance at and skip.
    | ^\s*$                 # empty/whitespace only
    | ^[0-9.,\-+%°]+$       # pure number/unit
    | ^(px|rem|em|vh|vw|s|ms)$
    | ^(click|change|input|load|resize|keydown|keyup|mousedown|mouseup|touchstart|touchend|scroll)$
    | ^https?://            # URL
    | ^[./][\w./-]*$        # path-like
    | ^\#[0-9a-fA-F]{3,8}$   # hex color
    | ^rgba?\(              # css color function
    | ^[a-z-]+$             # kebab-case (css class / attr names) with no space
''', re.VERBOSE)

CONSOLE_LINE = re.compile(r'console\.(log|warn|error|debug|info)\s*\(')

def looks_like_prose(s: str) -> bool:
    if not s.strip():
        return False
    if CODE_LOOKALIKE.search(s):
        return False
    if ' ' in s.strip():
        return True
    # Standalone capitalized word (e.g. "Temperature", "Loading…") is a
    # plausible label even without a space.
    return s[:1].isupper() and s[1:].isalpha() or s[:1].isupper()

def main(path):
    candidates = []
    with open(path, encoding='utf-8') as f:
        lines = f.readlines()

    for lineno, line in enumerate(lines, start=1):
        if CONSOLE_LINE.search(line):
            continue  # developer-facing log messages, not UI text
        if line.strip().startswith('//'):
            continue
        for m in STRING_RE.finditer(line):
            s = m.group(2)
            if looks_like_prose(s):
                candidates.append((lineno, s, line.rstrip('\n')))

    # De-duplicate by string value, keep first occurrence + a count.
    seen = {}
    for lineno, s, line in candidates:
        if s not in seen:
            seen[s] = {'first_line': lineno, 'count': 0, 'context': line.strip()}
        seen[s]['count'] += 1

    print(f"# {len(seen)} candidate string(s) in {path}\n")
    for s, info in sorted(seen.items(), key=lambda kv: kv[1]['first_line']):
        print(f"L{info['first_line']:>4}  x{info['count']}  {s!r}")
        print(f"       {info['context'][:100]}")
    print(f"\n# Review these by hand -- this is a recall-oriented filter, not a "
          f"precision one. False positives (a data key, a CSS class the "
          f"filter missed) are expected; a string it *didn't* flag is the "
          f"real risk, so skim the file once too if this is going into "
          f"production.")

if __name__ == '__main__':
    main(sys.argv[1] if len(sys.argv) > 1 else 'cardTemperature.js')
