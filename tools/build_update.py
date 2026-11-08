#!/usr/bin/env python3
"""
build_update.py -- build a DivumWX update package.

An update package is a zip holding only the files that changed between
two releases, plus install.py and a divumwx_update.json describing the
package. A station already on the older release installs it with the
usual command:

    weectl extension install divumwx-update-2.1.3-to-2.2.0.zip

install.py sees divumwx_update.json and switches to update mode: it
checks the installed release, copies just these files, removes any the
new release deleted, asks no questions and leaves weewx.conf alone. See
"Update packages" in install.py.

Usage (from anywhere inside the repo):

    python3 tools/build_update.py                    # previous release tag -> HEAD
    python3 tools/build_update.py --from ver.2.1.3 --to ver.2.2.0
    python3 tools/build_update.py --from a1ba7d1 --worktree   # uncommitted work

--from / --to are any git refs (tags, commits, branches). Without
--from, the newest release tag (ver.X.Y.Z or verX.Y.Z) older than the
target version is used. --worktree takes the target from the working
tree, including new files not yet committed, instead of --to.

The versions recorded in the package are each side's DIVUMWX_VERSION
(bin/user/divumwx_version.py), so they must differ. The zip is written
to dist/ (or --out) and its path printed on the last line, for CI.

Run by .github/workflows/update-package.yml whenever a release tag is
pushed; the zip is attached to that tag's GitHub release.
"""

import argparse
import datetime
import io
import json
import os
import re
import subprocess
import sys
import zipfile

VERSION_FILE = 'bin/user/divumwx_version.py'
MANIFEST_NAME = 'divumwx_update.json'
FORMAT = 1

# Shipped to stations: what a full install puts on disk. Everything else
# in the repo (tests/, tools/, CI, icon sources) isn't. Repo-root files
# are copied next to weewx.conf except these -- keep in step with
# install.py's DIVUMWX_ROOT_DOCS_EXCLUDED_FILES.
SHIPPED_DIRS = ('bin/', 'skins/', 'divumwx/')
ROOT_EXCLUDED = {
    '.gitignore',
    'build_card_bundle.py',
    'extract_strings.py',
}
# Always in the package: install.py does the updating, and the version
# file is what the next update checks against.
ALWAYS = ('install.py', VERSION_FILE)

# install.py lines that change routinely without changing what the
# installer does to weewx.conf: comments, the files list's paths and the
# language list. Anything else changing sets installer_changed.
_INSTALLER_NOISE = (
    re.compile(r"^\s*#"),
    re.compile(r"^\s*$"),
    re.compile(r"^\s*'(?:bin|skins|divumwx)/[^']*',\s*$"),
    re.compile(r"^\s*'[A-Za-z_]+':\s+'[^']*\([^)]*\)',\s*$"),
)


def git(*args, binary=False):
    out = subprocess.run(['git'] + list(args), check=True, capture_output=True).stdout
    return out if binary else out.decode('utf-8')


def version_tuple(v):
    return tuple(int(x) for x in re.findall(r'\d+', v.split('-', 1)[0])[:3])


def read_version(text):
    match = re.search(r'^DIVUMWX_VERSION\s*=\s*["\']([^"\']+)["\']', text, re.MULTILINE)
    if not match:
        sys.exit(f"Couldn't read DIVUMWX_VERSION from {VERSION_FILE}")
    return match.group(1)


def release_tags():
    """[(version_tuple, tag)] for every ver.X.Y.Z / verX.Y.Z tag."""
    tags = []
    for tag in git('tag', '--list', 'ver*').split():
        match = re.fullmatch(r'ver\.?(\d+\.\d+\.\d+)', tag)
        if match:
            tags.append((version_tuple(match.group(1)), tag))
    return sorted(tags)


def shipped(path):
    if '__pycache__/' in path or path.endswith('.pyc'):
        return False
    if path.startswith(SHIPPED_DIRS):
        return True
    return '/' not in path and path not in ROOT_EXCLUDED and path != MANIFEST_NAME


def installer_changed(old, new):
    def meaningful(text):
        return [line.rstrip() for line in text.splitlines()
                if not any(p.match(line) for p in _INSTALLER_NOISE)]
    return meaningful(old) != meaningful(new)


def main():
    ap = argparse.ArgumentParser(description=__doc__.split('\n\n')[1],
                                 formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument('--from', dest='from_ref', help='the release being updated from (git ref)')
    ap.add_argument('--to', dest='to_ref', default='HEAD', help='the new release (git ref, default HEAD)')
    ap.add_argument('--worktree', action='store_true',
                    help='take the new release from the working tree instead of --to')
    ap.add_argument('--out', default='dist', help='output directory (default dist/)')
    ap.add_argument('--installer-unchanged', action='store_true',
                    help="install.py changed, but not in a way that touches weewx.conf: "
                         "don't tell stations to consider the full installer")
    args = ap.parse_args()

    os.chdir(git('rev-parse', '--show-toplevel').strip())

    if args.worktree:
        def read(path):
            with open(path, 'rb') as f:
                return f.read()
        to_label = 'working tree'
    else:
        git('rev-parse', '--verify', args.to_ref + '^{commit}')

        def read(path):
            return git('show', f'{args.to_ref}:{path}', binary=True)
        to_label = args.to_ref
    to_version = read_version(read(VERSION_FILE).decode('utf-8'))

    from_ref = args.from_ref
    if not from_ref:
        older = [tag for v, tag in release_tags() if v < version_tuple(to_version)]
        if not older:
            sys.exit(f"No release tag older than {to_version} found; pass --from <ref>.")
        from_ref = older[-1]
    git('rev-parse', '--verify', from_ref + '^{commit}')
    from_version = read_version(git('show', f'{from_ref}:{VERSION_FILE}'))
    if from_version == to_version:
        sys.exit(f"{from_ref} and {to_label} are both DivumWX {to_version}: bump "
                 f"DIVUMWX_VERSION for the new release first.")

    # Added/modified/deleted paths, renames split into delete + add.
    if args.worktree:
        status = git('diff', '--name-status', '--no-renames', from_ref)
        untracked = git('ls-files', '--others', '--exclude-standard').splitlines()
    else:
        status = git('diff', '--name-status', '--no-renames', from_ref, args.to_ref)
        untracked = []
    changed, removed = set(), set()
    for line in status.splitlines():
        code, path = line.split('\t', 1)
        if not shipped(path):
            continue
        (removed if code.startswith('D') else changed).add(path)
    changed.update(p for p in untracked if shipped(p))
    changed.update(ALWAYS)
    changed = sorted(changed)
    removed = sorted(removed - set(changed))

    old_installer = git('show', f'{from_ref}:install.py')
    manifest = {
        'format': FORMAT,
        'from_version': from_version,
        'to_version': to_version,
        'from_ref': git('rev-parse', '--short', from_ref).strip(),
        'to_ref': 'worktree' if args.worktree else git('rev-parse', '--short', args.to_ref).strip(),
        'built': datetime.datetime.now(datetime.timezone.utc).strftime('%Y-%m-%dT%H:%M:%SZ'),
        'installer_changed': (not args.installer_unchanged
                              and installer_changed(old_installer, read('install.py').decode('utf-8'))),
        'files': changed,
        'removed': removed,
    }

    name = f'divumwx-update-{from_version}-to-{to_version}'
    os.makedirs(args.out, exist_ok=True)
    zip_path = os.path.join(args.out, name + '.zip')
    buf = io.BytesIO()
    with zipfile.ZipFile(buf, 'w', zipfile.ZIP_DEFLATED) as z:
        for path in changed:
            info = zipfile.ZipInfo(f'{name}/{path}', date_time=(2020, 1, 1, 0, 0, 0))
            info.external_attr = 0o644 << 16
            info.compress_type = zipfile.ZIP_DEFLATED
            z.writestr(info, read(path))
        z.writestr(f'{name}/{MANIFEST_NAME}', json.dumps(manifest, indent=2) + '\n')
    with open(zip_path, 'wb') as f:
        f.write(buf.getvalue())

    print(f"DivumWX update {from_version} ({from_ref}) -> {to_version} ({to_label})")
    print(f"  {len(changed)} file(s) to update, {len(removed)} to remove")
    for path in removed:
        print(f"  remove: {path}")
    if manifest['installer_changed']:
        print("  NOTE: install.py changed beyond its file and language lists. The update")
        print("        package leaves weewx.conf alone and tells stations so. If no new")
        print("        settings are involved, rebuild with --installer-unchanged.")
    print(zip_path)


if __name__ == '__main__':
    main()
