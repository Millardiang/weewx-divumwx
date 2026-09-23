"""
The single authoritative DivumWX release version.

Read by install.py (the version `weectl extension list` reports), by
bin/user/divumwx.py (logged when WeeWX loads DivumWX), and by
bin/user/divumwx_cards.py (published in archive.json as
meta.divumwx_version, which the dashboard footer displays).

Change it here, and only here, when cutting a release. Then run
`python3 tools/check_version.py` to confirm the static fallback labels
in the frontend match.
"""

DIVUMWX_VERSION = "1.0.1"
