#!/usr/bin/env python3
"""divumwx_uninstall_helper.py -- manual pre-uninstall cleanup for DivumWX.

WHY THIS EXISTS
----------------
'weectl extension uninstall divumwx' only reverses two things:
  1. files listed in install.py's `files=` manifest (bin/user/*.py and
     the three skins/ directories)
  2. the exact service-list entries declared via install.py's
     ExtensionInstaller constructor kwargs (prep_services=, data_services=,
     xtype_services=, archive_services=, report_services=)

It has NO knowledge of, and will NOT undo:
  - the dynamically-copied <HTML_ROOT>/divumwx/ frontend tree (copied at
    configure()-time, after HTML_ROOT is resolved -- outside the static
    files= mechanism entirely)
  - any of the weewx.conf sections install.py's apply_*_merge() functions
    wrote (DivumWXCards, WeatherAPI and its subsections, DataInjectService,
    LiveData, Timelapse, SkyfieldLoopData, the StdReport/DivumWXReport,
    DivumWXSkyfield, DivumWXCelestial stanzas, the StdWXCalculate/
    WXXTypes overrides)
  - enable=false having been written onto the third-party SkyfieldReport/
    CelestialReport stanzas
  - the archive-table columns added via ALTER TABLE (irreversible via
    WeeWX's extension framework regardless -- there's no "drop column on
    uninstall" concept, and older SQLite can't even DROP COLUMN without a
    full table rebuild)

This script handles everything above that CAN be reversed. It does NOT
call 'weectl extension uninstall' itself -- run this first, review its
output, then run the native uninstall separately.

USAGE
-----
    python3 divumwx_uninstall_helper.py [--config PATH] [--dry-run] [--yes]

    --config PATH   Path to weewx.conf. Defaults to weecfg's own
                     config-file discovery, same as weectl itself.
    --dry-run       Print what would happen; make no changes.
    --yes           Skip the interactive confirmation prompts (for
                     scripted/unattended use). Without it, each
                     destructive step asks first.

WHAT THIS SCRIPT DOES
----------------------
  1. Makes a timestamped backup copy of weewx.conf before touching it.
  2. Removes the copied <HTML_ROOT>/divumwx/ frontend directory tree
     (prompts for confirmation; skipped entirely with --dry-run).
  3. Strips the weewx.conf sections DivumWX's install.py is known to
     have written (see DIVUMWX_OWNED_* below).
  4. Optionally re-enables the third-party SkyfieldReport/CelestialReport
     stanzas (asks; default is to leave them disabled, since re-enabling
     assumes weewx-skyfield/weewx-celestial are still installed and
     wanted).
  5. Prints a clear, final report of the archive-table columns that were
     added by DivumWX and are NOT being touched -- these require a
     manual decision (leave them, or handle a full table rebuild by hand
     if you want them gone).
  6. Writes weewx.conf back out -- ONLY after all the above, and only if
     not --dry-run.

After this runs, follow up with:
    weectl extension uninstall divumwx
to remove the bin/user/*.py files, skins/ directories, and the declared
service-list entries.
"""

import argparse
import os
import shutil
import sys
from datetime import datetime

import weecfg


# --- Sections this installer is known to have written, and therefore
# --- the only things this script will remove. Anything NOT listed here
# --- is left alone, even if it looks related -- better to under-clean
# --- than to delete something the person configured by hand.

# Top-level sections written entirely by DivumWX's own install.py.
DIVUMWX_OWNED_TOP_LEVEL_SECTIONS = [
    'DivumWXCards',
    'AirDensity',
    'vpd',
    'RadiationDays',
    'Sunduration',
    'LastNonZero',
    'DataInjectService',
    'LiveData',
    'WeatherAPI',
    'Timelapse',
    'SkyfieldLoopData',
]

# [StdReport][[...]] subsections written entirely by DivumWX.
DIVUMWX_OWNED_REPORT_STANZAS = [
    'DivumWXReport',
    'DivumWXSkyfield',
    'DivumWXCelestial',
]

# The third-party reports DivumWX's install.py disables (enable=false)
# rather than removes. This script can restore them to enabled -- it
# never deletes these stanzas, since they belong to weewx-skyfield/
# weewx-celestial's own installers, not DivumWX's.
THIRDPARTY_ASTRONOMY_REPORTS = ['SkyfieldReport', 'CelestialReport']

# StdWXCalculate/Calculations keys DivumWX's install.py adds or forces.
# Only removed if their CURRENT value still matches what DivumWX would
# have written -- if a value differs, it's left alone on the assumption
# it's since been hand-tuned and removing it would silently revert that
# customization to WeeWX's own bare default instead.
DIVUMWX_CALCULATIONS_KEYS = {
    'vpd': 'software',
    'AirDensity': 'software',
    'ET': 'software',
    'lightning_strike_count': 'prefer_hardware',
    'windrun': 'software, archive',
    'beaufort': 'prefer_hardware',
    'abs_humidity': 'prefer_hardware, archive',
    'GTS': '"software,archive"',
    'GTSdate': '"software,archive"',
    'utcoffsetLMT': '"software,archive"',
    'dayET': '"prefer_hardware,archive"',
    'ET24': '"prefer_hardware,archive"',
    'yearGDD': '"software,archive"',
    'seasonGDD': '"software,archive"',
    'rain': 'prefer_hardware',
    'hail': 'prefer_hardware',
}

DIVUMWX_ROOT_SUBDIR = 'divumwx'

# Mirrors install.py's own DIVUMWX_EXTRA_COLUMNS -- reproduced here only
# for the final report, never used to actually touch the database.
DIVUMWX_EXTRA_COLUMN_NAMES = [
    'aerosol_optical_depth', 'AirDensity', 'cloudcover', 'dust',
    'lightning_last_det_time', 'alder_pollen', 'birch_pollen', 'olive_pollen',
    'grass_pollen', 'mugwort_pollen', 'ragweed_pollen', 'p_rain', 'p_rainRate',
    'p_hourRain', 'p_dayRain', 'p_weekRain', 'p_monthRain', 'p_yearRain',
    'p_stormRain', 'isRaining', 'hourRain', 'dayRain', 'weekRain', 'monthRain',
    'yearRain', 'stormRain', 'sunshine_time', 'sunshine_time_hours',
    'is_sunshine', 'threshold', 'vpd', 'pm4_0', 'pm2_5_SDS', 'pm10_0_SDS',
    'pv_power', 'pv_voltage_1', 'pv_voltage_2', 'pv_current_1', 'pv_current_2',
    'pv_power_1', 'pv_power_2', 'pv_energy_today', 'battery_power',
    'battery_voltage', 'battery_current', 'battery_soc', 'battery_temp',
    'battery_charge_today', 'battery_discharge_today', 'battery_discharge_total',
    'grid_power', 'grid_power_ct', 'grid_voltage', 'grid_frequency',
    'grid_import_today', 'grid_import_total', 'grid_export_today', 'load_power',
    'load_power_essential', 'load_power_non_essential', 'load_percentage',
    'inverter_temp', 'ac_output_frequency', 'ac_output_voltage',
]


def confirm(prompt, assume_yes):
    if assume_yes:
        return True
    answer = input(f"{prompt} [y/N] ").strip().lower()
    return answer == 'y'


def backup_config(config_path, dry_run):
    stamp = datetime.now().strftime('%Y%m%d_%H%M%S')
    backup_path = f"{config_path}.divumwx-uninstall-backup-{stamp}"
    if dry_run:
        print(f"[dry-run] Would back up {config_path} -> {backup_path}")
        return backup_path
    shutil.copy2(config_path, backup_path)
    print(f"Backed up {config_path} -> {backup_path}")
    return backup_path


def resolve_html_root(cfg, weewx_root):
    site_html_root = cfg['StdReport']['HTML_ROOT']
    if not os.path.isabs(site_html_root):
        site_html_root = os.path.join(weewx_root, site_html_root)
    if os.path.basename(os.path.normpath(site_html_root)) == DIVUMWX_ROOT_SUBDIR:
        return os.path.normpath(site_html_root)
    return os.path.join(site_html_root, DIVUMWX_ROOT_SUBDIR)


def remove_frontend_tree(html_root, dry_run, assume_yes):
    if not os.path.isdir(html_root):
        print(f"Frontend directory {html_root} doesn't exist -- nothing to remove.")
        return
    file_count = sum(len(files) for _, _, files in os.walk(html_root))
    print(f"Found DivumWX frontend at {html_root} ({file_count} file(s), "
          f"including any live jsondata/*.json output and webcam-timelapse frames).")
    if dry_run:
        print(f"[dry-run] Would delete {html_root}")
        return
    if confirm(f"Delete {html_root} and everything under it?", assume_yes):
        shutil.rmtree(html_root)
        print(f"Deleted {html_root}")
    else:
        print(f"Left {html_root} in place.")


def strip_owned_sections(cfg, dry_run):
    removed = []
    for name in DIVUMWX_OWNED_TOP_LEVEL_SECTIONS:
        if name in cfg:
            removed.append(name)
            if not dry_run:
                del cfg[name]
    for name in DIVUMWX_OWNED_REPORT_STANZAS:
        if name in cfg.get('StdReport', {}):
            removed.append(f'StdReport.{name}')
            if not dry_run:
                del cfg['StdReport'][name]

    if removed:
        prefix = '[dry-run] Would remove' if dry_run else 'Removed'
        print(f"{prefix} weewx.conf section(s): {', '.join(removed)}")
    else:
        print("No DivumWX-owned weewx.conf sections found -- nothing to remove there.")


def strip_calculations(cfg, dry_run):
    calc = cfg.get('StdWXCalculate', {}).get('Calculations', {})
    if not calc:
        return
    removed, skipped = [], []
    for key, expected_value in DIVUMWX_CALCULATIONS_KEYS.items():
        if key not in calc:
            continue
        current = calc[key]
        # ConfigObj may hand back a list for comma-containing values;
        # normalize both sides to a plain string for comparison.
        current_str = ', '.join(current) if isinstance(current, list) else str(current)
        if current_str.strip('"') == str(expected_value).strip('"'):
            removed.append(key)
            if not dry_run:
                del calc[key]
        else:
            skipped.append(key)

    if removed:
        prefix = '[dry-run] Would remove' if dry_run else 'Removed'
        print(f"{prefix} StdWXCalculate/Calculations key(s) matching DivumWX's "
              f"own defaults: {', '.join(removed)}")
    if skipped:
        print(f"Left StdWXCalculate/Calculations key(s) in place (value differs "
              f"from DivumWX's default, likely hand-tuned since install): "
              f"{', '.join(skipped)}")


def restore_thirdparty_reports(cfg, dry_run, assume_yes):
    present = [name for name in THIRDPARTY_ASTRONOMY_REPORTS
               if name in cfg.get('StdReport', {})]
    if not present:
        return
    print(f"Found third-party report stanza(s) disabled by DivumWX: {', '.join(present)}")
    if not confirm("Re-enable these (set enable = true)? Only do this if "
                    "weewx-skyfield/weewx-celestial are still installed "
                    "and you want their own reports back.", assume_yes):
        print("Left third-party report(s) disabled.")
        return
    for name in present:
        if not dry_run:
            cfg['StdReport'][name]['enable'] = 'true'
    prefix = '[dry-run] Would re-enable' if dry_run else 'Re-enabled'
    print(f"{prefix}: {', '.join(present)}")


def report_database_columns():
    print()
    print("=" * 70)
    print("DATABASE COLUMNS -- NOT TOUCHED BY THIS SCRIPT")
    print("=" * 70)
    print(f"DivumWX's install.py added {len(DIVUMWX_EXTRA_COLUMN_NAMES)} column(s) "
          "to the main archive table via ALTER TABLE. This is a ONE-WAY "
          "operation -- WeeWX's extension framework has no mechanism to "
          "drop columns, and SQLite historically requires rebuilding the "
          "whole table to remove one. These columns are being left in "
          "place. If you want them gone, that's a manual database "
          "operation you'll need to do yourself (and back up the archive "
          "database first). Columns added:")
    for name in DIVUMWX_EXTRA_COLUMN_NAMES:
        print(f"  - {name}")
    print("=" * 70)


def main():
    parser = argparse.ArgumentParser(description=__doc__,
                                      formatter_class=argparse.RawDescriptionHelpFormatter)
    parser.add_argument('--config', default=None, help="Path to weewx.conf")
    parser.add_argument('--dry-run', action='store_true',
                         help="Print what would happen; make no changes")
    parser.add_argument('--yes', action='store_true',
                         help="Skip interactive confirmation prompts")
    args = parser.parse_args()

    config_path, cfg = weecfg.read_config(args.config)
    weewx_root = cfg.get('WEEWX_ROOT', os.path.dirname(config_path))

    print(f"Using config: {config_path}")
    if args.dry_run:
        print("DRY RUN -- no changes will be made.")

    backup_config(config_path, args.dry_run)

    html_root = resolve_html_root(cfg, weewx_root)
    remove_frontend_tree(html_root, args.dry_run, args.yes)

    strip_owned_sections(cfg, args.dry_run)
    strip_calculations(cfg, args.dry_run)
    restore_thirdparty_reports(cfg, args.dry_run, args.yes)

    if not args.dry_run:
        # backup=False -- backup_config() above already made an explicit,
        # descriptively-named backup before any changes were applied;
        # weecfg.save_with_backup() would make a second, redundant one.
        # (weecfg has no 'save_config' function at all -- that call was
        # always going to AttributeError the moment a real run reached
        # it, since --dry-run never exercises this branch.)
        weecfg.save(cfg, config_path, backup=False)
        print(f"\nSaved {config_path}.")
    else:
        print("\n[dry-run] weewx.conf NOT saved.")

    report_database_columns()

    print()
    print("Next step: run 'weectl extension uninstall divumwx' to remove "
          "the remaining bin/user/*.py files, skins/ directories, and "
          "service-list entries.")


if __name__ == '__main__':
    sys.exit(main())
