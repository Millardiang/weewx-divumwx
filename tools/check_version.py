#!/usr/bin/env python3
"""
Release check: every version label in the repo must agree with
bin/user/divumwx_version.py (the single authoritative DivumWX version).

Run from the repository root before tagging a release:

    python3 tools/check_version.py

Exits non-zero, listing each mismatch, if anything is out of step.

Versions are X.Y.Z, or X.Y.Z-<pre-release> for a release candidate, e.g.
2.0.0-RC2 (tagged ver.2.0.0-RC2). For a pre-release the README title and the
latest changelog entry may give the version it leads up to (2.0.0), and the
changelog's top entry may still be "## Unreleased — for 2.0.0"; every other
label must show the full version.
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


# X.Y.Z with an optional pre-release suffix (2.0.0-RC1)
VERSION_RE = r'\d+\.\d+\.\d+(?:-[0-9A-Za-z.]+)?'

# (file, regex whose group 1 must equal the release version, description,
#  whether a pre-release may give its base version X.Y.Z here instead)
CHECKS = [
    ('divumwx/index.html', r'# index\.html version (\S+)', 'collection version banner', False),
    ('divumwx/index.html', r'id="footerDivumwxVer">([^<]+)<', 'footer fallback label', False),
    ('divumwx/divumwf.html', r'id="wfBrandVersion">v([^<]+)<', 'brand badge', False),
    ('INSTALLATION_GUIDE.md', r'covers DivumWX \*\*([^*]+)\*\*', 'guide version', False),
    ('README.md', r'WeeWX Version (' + VERSION_RE + ')', 'README title', True),
    # the top entry: a released "## X.Y.Z[-pre]", or "## Unreleased — for X.Y.Z"
    ('CHANGELOG.md', r'^## (?:Unreleased\s+\S+\s+for\s+)?(' + VERSION_RE + ')', 'latest changelog entry', True),
]


def base_version(version):
    """2.0.0-RC1 -> 2.0.0; 2.0.0 -> 2.0.0."""
    return version.split('-', 1)[0]


def main():
    version = authoritative_version()
    problems = []
    if not re.fullmatch(VERSION_RE, version):
        problems.append(f"bin/user/divumwx_version.py: {version!r} isn't X.Y.Z or X.Y.Z-<pre-release>")
    prerelease = version != base_version(version)

    for rel, pattern, what, base_ok in CHECKS:
        path = os.path.join(ROOT, rel)
        try:
            text = open(path, encoding='utf-8').read()
        except OSError as e:
            problems.append(f"{rel}: cannot read ({e})")
            continue
        match = re.search(pattern, text, re.MULTILINE)
        if rel == 'CHANGELOG.md':
            # only the first "## " heading counts as the latest entry
            first = re.search(r'^## .*$', text, re.MULTILINE)
            match = re.match(pattern, first.group(0)) if first else None
        allowed = {version} | ({base_version(version)} if prerelease and base_ok else set())
        if not match:
            problems.append(f"{rel}: {what} not found")
        elif match.group(1) not in allowed:
            problems.append(f"{rel}: {what} is {match.group(1)}, expected {' or '.join(sorted(allowed))}")

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
