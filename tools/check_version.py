#!/usr/bin/env python3
"""
Release check: every version label in the repo must agree with
bin/user/divumwx_version.py (the single authoritative DivumWX version).

Run from the repository root before tagging a release:

    python3 tools/check_version.py

Exits non-zero, listing each mismatch, if anything is out of step.
"""

import importlib.util
import os
import re
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


def authoritative_version():
    path = os.path.join(ROOT, 'bin', 'user', 'divumwx_version.py')
    spec = importlib.util.spec_from_file_location('divumwx_version', path)
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module.DIVUMWX_VERSION


# (file, regex whose group 1 must equal the release version, description)
CHECKS = [
    ('divumwx/index.html', r'# index\.html version (\S+)', 'collection version banner'),
    ('divumwx/index.html', r'id="footerDivumwxVer">([^<]+)<', 'footer fallback label'),
    ('divumwx/divumwf.html', r'id="wfBrandVersion">v([^<]+)<', 'brand badge'),
    ('INSTALLATION_GUIDE.md', r'covers DivumWX \*\*([^*]+)\*\*', 'guide version'),
    ('README.md', r'WeeWX Version (\d+\.\d+\.\d+)', 'README title'),
    ('CHANGELOG.md', r'^## (\d+\.\d+\.\d+)', 'latest changelog entry'),
]


def main():
    version = authoritative_version()
    problems = []

    for rel, pattern, what in CHECKS:
        path = os.path.join(ROOT, rel)
        try:
            text = open(path, encoding='utf-8').read()
        except OSError as e:
            problems.append(f"{rel}: cannot read ({e})")
            continue
        match = re.search(pattern, text, re.MULTILINE)
        if not match:
            problems.append(f"{rel}: {what} not found")
        elif match.group(1) != version:
            problems.append(f"{rel}: {what} is {match.group(1)}, expected {version}")

    guide = open(os.path.join(ROOT, 'INSTALLATION_GUIDE.md'), encoding='utf-8').read()
    for url_tag in re.findall(r'archive/refs/tags/([^/\s]+)\.zip', guide):
        if url_tag != f'ver.{version}':
            problems.append(f"INSTALLATION_GUIDE.md: download URL uses tag {url_tag}, expected ver.{version}")
    if 'refs/heads/' in guide:
        problems.append("INSTALLATION_GUIDE.md: links to a branch archive; use the release tag")

    if problems:
        print(f"Version check FAILED for DivumWX {version}:")
        for p in problems:
            print(f"  - {p}")
        return 1
    print(f"Version check passed: everything says DivumWX {version}.")
    return 0


if __name__ == '__main__':
    sys.exit(main())
