"""
Small, independently-testable helpers for merging DivumWX's required
entries into an existing weewx.conf, without clobbering what's already
there. Built incrementally against test.conf before being wired into
install.py proper.
"""


def as_list(value):
    """
    configobj returns a comma-separated config value as:
      - a plain string, if there's exactly one item
      - a list, if there are 0 or 2+ items (data_services = , -> [])
    Normalize any of these shapes to a list of non-empty, stripped strings.
    """
    if value is None:
        return []
    if isinstance(value, str):
        value = value.strip()
        return [value] if value else []
    # already a list
    return [v.strip() for v in value if v and v.strip()]


def merge_service_list(existing_value, new_entries):
    """
    Append new_entries onto whatever's already in existing_value,
    preserving existing order, skipping duplicates, and skipping any
    new_entries already present. Returns a plain list (caller decides
    how to write it back, e.g. via configobj which will comma-join it).
    """
    current = as_list(existing_value)
    result = list(current)  # preserve order/copy
    for entry in new_entries:
        entry = entry.strip()
        if entry and entry not in result:
            result.append(entry)
    return result


# The DivumWX additions file's target values for each service list.
# prep_services confirmed required (skyfield/skymap almanac services).
DIVUMWX_SERVICES = {
    'prep_services': ['user.skyfieldalmanac.SkyfieldService', 'user.skymapalmanac.SkymapService'],
    'data_services': ['user.divumwx.DataInjectService', 'user.livedata.LiveDataService',
                       'user.skyfieldalmanac.LiveService', 'user.moonimage.MoonService',
                       'user.divumwx.WeatherAPIService'],
    'xtype_services': ['user.divumwx.AirDensityService', 'user.divumwx.vpdService',
                        'user.divumwx.LastNonZeroService'],
    'archive_services': ['user.divumwx.SunshineDuration', 'user.divumwx.DivumwxExtrasService'],
    'report_services': ['user.loopdata.LoopData'],
}


def apply_service_merges(cfg, services=DIVUMWX_SERVICES):
    """
    Mutates cfg['Engine']['Services'] in place, merging in DivumWX's
    required service entries. Returns a dict of {key: (before, after)}
    for anything that actually changed, so the caller can report it.
    """
    svc = cfg['Engine']['Services']
    changes = {}
    for key, new_entries in services.items():
        before = svc.get(key)
        merged = merge_service_list(before, new_entries)
        before_list = as_list(before)
        if merged != before_list:
            changes[key] = (before_list, merged)
        svc[key] = merged
    return changes


def _run_services_demo():
    import configobj
    import shutil

    # Work on a throwaway copy so test.conf itself stays pristine for re-runs
    shutil.copy('test.conf', 'test.conf.merged')

    cfg = configobj.ConfigObj('test.conf.merged', encoding='utf-8', file_error=True)

    print("=== BEFORE (raw configobj values) ===")
    for key in DIVUMWX_SERVICES:
        print(f"  {key} = {cfg['Engine']['Services'].get(key)!r}")

    changes = apply_service_merges(cfg)

    print("\n=== CHANGES APPLIED ===")
    for key, (before, after) in changes.items():
        print(f"  {key}:")
        print(f"    before: {before}")
        print(f"    after:  {after}")

    cfg.write()
    print("\nWrote merged result to test.conf.merged")

    # --- Verification pass: re-parse the written file fresh ---
    print("\n=== VERIFICATION: re-parsing test.conf.merged from disk ===")
    verify_cfg = configobj.ConfigObj('test.conf.merged', encoding='utf-8', file_error=True)
    original = configobj.ConfigObj('test.conf', encoding='utf-8', file_error=True)
    ok = True
    for key, new_entries in DIVUMWX_SERVICES.items():
        result = as_list(verify_cfg['Engine']['Services'].get(key))
        for entry in new_entries:
            if entry not in result:
                print(f"  FAIL: {entry!r} missing from {key} after re-parse")
                ok = False
    # Confirm nothing outside [Engine][Services] moved
    for section in ('Station', 'StdRESTful', 'StdReport', 'StdWXCalculate',
                     'DataBindings', 'Databases', 'DatabaseTypes'):
        if verify_cfg[section] != original[section]:
            print(f"  FAIL: [{section}] changed unexpectedly")
            ok = False
    print("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED")


# =====================================================================
# [StdWXCalculate][[Calculations]] merge
# =====================================================================

# DivumWX's required [StdWXCalculate][[Calculations]] entries.
DIVUMWX_CALCULATIONS = {
    'vpd': 'software',
    'AirDensity': 'software',
    'ET': 'software',
    'lightning_strike_count': 'prefer_hardware',
}


def is_fresh_divumwx_install(cfg, services=DIVUMWX_SERVICES):
    """
    'Fresh install' = none of DivumWX's own service entries are present
    yet in [Engine][[Services]]. If even one is already there, treat this
    as a reinstall/upgrade rather than a fresh install.
    """
    svc = cfg['Engine']['Services']
    for key, wanted_entries in services.items():
        existing = as_list(svc.get(key))
        if any(entry in existing for entry in wanted_entries):
            return False
    return True


def apply_calculation_merges(cfg, calculations=DIVUMWX_CALCULATIONS, fresh_install=None):
    """
    Mutates cfg['StdWXCalculate']['Calculations'] in place.

    - A key that doesn't exist yet is added outright (no conflict possible).
    - A key that already exists with the SAME value is left alone, reported
      as 'unchanged'.
    - A key that already exists with a DIFFERENT value:
        * fresh_install=True  -> forced to DivumWX's value, reported as 'forced'
        * fresh_install=False -> left as-is, reported as 'conflicts'
      If fresh_install is None, it's auto-detected via is_fresh_divumwx_install(cfg)
      (checked BEFORE this function's own service/calculation changes are applied,
      so call this before apply_service_merges if you want auto-detection here).

    Returns a dict: {'added': {...}, 'unchanged': {...}, 'forced': {...}, 'conflicts': {...}}
    """
    if fresh_install is None:
        fresh_install = is_fresh_divumwx_install(cfg)

    calc = cfg['StdWXCalculate']['Calculations']
    report = {'added': {}, 'unchanged': {}, 'forced': {}, 'conflicts': {}, 'fresh_install': fresh_install}

    for key, wanted in calculations.items():
        if key not in calc:
            calc[key] = wanted
            report['added'][key] = wanted
        elif calc[key] == wanted:
            report['unchanged'][key] = wanted
        elif fresh_install:
            report['forced'][key] = (calc[key], wanted)
            calc[key] = wanted
        else:
            report['conflicts'][key] = (calc[key], wanted)
            # deliberately NOT changed here

    return report


def _run_calculations_demo():
    import configobj
    import shutil

    # --- Scenario 1: fresh install (plain test.conf, no DivumWX services yet) ---
    shutil.copy('test.conf', 'test.conf.calc_fresh')
    fresh_cfg = configobj.ConfigObj('test.conf.calc_fresh', encoding='utf-8', file_error=True)
    fresh_report = apply_calculation_merges(fresh_cfg)  # fresh_install auto-detected
    fresh_cfg.write()

    print("=== Scenario 1: FRESH INSTALL (test.conf) ===")
    print(f"  detected fresh_install = {fresh_report['fresh_install']}")
    print(f"  added:   {fresh_report['added']}")
    print(f"  forced:  {fresh_report['forced']}")
    print(f"  conflicts: {fresh_report['conflicts']}")

    # --- Scenario 2: reinstall/upgrade (test.conf.merged, which already has
    #     DivumWX's services from the earlier Services-merge step) ---
    shutil.copy('test.conf.merged', 'test.conf.calc_reinstall')
    reinstall_cfg = configobj.ConfigObj('test.conf.calc_reinstall', encoding='utf-8', file_error=True)
    reinstall_report = apply_calculation_merges(reinstall_cfg)  # fresh_install auto-detected
    reinstall_cfg.write()

    print("\n=== Scenario 2: REINSTALL (test.conf.merged, DivumWX services already present) ===")
    print(f"  detected fresh_install = {reinstall_report['fresh_install']}")
    print(f"  added:   {reinstall_report['added']}")
    print(f"  forced:  {reinstall_report['forced']}")
    print(f"  conflicts: {reinstall_report['conflicts']}")

    # --- Verification ---
    print("\n=== VERIFICATION ===")
    ok = True

    v_fresh = configobj.ConfigObj('test.conf.calc_fresh', encoding='utf-8', file_error=True)
    if v_fresh['StdWXCalculate']['Calculations'].get('ET') != 'software':
        print("  FAIL: fresh install should have forced ET=software")
        ok = False
    else:
        print("  OK: fresh install forced ET=software")

    v_reinstall = configobj.ConfigObj('test.conf.calc_reinstall', encoding='utf-8', file_error=True)
    if v_reinstall['StdWXCalculate']['Calculations'].get('ET') != 'prefer_hardware':
        print("  FAIL: reinstall should have left ET untouched (prefer_hardware)")
        ok = False
    else:
        print("  OK: reinstall left ET=prefer_hardware untouched")

    print("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED")


# =====================================================================
# [StdReport][[DivumWXReport]] merge
# =====================================================================

# These are DivumWX's own report subsection — since nothing else owns it,
# 'skin' and the unit Groups are enforced every time (the additions file's
# own comment says "do not change" for the Groups block). 'enable' is only
# defaulted on creation, since a user may deliberately disable the report
# later and a reinstall shouldn't silently re-enable it. HTML_ROOT is a
# per-install path that must be prompted for — never invented, never
# overwritten once set.
DIVUMWX_REPORT_GROUPS = {
    'group_altitude': 'meter',
    'group_degree_day': 'degree_C_day',
    'group_pressure': 'hPa',
    'group_rain': 'mm',
    'group_rainrate': 'mm_per_hour',
    'group_speed': 'meter_per_second',
    'group_speed2': 'meter_per_second2',
    'group_temperature': 'degree_C',
}


def apply_report_merge(cfg, html_root=None, groups=DIVUMWX_REPORT_GROUPS):
    """
    Mutates cfg['StdReport']['DivumWXReport'] in place, creating it if
    it doesn't exist.

    html_root: the answer to install.py's HTML_ROOT prompt, if already
    known. If the subsection doesn't have HTML_ROOT yet and html_root is
    None, it's left unset and report['html_root_needs_prompt'] = True so
    the caller knows to go ask.

    Returns a report dict describing what happened.
    """
    report = {
        'created_subsection': False,
        'skin_enforced': False,
        'enable_defaulted': False,
        'html_root_set': False,
        'html_root_needs_prompt': False,
        'groups_enforced': [],
    }

    if 'DivumWXReport' not in cfg['StdReport']:
        cfg['StdReport']['DivumWXReport'] = {}
        report['created_subsection'] = True

    dr = cfg['StdReport']['DivumWXReport']

    # skin: always enforced, this subsection exists only for DivumWX's skin
    if dr.get('skin') != 'DivumWX':
        dr['skin'] = 'DivumWX'
        report['skin_enforced'] = True

    # enable: only set a default if missing entirely; never override a
    # deliberate user choice on reinstall
    if 'enable' not in dr:
        dr['enable'] = 'true'
        report['enable_defaulted'] = True

    # HTML_ROOT: only set if missing; never overwrite an existing path
    if 'HTML_ROOT' not in dr or not dr['HTML_ROOT']:
        if html_root:
            dr['HTML_ROOT'] = html_root
            report['html_root_set'] = True
        else:
            report['html_root_needs_prompt'] = True

    # Units/Groups: always enforced (per the additions file's own comment
    # that these must not be changed, to avoid affecting other skins)
    if 'Units' not in dr:
        dr['Units'] = {}
    if 'Groups' not in dr['Units']:
        dr['Units']['Groups'] = {}
    grp = dr['Units']['Groups']
    for key, wanted in groups.items():
        if grp.get(key) != wanted:
            grp[key] = wanted
            report['groups_enforced'].append(key)

    return report


def _run_report_demo():
    import configobj
    import shutil

    # --- Scenario 1: fresh install, HTML_ROOT not yet known (should ask to prompt) ---
    shutil.copy('test.conf', 'test.conf.report_fresh_noprompt')
    cfg1 = configobj.ConfigObj('test.conf.report_fresh_noprompt', encoding='utf-8', file_error=True)
    report1 = apply_report_merge(cfg1)  # no html_root supplied
    cfg1.write()

    print("=== Scenario 1: fresh install, HTML_ROOT not supplied ===")
    for k, v in report1.items():
        print(f"  {k}: {v}")

    # --- Scenario 2: fresh install, HTML_ROOT supplied (as if install.py already prompted) ---
    shutil.copy('test.conf', 'test.conf.report_fresh_withpath')
    cfg2 = configobj.ConfigObj('test.conf.report_fresh_withpath', encoding='utf-8', file_error=True)
    report2 = apply_report_merge(cfg2, html_root='/var/www/html/divumwx')
    cfg2.write()

    print("\n=== Scenario 2: fresh install, HTML_ROOT='/var/www/html/divumwx' ===")
    for k, v in report2.items():
        print(f"  {k}: {v}")

    # --- Scenario 3: reinstall — subsection already exists, user disabled the report,
    #     and had customized HTML_ROOT. Both should be left alone. Groups still enforced. ---
    shutil.copy('test.conf.report_fresh_withpath', 'test.conf.report_reinstall')
    cfg3 = configobj.ConfigObj('test.conf.report_reinstall', encoding='utf-8', file_error=True)
    # Simulate user having disabled it and tweaked one group value by hand
    cfg3['StdReport']['DivumWXReport']['enable'] = 'false'
    cfg3['StdReport']['DivumWXReport']['Units']['Groups']['group_pressure'] = 'inHg'  # user meddled
    cfg3.write()

    cfg3_reload = configobj.ConfigObj('test.conf.report_reinstall', encoding='utf-8', file_error=True)
    report3 = apply_report_merge(cfg3_reload, html_root='/var/www/html/divumwx')
    cfg3_reload.write()

    print("\n=== Scenario 3: reinstall, user had disabled report + hand-edited a group ===")
    for k, v in report3.items():
        print(f"  {k}: {v}")

    # --- Verification ---
    print("\n=== VERIFICATION ===")
    ok = True

    v1 = configobj.ConfigObj('test.conf.report_fresh_noprompt', encoding='utf-8', file_error=True)
    dr1 = v1['StdReport']['DivumWXReport']
    if 'HTML_ROOT' in dr1:
        print("  FAIL: scenario 1 should not have set HTML_ROOT")
        ok = False
    else:
        print("  OK: scenario 1 left HTML_ROOT unset, flagged for prompting")

    v2 = configobj.ConfigObj('test.conf.report_fresh_withpath', encoding='utf-8', file_error=True)
    dr2 = v2['StdReport']['DivumWXReport']
    if dr2.get('HTML_ROOT') != '/var/www/html/divumwx' or dr2.get('skin') != 'DivumWX' or dr2.get('enable') != 'true':
        print("  FAIL: scenario 2 basic fields wrong:", dict(dr2))
        ok = False
    else:
        print("  OK: scenario 2 skin/enable/HTML_ROOT all set correctly")
    if dr2['Units']['Groups'].get('group_pressure') != 'hPa':
        print("  FAIL: scenario 2 groups not enforced correctly")
        ok = False

    v3 = configobj.ConfigObj('test.conf.report_reinstall', encoding='utf-8', file_error=True)
    dr3 = v3['StdReport']['DivumWXReport']
    if dr3.get('enable') != 'false':
        print("  FAIL: scenario 3 should have left user's enable=false untouched")
        ok = False
    else:
        print("  OK: scenario 3 respected user's enable=false on reinstall")
    if dr3['Units']['Groups'].get('group_pressure') != 'hPa':
        print("  FAIL: scenario 3 should have re-enforced group_pressure=hPa despite user edit")
        ok = False
    else:
        print("  OK: scenario 3 re-enforced group_pressure=hPa over user's hand-edit")
    if dr3.get('HTML_ROOT') != '/var/www/html/divumwx':
        print("  FAIL: scenario 3 HTML_ROOT should have stayed as originally set")
        ok = False

    print("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED")


# =====================================================================
# [DataBindings][[divumwx_extras_binding]] and
# [Databases][[divumwx_extras_db]] merge
# =====================================================================

# Both of these subsections are entirely DivumWX's own — new names,
# not shared with wx_binding/archive_sqlite — so every key in them is
# owned and enforced every run, same treatment as 'skin' and the Groups
# block in the report merge. Nothing here is a per-install user choice.
DIVUMWX_DATABINDING = {
    'database': 'divumwx_extras_db',
    'table_name': 'archive',
    'manager': 'weewx.manager.DaySummaryManager',
    # Must point at the module-level `schema = [(...), ...]` list in
    # divumwx.py, NOT at the DivumwxExtrasService class. WeeWX resolves
    # this string via weeutil.weeutil.get_object() and hands whatever
    # it finds straight to weedb's create_table(), which iterates it
    # expecting (column, type) tuples. Pointing this at the service
    # class instead (a `type` object) crashes weewxd on first table
    # creation with "TypeError: 'type' object is not iterable" — fatal,
    # since this happens during StdEngine.loadServices() at startup,
    # before the engine's own retry/recovery logic is running.
    'schema': 'user.divumwx.schema',
}

DIVUMWX_DATABASE = {
    'database_name': 'divumwx_extras.sdb',
    'database_type': 'SQLite',
}


def _apply_owned_subsection(parent_section, subsection_name, owned_keys):
    """
    Generic helper: ensure parent_section[subsection_name] exists and
    every key in owned_keys is set to its required value, overwriting
    anything different. Returns a report dict.
    """
    report = {'created_subsection': False, 'enforced': {}}

    if subsection_name not in parent_section:
        parent_section[subsection_name] = {}
        report['created_subsection'] = True

    sub = parent_section[subsection_name]
    for key, wanted in owned_keys.items():
        if sub.get(key) != wanted:
            existing = sub.get(key)
            sub[key] = wanted
            report['enforced'][key] = (existing, wanted)

    return report


def apply_databinding_merge(cfg, owned_keys=DIVUMWX_DATABINDING):
    return _apply_owned_subsection(cfg['DataBindings'], 'divumwx_extras_binding', owned_keys)


def apply_database_merge(cfg, owned_keys=DIVUMWX_DATABASE):
    return _apply_owned_subsection(cfg['Databases'], 'divumwx_extras_db', owned_keys)


def _run_databinding_database_demo():
    import configobj
    import shutil

    # --- Scenario 1: fresh install, neither subsection exists yet ---
    shutil.copy('test.conf', 'test.conf.db_fresh')
    cfg1 = configobj.ConfigObj('test.conf.db_fresh', encoding='utf-8', file_error=True)
    binding_report1 = apply_databinding_merge(cfg1)
    database_report1 = apply_database_merge(cfg1)
    cfg1.write()

    print("=== Scenario 1: fresh install ===")
    print(f"  divumwx_extras_binding: {binding_report1}")
    print(f"  divumwx_extras_db:      {database_report1}")

    # --- Scenario 2: reinstall — subsections already exist, re-run should be a no-op ---
    cfg2 = configobj.ConfigObj('test.conf.db_fresh', encoding='utf-8', file_error=True)
    binding_report2 = apply_databinding_merge(cfg2)
    database_report2 = apply_database_merge(cfg2)
    cfg2.write()

    print("\n=== Scenario 2: re-run on already-merged file (should be no-op) ===")
    print(f"  divumwx_extras_binding: {binding_report2}")
    print(f"  divumwx_extras_db:      {database_report2}")

    # --- Scenario 3: someone hand-edited a value; it should be re-enforced ---
    cfg3 = configobj.ConfigObj('test.conf.db_fresh', encoding='utf-8', file_error=True)
    cfg3['Databases']['divumwx_extras_db']['database_type'] = 'MySQL'  # hand-edit
    cfg3.write()

    cfg3_reload = configobj.ConfigObj('test.conf.db_fresh', encoding='utf-8', file_error=True)
    database_report3 = apply_database_merge(cfg3_reload)
    cfg3_reload.write()

    print("\n=== Scenario 3: hand-edited database_type=MySQL, then re-run merge ===")
    print(f"  divumwx_extras_db: {database_report3}")

    # --- Verification ---
    print("\n=== VERIFICATION ===")
    ok = True
    original = configobj.ConfigObj('test.conf', encoding='utf-8', file_error=True)

    v1 = configobj.ConfigObj('test.conf.db_fresh', encoding='utf-8', file_error=True)
    if dict(v1['DataBindings']['divumwx_extras_binding']) != DIVUMWX_DATABINDING:
        print("  FAIL: divumwx_extras_binding not set correctly")
        ok = False
    else:
        print("  OK: divumwx_extras_binding set correctly")
    if dict(v1['Databases']['divumwx_extras_db']) != DIVUMWX_DATABASE:
        print("  FAIL: divumwx_extras_db not set correctly")
        ok = False
    else:
        print("  OK: divumwx_extras_db set correctly")

    # existing wx_binding / archive_sqlite / archive_mysql untouched
    if v1['DataBindings']['wx_binding'] != original['DataBindings']['wx_binding']:
        print("  FAIL: wx_binding was modified unexpectedly")
        ok = False
    else:
        print("  OK: existing wx_binding untouched")
    if v1['Databases']['archive_sqlite'] != original['Databases']['archive_sqlite']:
        print("  FAIL: archive_sqlite was modified unexpectedly")
        ok = False
    else:
        print("  OK: existing archive_sqlite/archive_mysql untouched")

    if binding_report2['created_subsection'] or binding_report2['enforced']:
        print("  FAIL: re-run on already-merged file should be a no-op")
        ok = False
    else:
        print("  OK: re-run is idempotent (no-op)")

    v3 = configobj.ConfigObj('test.conf.db_fresh', encoding='utf-8', file_error=True)
    if v3['Databases']['divumwx_extras_db'].get('database_type') != 'SQLite':
        print("  FAIL: hand-edited database_type was not re-enforced back to SQLite")
        ok = False
    else:
        print("  OK: hand-edited database_type re-enforced back to SQLite")

    print("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED")


# =====================================================================
# Standalone top-level sections: [AirDensity], [vpd], [RadiationDays],
# [Sunduration], [LastNonZero]
# =====================================================================

# Calibration/algorithm settings, not pure internal wiring — per decision,
# these are set ONCE on creation and never re-enforced. A later user edit
# (e.g. switching vpd's algorithm, or tuning Sunduration's coefficients)
# is respected on every subsequent reinstall/upgrade.
DIVUMWX_STANDALONE_SECTIONS = {
    'AirDensity': {'algorithm': 'simple'},
    'vpd': {'algorithm': 'tetens'},
    'RadiationDays': {'min_sunshine': '120'},
    'Sunduration': {'global_coeff': '1.0', 'B_coeff': '0.06'},
    'LastNonZero': {'algorithm': 'simple'},
}


def _apply_default_only_subsection(parent_section, subsection_name, default_keys):
    """
    Ensure parent_section[subsection_name] exists. For each key in
    default_keys: set it ONLY if missing from the subsection. Existing
    values (however they got there — DivumWX's own prior run, or a
    user hand-edit) are never overwritten.

    Returns a report dict: {'created_subsection': bool, 'defaulted': {...}}
    """
    report = {'created_subsection': False, 'defaulted': {}}

    if subsection_name not in parent_section:
        parent_section[subsection_name] = {}
        report['created_subsection'] = True

    sub = parent_section[subsection_name]
    for key, wanted in default_keys.items():
        if key not in sub:
            sub[key] = wanted
            report['defaulted'][key] = wanted

    return report


def apply_standalone_sections_merge(cfg, sections=DIVUMWX_STANDALONE_SECTIONS):
    """
    Applies the set-once-only policy to each of the standalone top-level
    sections. Returns {section_name: report} for all five.
    """
    return {
        name: _apply_default_only_subsection(cfg, name, keys)
        for name, keys in sections.items()
    }


def _run_standalone_sections_demo():
    import configobj
    import shutil

    # --- Scenario 1: fresh install, none of the five sections exist yet ---
    shutil.copy('test.conf', 'test.conf.standalone_fresh')
    cfg1 = configobj.ConfigObj('test.conf.standalone_fresh', encoding='utf-8', file_error=True)
    reports1 = apply_standalone_sections_merge(cfg1)
    cfg1.write()

    print("=== Scenario 1: fresh install ===")
    for name, r in reports1.items():
        print(f"  {name}: {r}")

    # Snapshot verification of the fresh-install result BEFORE scenario 2
    # mutates this same file.
    print("\n=== VERIFICATION (fresh install, before any hand-edits) ===")
    ok = True
    original = configobj.ConfigObj('test.conf', encoding='utf-8', file_error=True)
    v1 = configobj.ConfigObj('test.conf.standalone_fresh', encoding='utf-8', file_error=True)
    all_correct = True
    for name, keys in DIVUMWX_STANDALONE_SECTIONS.items():
        if dict(v1[name]) != keys:
            print(f"  FAIL: {name} not set correctly on fresh install: {dict(v1[name])}")
            ok = False
            all_correct = False
    if all_correct:
        print("  OK: all five sections set correctly on fresh install")

    # --- Scenario 2: user later edits vpd's algorithm and one Sunduration
    #     coefficient by hand; re-running the merge must leave both alone,
    #     but should still fill in any genuinely missing key ---
    cfg2 = configobj.ConfigObj('test.conf.standalone_fresh', encoding='utf-8', file_error=True)
    cfg2['vpd']['algorithm'] = 'buck'                # user's deliberate change
    cfg2['Sunduration']['global_coeff'] = '1.05'      # user's deliberate change
    del cfg2['LastNonZero']['algorithm']              # simulate a key gone missing somehow
    cfg2.write()

    cfg2_reload = configobj.ConfigObj('test.conf.standalone_fresh', encoding='utf-8', file_error=True)
    reports2 = apply_standalone_sections_merge(cfg2_reload)
    cfg2_reload.write()

    print("\n=== Scenario 2: user hand-edited vpd + Sunduration, then re-run merge ===")
    for name, r in reports2.items():
        print(f"  {name}: {r}")

    print("\n=== VERIFICATION (after re-run over hand-edits) ===")
    v2 = configobj.ConfigObj('test.conf.standalone_fresh', encoding='utf-8', file_error=True)
    if v2['vpd'].get('algorithm') != 'buck':
        print("  FAIL: user's vpd algorithm=buck was overwritten")
        ok = False
    else:
        print("  OK: user's vpd algorithm=buck respected")
    if v2['Sunduration'].get('global_coeff') != '1.05':
        print("  FAIL: user's Sunduration global_coeff=1.05 was overwritten")
        ok = False
    else:
        print("  OK: user's Sunduration global_coeff=1.05 respected")
    if v2['LastNonZero'].get('algorithm') != 'simple':
        print("  FAIL: missing LastNonZero.algorithm was not re-added")
        ok = False
    else:
        print("  OK: genuinely missing LastNonZero.algorithm was re-added")

    # confirm nothing pre-existing elsewhere moved (check against the ORIGINAL
    # base file, using v1's snapshot for sections these edits shouldn't touch)
    for section in ('Station', 'StdReport', 'DataBindings', 'Databases', 'Engine'):
        if v2[section] != original[section]:
            print(f"  FAIL: [{section}] changed unexpectedly")
            ok = False

    print("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED")


# =====================================================================
# [DataInjectService] merge
# =====================================================================

# 'path' is derived from the HTML_ROOT answer already gathered for
# [StdReport][[DivumWXReport]] (per decision: reuse, don't re-prompt).
# 'json_path' and 'mapping' are structural — the mapping keys are literal
# API field names that must match the incoming JSON, so they're always
# enforced, same as the Groups block. Note: 'european_aqi'/'us_aqi' are
# commented out in the additions file (not enabled by default), so they're
# intentionally excluded here too.
DIVUMWX_DATAINJECT_SOURCES = {
    'source_airquality': {
        'path_suffix': 'jsondata/airquality.txt',
        'json_path': 'current',
        'mapping': {
            'carbon_monoxide': 'co',
            'nitrogen_dioxide': 'no2',
            'sulphur_dioxide': 'so2',
            'ozone': 'o3',
            'ammonia': 'nh3',
            'aerosol_optical_depth': 'aerosol_optical_depth',
            'dust': 'dust',
            'alder_pollen': 'alder_pollen',
            'birch_pollen': 'birch_pollen',
            'olive_pollen': 'olive_pollen',
            'grass_pollen': 'grass_pollen',
            'mugwort_pollen': 'mugwort_pollen',
            'ragweed_pollen': 'ragweed_pollen',
        },
    },
    'source_forecast': {
        'path_suffix': 'jsondata/forecastcard.txt',
        'json_path': 'current',
        'mapping': {
            'cloud_cover': 'cloudcover',
        },
    },
}


def apply_datainject_merge(cfg, html_root=None, sources=DIVUMWX_DATAINJECT_SOURCES):
    """
    Mutates cfg['DataInjectService'] in place, creating source_airquality
    and source_forecast subsections as needed.

    html_root: the already-resolved HTML_ROOT (from the report merge step).
    If a source's 'path' isn't set yet and html_root is None, it's left
    unset and flagged needs_prompt (meaning: waiting on HTML_ROOT to be
    resolved first, not a separate prompt of its own).

    Returns {source_name: report} for both sources.
    """
    if 'DataInjectService' not in cfg:
        cfg['DataInjectService'] = {}
    dis = cfg['DataInjectService']

    reports = {}
    for source_name, source_def in sources.items():
        report = {
            'created_subsection': False,
            'path_set': False,
            'path_needs_prompt': False,
            'json_path_enforced': False,
            'mapping_enforced': [],
        }

        if source_name not in dis:
            dis[source_name] = {}
            report['created_subsection'] = True
        src = dis[source_name]

        # path: derived from html_root, only set if missing, never overwritten
        if 'path' not in src or not src['path']:
            if html_root:
                src['path'] = html_root.rstrip('/') + '/' + source_def['path_suffix']
                report['path_set'] = True
            else:
                report['path_needs_prompt'] = True

        # json_path: structural, always enforced
        if src.get('json_path') != source_def['json_path']:
            src['json_path'] = source_def['json_path']
            report['json_path_enforced'] = True

        # mapping: structural, always enforced key-by-key
        if 'mapping' not in src:
            src['mapping'] = {}
        mapping = src['mapping']
        for key, wanted in source_def['mapping'].items():
            if mapping.get(key) != wanted:
                mapping[key] = wanted
                report['mapping_enforced'].append(key)

        reports[source_name] = report

    return reports


def _run_datainject_demo():
    import configobj
    import shutil

    ok = True

    # --- Scenario 1: fresh install, HTML_ROOT already resolved (from report step) ---
    shutil.copy('test.conf', 'test.conf.datainject_fresh')
    cfg1 = configobj.ConfigObj('test.conf.datainject_fresh', encoding='utf-8', file_error=True)
    reports1 = apply_datainject_merge(cfg1, html_root='/var/www/html/divumwx')
    cfg1.write()

    print("=== Scenario 1: fresh install, HTML_ROOT='/var/www/html/divumwx' ===")
    for name, r in reports1.items():
        print(f"  {name}: {r}")

    # Verify Scenario 1 immediately, before anything else touches this file
    print("\n  --- verification (scenario 1) ---")
    v1 = configobj.ConfigObj('test.conf.datainject_fresh', encoding='utf-8', file_error=True)
    aq1 = v1['DataInjectService']['source_airquality']
    fc1 = v1['DataInjectService']['source_forecast']
    if aq1.get('path') != '/var/www/html/divumwx/jsondata/airquality.txt':
        print("  FAIL: source_airquality path wrong:", aq1.get('path'))
        ok = False
    else:
        print("  OK: source_airquality path built correctly from html_root")
    if fc1.get('path') != '/var/www/html/divumwx/jsondata/forecastcard.txt':
        print("  FAIL: source_forecast path wrong:", fc1.get('path'))
        ok = False
    else:
        print("  OK: source_forecast path built correctly from html_root")
    if dict(aq1['mapping']) != DIVUMWX_DATAINJECT_SOURCES['source_airquality']['mapping']:
        print("  FAIL: source_airquality mapping incorrect")
        ok = False
    else:
        print("  OK: source_airquality mapping correct (13 fields, no aqi keys)")
    original = configobj.ConfigObj('test.conf', encoding='utf-8', file_error=True)
    if v1['StdReport'] != original['StdReport']:
        print("  FAIL: unrelated [StdReport] changed unexpectedly")
        ok = False

    # --- Scenario 2: fresh install, HTML_ROOT not yet known ---
    shutil.copy('test.conf', 'test.conf.datainject_noroot')
    cfg2 = configobj.ConfigObj('test.conf.datainject_noroot', encoding='utf-8', file_error=True)
    reports2 = apply_datainject_merge(cfg2)  # no html_root
    cfg2.write()

    print("\n=== Scenario 2: fresh install, HTML_ROOT not yet known ===")
    for name, r in reports2.items():
        print(f"  {name}: {r}")

    print("\n  --- verification (scenario 2) ---")
    v2 = configobj.ConfigObj('test.conf.datainject_noroot', encoding='utf-8', file_error=True)
    if 'path' in v2['DataInjectService']['source_airquality']:
        print("  FAIL: path should not have been set without html_root")
        ok = False
    else:
        print("  OK: path correctly left unset, flagged needs_prompt, when html_root unknown")

    # --- Scenario 3: reinstall — user moved the airquality path elsewhere by
    #     hand, and hand-edited one mapping value. Path respected, mapping re-enforced.
    #     This deliberately reuses (mutates) test.conf.datainject_fresh from
    #     Scenario 1, since Scenario 1's own checks are already done above. ---
    cfg3 = configobj.ConfigObj('test.conf.datainject_fresh', encoding='utf-8', file_error=True)
    cfg3['DataInjectService']['source_airquality']['path'] = '/mnt/nas/divumwx/jsondata/airquality.txt'
    cfg3['DataInjectService']['source_airquality']['mapping']['ozone'] = 'o3_custom'
    cfg3.write()

    cfg3_reload = configobj.ConfigObj('test.conf.datainject_fresh', encoding='utf-8', file_error=True)
    reports3 = apply_datainject_merge(cfg3_reload, html_root='/var/www/html/divumwx')
    cfg3_reload.write()

    print("\n=== Scenario 3: reinstall, user hand-edited path + one mapping value ===")
    for name, r in reports3.items():
        print(f"  {name}: {r}")

    print("\n  --- verification (scenario 3) ---")
    v3 = configobj.ConfigObj('test.conf.datainject_fresh', encoding='utf-8', file_error=True)
    aq3 = v3['DataInjectService']['source_airquality']
    if aq3.get('path') != '/mnt/nas/divumwx/jsondata/airquality.txt':
        print("  FAIL: user's hand-edited path was overwritten")
        ok = False
    else:
        print("  OK: user's hand-edited path respected on reinstall")
    if aq3['mapping'].get('ozone') != 'o3':
        print("  FAIL: hand-edited mapping value was not re-enforced back to 'o3'")
        ok = False
    else:
        print("  OK: hand-edited mapping value re-enforced back to 'o3' (structural)")

    print("\n" + ("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED"))


# =====================================================================
# [LiveData] merge
# =====================================================================

# json_file: derived from HTML_ROOT, same as DataInjectService paths —
#   set once, never re-derived/overwritten.
# update_interval: a genuine user-tunable polling interval — set-once
#   default, respect later edits (same treatment as the standalone
#   calibration sections).
# unit_system: the additions file's own comment says "important DO NOT
#   CHANGE, it does not affect any other skins" — always enforced,
#   structural, same as StdReport's Groups block.
DIVUMWX_LIVEDATA_JSON_FILE_SUFFIX = 'jsondata/loop.json'
DIVUMWX_LIVEDATA_DEFAULT_UPDATE_INTERVAL = '2'
DIVUMWX_LIVEDATA_UNIT_SYSTEM = 'METRICWX'


def apply_livedata_merge(cfg, html_root=None, update_interval=None):
    """
    Mutates cfg['LiveData'] in place. Returns a report dict.

    update_interval: the answer to install.py's "update interval (seconds)"
    prompt, if already known. Falls back to DIVUMWX_LIVEDATA_DEFAULT_UPDATE_INTERVAL
    (2) if not supplied. Like update_interval's general treatment, this is
    only ever set once — an existing value (from a prior install or a
    hand-edit) is left alone on reinstall.
    """
    report = {
        'created_section': False,
        'json_file_set': False,
        'json_file_needs_prompt': False,
        'update_interval_set': False,
        'unit_system_enforced': False,
    }

    if 'LiveData' not in cfg:
        cfg['LiveData'] = {}
        report['created_section'] = True
    ld = cfg['LiveData']

    # json_file: derived from html_root, set-once
    if 'json_file' not in ld or not ld['json_file']:
        if html_root:
            ld['json_file'] = html_root.rstrip('/') + '/' + DIVUMWX_LIVEDATA_JSON_FILE_SUFFIX
            report['json_file_set'] = True
        else:
            report['json_file_needs_prompt'] = True

    # update_interval: set-once, prompted value if given, else default of 2
    if 'update_interval' not in ld:
        ld['update_interval'] = str(update_interval) if update_interval is not None \
            else DIVUMWX_LIVEDATA_DEFAULT_UPDATE_INTERVAL
        report['update_interval_set'] = True

    # unit_system: always enforced (structural)
    if ld.get('unit_system') != DIVUMWX_LIVEDATA_UNIT_SYSTEM:
        ld['unit_system'] = DIVUMWX_LIVEDATA_UNIT_SYSTEM
        report['unit_system_enforced'] = True

    return report


def _run_livedata_demo():
    import configobj
    import shutil

    ok = True

    # --- Scenario 1: fresh install, HTML_ROOT known ---
    shutil.copy('test.conf', 'test.conf.livedata_fresh')
    cfg1 = configobj.ConfigObj('test.conf.livedata_fresh', encoding='utf-8', file_error=True)
    report1 = apply_livedata_merge(cfg1, html_root='/var/www/html/divumwx')
    cfg1.write()

    print("=== Scenario 1: fresh install, HTML_ROOT='/var/www/html/divumwx' ===")
    print(f"  {report1}")

    print("\n  --- verification (scenario 1) ---")
    v1 = configobj.ConfigObj('test.conf.livedata_fresh', encoding='utf-8', file_error=True)
    ld1 = v1['LiveData']
    if ld1.get('json_file') != '/var/www/html/divumwx/jsondata/loop.json':
        print("  FAIL: json_file wrong:", ld1.get('json_file'))
        ok = False
    else:
        print("  OK: json_file built correctly from html_root")
    if ld1.get('update_interval') != '2':
        print("  FAIL: update_interval wrong:", ld1.get('update_interval'))
        ok = False
    else:
        print("  OK: update_interval defaulted to 2 (no prompt answer supplied)")
    if ld1.get('unit_system') != 'METRICWX':
        print("  FAIL: unit_system wrong:", ld1.get('unit_system'))
        ok = False
    else:
        print("  OK: unit_system enforced to METRICWX")

    # --- Scenario 2: fresh install, HTML_ROOT not yet known ---
    shutil.copy('test.conf', 'test.conf.livedata_noroot')
    cfg2 = configobj.ConfigObj('test.conf.livedata_noroot', encoding='utf-8', file_error=True)
    report2 = apply_livedata_merge(cfg2)
    cfg2.write()

    print("\n=== Scenario 2: fresh install, HTML_ROOT not yet known ===")
    print(f"  {report2}")

    print("\n  --- verification (scenario 2) ---")
    v2 = configobj.ConfigObj('test.conf.livedata_noroot', encoding='utf-8', file_error=True)
    if 'json_file' in v2['LiveData']:
        print("  FAIL: json_file should not have been set without html_root")
        ok = False
    else:
        print("  OK: json_file correctly left unset, flagged needs_prompt")

    # --- Scenario 3: reinstall — user tuned update_interval to 10, and
    #     (mistakenly or otherwise) changed unit_system to US. Interval
    #     respected, unit_system re-enforced back to METRICWX. ---
    cfg3 = configobj.ConfigObj('test.conf.livedata_fresh', encoding='utf-8', file_error=True)
    cfg3['LiveData']['update_interval'] = '10'
    cfg3['LiveData']['unit_system'] = 'US'
    cfg3.write()

    cfg3_reload = configobj.ConfigObj('test.conf.livedata_fresh', encoding='utf-8', file_error=True)
    report3 = apply_livedata_merge(cfg3_reload, html_root='/var/www/html/divumwx')
    cfg3_reload.write()

    print("\n=== Scenario 3: reinstall, user set update_interval=10 and unit_system=US ===")
    print(f"  {report3}")

    print("\n  --- verification (scenario 3) ---")
    v3 = configobj.ConfigObj('test.conf.livedata_fresh', encoding='utf-8', file_error=True)
    ld3 = v3['LiveData']
    if ld3.get('update_interval') != '10':
        print("  FAIL: user's update_interval=10 was overwritten")
        ok = False
    else:
        print("  OK: user's update_interval=10 respected")
    if ld3.get('unit_system') != 'METRICWX':
        print("  FAIL: unit_system=US was not re-enforced back to METRICWX")
        ok = False
    else:
        print("  OK: unit_system re-enforced back to METRICWX despite user's edit")

    print("\n" + ("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED"))


def _run_livedata_prompted_interval_demo():
    import configobj
    import shutil

    ok = True

    # Fresh install, user answers the update-interval prompt with 3
    shutil.copy('test.conf', 'test.conf.livedata_prompted')
    cfg = configobj.ConfigObj('test.conf.livedata_prompted', encoding='utf-8', file_error=True)
    report = apply_livedata_merge(cfg, html_root='/var/www/html/divumwx', update_interval=3)
    cfg.write()

    print("=== Scenario 4: fresh install, user answers update-interval prompt with 3 ===")
    print(f"  {report}")

    print("\n  --- verification (scenario 4) ---")
    v = configobj.ConfigObj('test.conf.livedata_prompted', encoding='utf-8', file_error=True)
    if v['LiveData'].get('update_interval') != '3':
        print("  FAIL: prompted update_interval=3 was not used:", v['LiveData'].get('update_interval'))
        ok = False
    else:
        print("  OK: prompted update_interval=3 used instead of the default")

    print("\n" + ("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED"))


# =====================================================================
# [WeatherAPI][[Alerts]] merge
# =====================================================================
#
# 'mandatory for global weather alerts' per the additions file's comment,
# so enabled/api_type are always enforced (not a user toggle).
# app_id is a genuine secret: must be prompted, never invented, set-once
#   (respect later key rotation done by hand).
# poll_interval: per decision, prompted per-service, set-once default 1800.
# data_path: derived from html_root, same pattern as before.

DIVUMWX_WEATHERAPI_ALERTS_API_TYPE = 'openweather'
DIVUMWX_WEATHERAPI_ALERTS_DEFAULT_POLL_INTERVAL = '1800'
DIVUMWX_WEATHERAPI_ALERTS_PATH_SUFFIX = 'jsondata/openweathermap.txt'


def apply_weatherapi_alerts_merge(cfg, html_root=None, app_id=None, poll_interval=None):
    """
    Mutates cfg['WeatherAPI']['Alerts'] in place. Returns a report dict.

    app_id: the OpenWeatherMap app_id, if already known (install.py's
    prompt answer). If not supplied and not already set, left unset and
    flagged app_id_needs_prompt.

    poll_interval: prompted value if known, else defaults to 1800 on
    creation. Set-once — a later hand-edit is respected.
    """
    if 'WeatherAPI' not in cfg:
        cfg['WeatherAPI'] = {}
    if 'Alerts' not in cfg['WeatherAPI']:
        cfg['WeatherAPI']['Alerts'] = {}
        created = True
    else:
        created = False
    alerts = cfg['WeatherAPI']['Alerts']

    report = {
        'created_subsection': created,
        'enabled_enforced': False,
        'api_type_enforced': False,
        'app_id_set': False,
        'app_id_needs_prompt': False,
        'poll_interval_set': False,
        'data_path_set': False,
        'data_path_needs_prompt': False,
    }

    # mandatory: always enforced
    if alerts.get('enabled') != 'True':
        alerts['enabled'] = 'True'
        report['enabled_enforced'] = True
    if alerts.get('api_type') != DIVUMWX_WEATHERAPI_ALERTS_API_TYPE:
        alerts['api_type'] = DIVUMWX_WEATHERAPI_ALERTS_API_TYPE
        report['api_type_enforced'] = True

    # app_id: secret, set-once, never invented
    if 'app_id' not in alerts or not alerts['app_id']:
        if app_id:
            alerts['app_id'] = app_id
            report['app_id_set'] = True
        else:
            report['app_id_needs_prompt'] = True

    # poll_interval: prompted, set-once, default 1800
    if 'poll_interval' not in alerts:
        alerts['poll_interval'] = str(poll_interval) if poll_interval is not None \
            else DIVUMWX_WEATHERAPI_ALERTS_DEFAULT_POLL_INTERVAL
        report['poll_interval_set'] = True

    # data_path: derived from html_root, set-once
    if 'data_path' not in alerts or not alerts['data_path']:
        if html_root:
            alerts['data_path'] = html_root.rstrip('/') + '/' + DIVUMWX_WEATHERAPI_ALERTS_PATH_SUFFIX
            report['data_path_set'] = True
        else:
            report['data_path_needs_prompt'] = True

    return report


def _run_weatherapi_alerts_demo():
    import configobj
    import shutil

    ok = True

    # --- Scenario 1: fresh install, all answers known ---
    shutil.copy('test.conf', 'test.conf.weatherapi_alerts_fresh')
    cfg1 = configobj.ConfigObj('test.conf.weatherapi_alerts_fresh', encoding='utf-8', file_error=True)
    report1 = apply_weatherapi_alerts_merge(
        cfg1, html_root='/var/www/html/divumwx', app_id='abc123secret', poll_interval=1800)
    cfg1.write()

    print("=== Scenario 1: fresh install, app_id and poll_interval both supplied ===")
    print(f"  {report1}")

    print("\n  --- verification (scenario 1) ---")
    v1 = configobj.ConfigObj('test.conf.weatherapi_alerts_fresh', encoding='utf-8', file_error=True)
    a1 = v1['WeatherAPI']['Alerts']
    checks1 = [
        (a1.get('enabled') == 'True', "enabled=True"),
        (a1.get('api_type') == 'openweather', "api_type=openweather"),
        (a1.get('app_id') == 'abc123secret', "app_id set from prompt"),
        (a1.get('poll_interval') == '1800', "poll_interval set from prompt"),
        (a1.get('data_path') == '/var/www/html/divumwx/jsondata/openweathermap.txt', "data_path derived"),
    ]
    for passed, label in checks1:
        print(f"  {'OK' if passed else 'FAIL'}: {label}")
        ok = ok and passed

    # --- Scenario 2: fresh install, app_id NOT yet known (should flag prompt, not invent one) ---
    shutil.copy('test.conf', 'test.conf.weatherapi_alerts_noapp')
    cfg2 = configobj.ConfigObj('test.conf.weatherapi_alerts_noapp', encoding='utf-8', file_error=True)
    report2 = apply_weatherapi_alerts_merge(cfg2, html_root='/var/www/html/divumwx')
    cfg2.write()

    print("\n=== Scenario 2: fresh install, app_id not yet supplied ===")
    print(f"  {report2}")

    print("\n  --- verification (scenario 2) ---")
    v2 = configobj.ConfigObj('test.conf.weatherapi_alerts_noapp', encoding='utf-8', file_error=True)
    if 'app_id' in v2['WeatherAPI']['Alerts']:
        print("  FAIL: app_id should not have been invented")
        ok = False
    else:
        print("  OK: app_id correctly left unset, flagged needs_prompt")
    if not report2['app_id_needs_prompt']:
        print("  FAIL: app_id_needs_prompt should be True")
        ok = False

    # --- Scenario 3: reinstall — user rotated their API key by hand and
    #     tuned poll_interval down to 900. Both respected (set-once). ---
    cfg3 = configobj.ConfigObj('test.conf.weatherapi_alerts_fresh', encoding='utf-8', file_error=True)
    cfg3['WeatherAPI']['Alerts']['app_id'] = 'rotated-key-xyz'
    cfg3['WeatherAPI']['Alerts']['poll_interval'] = '900'
    cfg3.write()

    cfg3_reload = configobj.ConfigObj('test.conf.weatherapi_alerts_fresh', encoding='utf-8', file_error=True)
    report3 = apply_weatherapi_alerts_merge(
        cfg3_reload, html_root='/var/www/html/divumwx', app_id='abc123secret', poll_interval=1800)
    cfg3_reload.write()

    print("\n=== Scenario 3: reinstall, user rotated app_id and tuned poll_interval by hand ===")
    print(f"  {report3}")

    print("\n  --- verification (scenario 3) ---")
    v3 = configobj.ConfigObj('test.conf.weatherapi_alerts_fresh', encoding='utf-8', file_error=True)
    a3 = v3['WeatherAPI']['Alerts']
    if a3.get('app_id') != 'rotated-key-xyz':
        print("  FAIL: user's rotated app_id was overwritten")
        ok = False
    else:
        print("  OK: user's rotated app_id respected (not overwritten by re-run's supplied value)")
    if a3.get('poll_interval') != '900':
        print("  FAIL: user's tuned poll_interval was overwritten")
        ok = False
    else:
        print("  OK: user's tuned poll_interval=900 respected")
    if a3.get('enabled') != 'True' or a3.get('api_type') != 'openweather':
        print("  FAIL: mandatory fields not still enforced")
        ok = False
    else:
        print("  OK: mandatory enabled/api_type still correctly enforced")

    print("\n" + ("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED"))


# =====================================================================
# Generic "simple mandatory" [WeatherAPI][[...]] sub-section merge
# =====================================================================
#
# Shape shared by Forecast, Airquality, and (later) Earthquakes/Ki/K2:
# mandatory (enabled/api_type always enforced), no secret, no poll_interval,
# just a data_path derived from html_root. Written once, reused for each.

def apply_weatherapi_simple_merge(cfg, section_name, api_type, path_suffix, html_root=None):
    """
    Mutates cfg['WeatherAPI'][section_name] in place for the "simple
    mandatory" shape: enabled=True and api_type always enforced,
    data_path derived from html_root (set-once). Returns a report dict.
    """
    if 'WeatherAPI' not in cfg:
        cfg['WeatherAPI'] = {}
    if section_name not in cfg['WeatherAPI']:
        cfg['WeatherAPI'][section_name] = {}
        created = True
    else:
        created = False
    sub = cfg['WeatherAPI'][section_name]

    report = {
        'created_subsection': created,
        'enabled_enforced': False,
        'api_type_enforced': False,
        'data_path_set': False,
        'data_path_needs_prompt': False,
    }

    if sub.get('enabled') != 'True':
        sub['enabled'] = 'True'
        report['enabled_enforced'] = True
    if sub.get('api_type') != api_type:
        sub['api_type'] = api_type
        report['api_type_enforced'] = True

    if 'data_path' not in sub or not sub['data_path']:
        if html_root:
            sub['data_path'] = html_root.rstrip('/') + '/' + path_suffix
            report['data_path_set'] = True
        else:
            report['data_path_needs_prompt'] = True

    return report


# Sections using this exact shape, in additions-file order.
DIVUMWX_WEATHERAPI_SIMPLE_SECTIONS = {
    'Forecast': {'api_type': 'openmeteo', 'path_suffix': 'jsondata/forecastcard.txt'},
    'Airquality': {'api_type': 'airquality', 'path_suffix': 'jsondata/airquality.txt'},
    'Earthquakes': {'api_type': 'earthquakes', 'path_suffix': 'jsondata/eq.txt'},
    'Ki': {'api_type': 'ki', 'path_suffix': 'jsondata/ki.txt'},
    'K2': {'api_type': 'k2', 'path_suffix': 'jsondata/k2.txt'},
    'Ovation': {'api_type': 'ovation', 'path_suffix': 'jsondata/ovation.txt'},
}


def _run_weatherapi_simple_demo():
    import configobj
    import shutil

    ok = True

    # --- Scenario 1: fresh install, html_root known ---
    shutil.copy('test.conf', 'test.conf.weatherapi_simple_fresh')
    cfg1 = configobj.ConfigObj('test.conf.weatherapi_simple_fresh', encoding='utf-8', file_error=True)
    reports1 = {}
    for name, spec in DIVUMWX_WEATHERAPI_SIMPLE_SECTIONS.items():
        reports1[name] = apply_weatherapi_simple_merge(
            cfg1, name, spec['api_type'], spec['path_suffix'], html_root='/var/www/html/divumwx')
    cfg1.write()

    print("=== Scenario 1: fresh install, Forecast + Airquality, html_root known ===")
    for name, r in reports1.items():
        print(f"  {name}: {r}")

    print("\n  --- verification (scenario 1) ---")
    v1 = configobj.ConfigObj('test.conf.weatherapi_simple_fresh', encoding='utf-8', file_error=True)
    checks = [
        (v1['WeatherAPI']['Forecast'].get('api_type') == 'openmeteo', "Forecast api_type=openmeteo"),
        (v1['WeatherAPI']['Forecast'].get('data_path') == '/var/www/html/divumwx/jsondata/forecastcard.txt',
         "Forecast data_path derived"),
        (v1['WeatherAPI']['Airquality'].get('api_type') == 'airquality', "Airquality api_type=airquality"),
        (v1['WeatherAPI']['Airquality'].get('data_path') == '/var/www/html/divumwx/jsondata/airquality.txt',
         "Airquality data_path derived"),
        (v1['WeatherAPI']['Forecast'].get('enabled') == 'True', "Forecast enabled=True"),
        (v1['WeatherAPI']['Airquality'].get('enabled') == 'True', "Airquality enabled=True"),
    ]
    for passed, label in checks:
        print(f"  {'OK' if passed else 'FAIL'}: {label}")
        ok = ok and passed

    # --- Scenario 2: reinstall — user disabled Airquality by hand; per
    #     'mandatory' treatment this gets re-enforced back to True, same
    #     as Alerts' enabled/api_type. ---
    cfg2 = configobj.ConfigObj('test.conf.weatherapi_simple_fresh', encoding='utf-8', file_error=True)
    cfg2['WeatherAPI']['Airquality']['enabled'] = 'False'
    cfg2.write()

    cfg2_reload = configobj.ConfigObj('test.conf.weatherapi_simple_fresh', encoding='utf-8', file_error=True)
    spec = DIVUMWX_WEATHERAPI_SIMPLE_SECTIONS['Airquality']
    report2 = apply_weatherapi_simple_merge(
        cfg2_reload, 'Airquality', spec['api_type'], spec['path_suffix'], html_root='/var/www/html/divumwx')
    cfg2_reload.write()

    print("\n=== Scenario 2: reinstall, user set Airquality enabled=False by hand ===")
    print(f"  {report2}")

    print("\n  --- verification (scenario 2) ---")
    v2 = configobj.ConfigObj('test.conf.weatherapi_simple_fresh', encoding='utf-8', file_error=True)
    if v2['WeatherAPI']['Airquality'].get('enabled') != 'True':
        print("  FAIL: mandatory enabled was not re-enforced")
        ok = False
    else:
        print("  OK: mandatory enabled re-enforced back to True (consistent with Alerts' treatment)")

    print("\n" + ("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED"))


# =====================================================================
# [WeatherAPI][[Metar]] merge
# =====================================================================
#
# 'mandatory for local METAR conditions' -> enabled/api_type enforced.
# url: built from a prompted ICAO airport code via a template (per
#   decision), set-once — never rebuilt/overwritten once present, so a
#   user who later swaps to a fully custom URL by hand keeps it.
# poll_interval: prompted, set-once, default 300 (per decision).
# data_path: derived from html_root, same pattern as before.

DIVUMWX_WEATHERAPI_METAR_API_TYPE = 'custom'
DIVUMWX_WEATHERAPI_METAR_URL_TEMPLATE = 'https://aviationweather.gov/api/data/metar?ids={icao_code}&format=json'
DIVUMWX_WEATHERAPI_METAR_DEFAULT_POLL_INTERVAL = '300'
DIVUMWX_WEATHERAPI_METAR_PATH_SUFFIX = 'jsondata/me.txt'


def apply_weatherapi_metar_merge(cfg, html_root=None, icao_code=None, poll_interval=None):
    """
    Mutates cfg['WeatherAPI']['Metar'] in place. Returns a report dict.

    icao_code: the answer to install.py's "ICAO airport code" prompt
    (e.g. 'EGTK'), if already known. Used to build 'url' from the
    template, once, on creation only. If not supplied and url isn't
    already set, left unset and flagged url_needs_prompt.
    """
    if 'WeatherAPI' not in cfg:
        cfg['WeatherAPI'] = {}
    if 'Metar' not in cfg['WeatherAPI']:
        cfg['WeatherAPI']['Metar'] = {}
        created = True
    else:
        created = False
    metar = cfg['WeatherAPI']['Metar']

    report = {
        'created_subsection': created,
        'enabled_enforced': False,
        'api_type_enforced': False,
        'url_set': False,
        'url_needs_prompt': False,
        'poll_interval_set': False,
        'data_path_set': False,
        'data_path_needs_prompt': False,
    }

    if metar.get('enabled') != 'True':
        metar['enabled'] = 'True'
        report['enabled_enforced'] = True
    if metar.get('api_type') != DIVUMWX_WEATHERAPI_METAR_API_TYPE:
        metar['api_type'] = DIVUMWX_WEATHERAPI_METAR_API_TYPE
        report['api_type_enforced'] = True

    # url: built from ICAO code template, set-once
    if 'url' not in metar or not metar['url']:
        if icao_code:
            metar['url'] = DIVUMWX_WEATHERAPI_METAR_URL_TEMPLATE.format(icao_code=icao_code)
            report['url_set'] = True
        else:
            report['url_needs_prompt'] = True

    # poll_interval: prompted, set-once, default 300
    if 'poll_interval' not in metar:
        metar['poll_interval'] = str(poll_interval) if poll_interval is not None \
            else DIVUMWX_WEATHERAPI_METAR_DEFAULT_POLL_INTERVAL
        report['poll_interval_set'] = True

    # data_path: derived from html_root, set-once
    if 'data_path' not in metar or not metar['data_path']:
        if html_root:
            metar['data_path'] = html_root.rstrip('/') + '/' + DIVUMWX_WEATHERAPI_METAR_PATH_SUFFIX
            report['data_path_set'] = True
        else:
            report['data_path_needs_prompt'] = True

    return report


def _run_weatherapi_metar_demo():
    import configobj
    import shutil

    ok = True

    # --- Scenario 1: fresh install, ICAO code known ---
    shutil.copy('test.conf', 'test.conf.weatherapi_metar_fresh')
    cfg1 = configobj.ConfigObj('test.conf.weatherapi_metar_fresh', encoding='utf-8', file_error=True)
    report1 = apply_weatherapi_metar_merge(
        cfg1, html_root='/var/www/html/divumwx', icao_code='EGTK', poll_interval=300)
    cfg1.write()

    print("=== Scenario 1: fresh install, icao_code='EGTK' ===")
    print(f"  {report1}")

    print("\n  --- verification (scenario 1) ---")
    v1 = configobj.ConfigObj('test.conf.weatherapi_metar_fresh', encoding='utf-8', file_error=True)
    m1 = v1['WeatherAPI']['Metar']
    expected_url = 'https://aviationweather.gov/api/data/metar?ids=EGTK&format=json'
    checks = [
        (m1.get('url') == expected_url, f"url built correctly: {m1.get('url')}"),
        (m1.get('api_type') == 'custom', "api_type=custom"),
        (m1.get('enabled') == 'True', "enabled=True"),
        (m1.get('poll_interval') == '300', "poll_interval=300"),
        (m1.get('data_path') == '/var/www/html/divumwx/jsondata/me.txt', "data_path derived"),
    ]
    for passed, label in checks:
        print(f"  {'OK' if passed else 'FAIL'}: {label}")
        ok = ok and passed

    # --- Scenario 2: fresh install, ICAO code not yet known ---
    shutil.copy('test.conf', 'test.conf.weatherapi_metar_noicao')
    cfg2 = configobj.ConfigObj('test.conf.weatherapi_metar_noicao', encoding='utf-8', file_error=True)
    report2 = apply_weatherapi_metar_merge(cfg2, html_root='/var/www/html/divumwx')
    cfg2.write()

    print("\n=== Scenario 2: fresh install, icao_code not yet supplied ===")
    print(f"  {report2}")

    print("\n  --- verification (scenario 2) ---")
    v2 = configobj.ConfigObj('test.conf.weatherapi_metar_noicao', encoding='utf-8', file_error=True)
    if 'url' in v2['WeatherAPI']['Metar']:
        print("  FAIL: url should not have been invented without an ICAO code")
        ok = False
    else:
        print("  OK: url correctly left unset, flagged url_needs_prompt")

    # --- Scenario 3: reinstall — user swapped to a different airport /
    #     fully custom URL by hand, and tuned poll_interval. Both respected. ---
    cfg3 = configobj.ConfigObj('test.conf.weatherapi_metar_fresh', encoding='utf-8', file_error=True)
    cfg3['WeatherAPI']['Metar']['url'] = 'https://aviationweather.gov/api/data/metar?ids=KJFK&format=json'
    cfg3['WeatherAPI']['Metar']['poll_interval'] = '600'
    cfg3.write()

    cfg3_reload = configobj.ConfigObj('test.conf.weatherapi_metar_fresh', encoding='utf-8', file_error=True)
    report3 = apply_weatherapi_metar_merge(
        cfg3_reload, html_root='/var/www/html/divumwx', icao_code='EGTK', poll_interval=300)
    cfg3_reload.write()

    print("\n=== Scenario 3: reinstall, user swapped airport to KJFK and tuned poll_interval to 600 ===")
    print(f"  {report3}")

    print("\n  --- verification (scenario 3) ---")
    v3 = configobj.ConfigObj('test.conf.weatherapi_metar_fresh', encoding='utf-8', file_error=True)
    m3 = v3['WeatherAPI']['Metar']
    if 'KJFK' not in m3.get('url', ''):
        print("  FAIL: user's KJFK url change was overwritten")
        ok = False
    else:
        print("  OK: user's hand-edited url (KJFK) respected, not rebuilt from EGTK")
    if m3.get('poll_interval') != '600':
        print("  FAIL: user's poll_interval=600 was overwritten")
        ok = False
    else:
        print("  OK: user's poll_interval=600 respected")

    print("\n" + ("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED"))


# =====================================================================
# Region-conditional [WeatherAPI][[...]] sub-sections
# =====================================================================
#
# Flood/HeatAlert/ColdAlert default enabled based on "are you in England?",
# MetOfficeRSS on "are you in the UK?", AuroraWatch on "Northern Hemisphere?".
# Per decision: ask these 2-3 broad region questions ONCE in install.py,
# reuse the answers across every affected section.
#
# Unlike the 'mandatory' sections (Alerts/Forecast/etc.), 'enabled' here is
# NOT force-enforced — it's a set-once default derived from the region
# answer. A user who later overrides it by hand (wants Flood alerts despite
# living outside England, say) is respected on reinstall, same treatment
# as DivumWXReport's 'enable'.

DIVUMWX_WEATHERAPI_FLOOD_API_TYPE = 'flood'
DIVUMWX_WEATHERAPI_FLOOD_PATH_SUFFIX = 'jsondata/flood.txt'


def apply_weatherapi_flood_merge(cfg, html_root=None, in_england=None):
    """
    Mutates cfg['WeatherAPI']['Flood'] in place. Returns a report dict.

    in_england: True/False answer to install.py's region question, if
    already known. If 'enabled' isn't set yet and in_england is None,
    left unset and flagged enabled_needs_prompt (i.e. waiting on the
    region question, not a Flood-specific prompt of its own).
    """
    if 'WeatherAPI' not in cfg:
        cfg['WeatherAPI'] = {}
    if 'Flood' not in cfg['WeatherAPI']:
        cfg['WeatherAPI']['Flood'] = {}
        created = True
    else:
        created = False
    flood = cfg['WeatherAPI']['Flood']

    report = {
        'created_subsection': created,
        'api_type_enforced': False,
        'enabled_defaulted': False,
        'enabled_needs_prompt': False,
        'data_path_set': False,
        'data_path_needs_prompt': False,
    }

    # api_type: structural, always enforced
    if flood.get('api_type') != DIVUMWX_WEATHERAPI_FLOOD_API_TYPE:
        flood['api_type'] = DIVUMWX_WEATHERAPI_FLOOD_API_TYPE
        report['api_type_enforced'] = True

    # enabled: set-once default derived from region answer; never re-forced
    if 'enabled' not in flood:
        if in_england is not None:
            flood['enabled'] = 'True' if in_england else 'False'
            report['enabled_defaulted'] = True
        else:
            report['enabled_needs_prompt'] = True

    # data_path: derived from html_root, set-once
    if 'data_path' not in flood or not flood['data_path']:
        if html_root:
            flood['data_path'] = html_root.rstrip('/') + '/' + DIVUMWX_WEATHERAPI_FLOOD_PATH_SUFFIX
            report['data_path_set'] = True
        else:
            report['data_path_needs_prompt'] = True

    return report


def _run_weatherapi_flood_demo():
    import configobj
    import shutil

    ok = True

    # --- Scenario 1: fresh install, user is in England ---
    shutil.copy('test.conf', 'test.conf.weatherapi_flood_england')
    cfg1 = configobj.ConfigObj('test.conf.weatherapi_flood_england', encoding='utf-8', file_error=True)
    report1 = apply_weatherapi_flood_merge(cfg1, html_root='/var/www/html/divumwx', in_england=True)
    cfg1.write()

    print("=== Scenario 1: fresh install, in_england=True ===")
    print(f"  {report1}")

    print("\n  --- verification (scenario 1) ---")
    v1 = configobj.ConfigObj('test.conf.weatherapi_flood_england', encoding='utf-8', file_error=True)
    f1 = v1['WeatherAPI']['Flood']
    if f1.get('enabled') != 'True':
        print("  FAIL: expected enabled=True for in_england=True")
        ok = False
    else:
        print("  OK: enabled=True derived from in_england=True")
    if f1.get('data_path') != '/var/www/html/divumwx/jsondata/flood.txt':
        print("  FAIL: data_path wrong:", f1.get('data_path'))
        ok = False
    else:
        print("  OK: data_path derived correctly")

    # --- Scenario 2: fresh install, user is NOT in England ---
    shutil.copy('test.conf', 'test.conf.weatherapi_flood_notengland')
    cfg2 = configobj.ConfigObj('test.conf.weatherapi_flood_notengland', encoding='utf-8', file_error=True)
    report2 = apply_weatherapi_flood_merge(cfg2, html_root='/var/www/html/divumwx', in_england=False)
    cfg2.write()

    print("\n=== Scenario 2: fresh install, in_england=False ===")
    print(f"  {report2}")

    print("\n  --- verification (scenario 2) ---")
    v2 = configobj.ConfigObj('test.conf.weatherapi_flood_notengland', encoding='utf-8', file_error=True)
    if v2['WeatherAPI']['Flood'].get('enabled') != 'False':
        print("  FAIL: expected enabled=False for in_england=False")
        ok = False
    else:
        print("  OK: enabled=False derived from in_england=False")

    # --- Scenario 3: fresh install, region not yet asked ---
    shutil.copy('test.conf', 'test.conf.weatherapi_flood_noregion')
    cfg3 = configobj.ConfigObj('test.conf.weatherapi_flood_noregion', encoding='utf-8', file_error=True)
    report3 = apply_weatherapi_flood_merge(cfg3, html_root='/var/www/html/divumwx')
    cfg3.write()

    print("\n=== Scenario 3: fresh install, region question not yet answered ===")
    print(f"  {report3}")

    print("\n  --- verification (scenario 3) ---")
    v3 = configobj.ConfigObj('test.conf.weatherapi_flood_noregion', encoding='utf-8', file_error=True)
    if 'enabled' in v3['WeatherAPI']['Flood']:
        print("  FAIL: enabled should not have been set without a region answer")
        ok = False
    else:
        print("  OK: enabled correctly left unset, flagged enabled_needs_prompt")

    # --- Scenario 4: reinstall — user living outside England manually
    #     enabled Flood anyway. Re-running with in_england=False must
    #     NOT flip it back to False. ---
    cfg4 = configobj.ConfigObj('test.conf.weatherapi_flood_notengland', encoding='utf-8', file_error=True)
    cfg4['WeatherAPI']['Flood']['enabled'] = 'True'  # user overrode by hand
    cfg4.write()

    cfg4_reload = configobj.ConfigObj('test.conf.weatherapi_flood_notengland', encoding='utf-8', file_error=True)
    report4 = apply_weatherapi_flood_merge(cfg4_reload, html_root='/var/www/html/divumwx', in_england=False)
    cfg4_reload.write()

    print("\n=== Scenario 4: reinstall, user outside England manually enabled Flood ===")
    print(f"  {report4}")

    print("\n  --- verification (scenario 4) ---")
    v4 = configobj.ConfigObj('test.conf.weatherapi_flood_notengland', encoding='utf-8', file_error=True)
    if v4['WeatherAPI']['Flood'].get('enabled') != 'True':
        print("  FAIL: user's manual enabled=True override was reset by region default")
        ok = False
    else:
        print("  OK: user's manual override respected, not reset by region default")

    print("\n" + ("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED"))


# =====================================================================
# [WeatherAPI][[AuroraWatch]] merge
# =====================================================================
#
# Combines the two patterns already built: enabled is a set-once default
# derived from the "Northern Hemisphere?" region answer (same treatment
# as Flood's in_england), and poll_interval is prompted/set-once like
# Alerts/Metar (default 120).

DIVUMWX_WEATHERAPI_AURORAWATCH_API_TYPE = 'aurorawatch'
DIVUMWX_WEATHERAPI_AURORAWATCH_DEFAULT_POLL_INTERVAL = '120'
DIVUMWX_WEATHERAPI_AURORAWATCH_PATH_SUFFIX = 'jsondata/aurora.txt'


def apply_weatherapi_aurorawatch_merge(cfg, html_root=None, in_northern_hemisphere=None, poll_interval=None):
    """
    Mutates cfg['WeatherAPI']['AuroraWatch'] in place. Returns a report dict.

    in_northern_hemisphere: True/False answer to install.py's region
    question, if already known. Same set-once-default treatment as
    Flood's in_england.

    poll_interval: prompted value if known, else defaults to 120 on
    creation, set-once.
    """
    if 'WeatherAPI' not in cfg:
        cfg['WeatherAPI'] = {}
    if 'AuroraWatch' not in cfg['WeatherAPI']:
        cfg['WeatherAPI']['AuroraWatch'] = {}
        created = True
    else:
        created = False
    aw = cfg['WeatherAPI']['AuroraWatch']

    report = {
        'created_subsection': created,
        'api_type_enforced': False,
        'enabled_defaulted': False,
        'enabled_needs_prompt': False,
        'poll_interval_set': False,
        'data_path_set': False,
        'data_path_needs_prompt': False,
    }

    # api_type: structural, always enforced
    if aw.get('api_type') != DIVUMWX_WEATHERAPI_AURORAWATCH_API_TYPE:
        aw['api_type'] = DIVUMWX_WEATHERAPI_AURORAWATCH_API_TYPE
        report['api_type_enforced'] = True

    # enabled: set-once default derived from region answer; never re-forced
    if 'enabled' not in aw:
        if in_northern_hemisphere is not None:
            aw['enabled'] = 'True' if in_northern_hemisphere else 'False'
            report['enabled_defaulted'] = True
        else:
            report['enabled_needs_prompt'] = True

    # poll_interval: prompted, set-once, default 120
    if 'poll_interval' not in aw:
        aw['poll_interval'] = str(poll_interval) if poll_interval is not None \
            else DIVUMWX_WEATHERAPI_AURORAWATCH_DEFAULT_POLL_INTERVAL
        report['poll_interval_set'] = True

    # data_path: derived from html_root, set-once
    if 'data_path' not in aw or not aw['data_path']:
        if html_root:
            aw['data_path'] = html_root.rstrip('/') + '/' + DIVUMWX_WEATHERAPI_AURORAWATCH_PATH_SUFFIX
            report['data_path_set'] = True
        else:
            report['data_path_needs_prompt'] = True

    return report


def _run_weatherapi_aurorawatch_demo():
    import configobj
    import shutil

    ok = True

    # --- Scenario 1: fresh install, Northern Hemisphere, poll_interval supplied ---
    shutil.copy('test.conf', 'test.conf.weatherapi_aurora_nh')
    cfg1 = configobj.ConfigObj('test.conf.weatherapi_aurora_nh', encoding='utf-8', file_error=True)
    report1 = apply_weatherapi_aurorawatch_merge(
        cfg1, html_root='/var/www/html/divumwx', in_northern_hemisphere=True, poll_interval=120)
    cfg1.write()

    print("=== Scenario 1: fresh install, in_northern_hemisphere=True ===")
    print(f"  {report1}")

    print("\n  --- verification (scenario 1) ---")
    v1 = configobj.ConfigObj('test.conf.weatherapi_aurora_nh', encoding='utf-8', file_error=True)
    a1 = v1['WeatherAPI']['AuroraWatch']
    checks1 = [
        (a1.get('enabled') == 'True', "enabled=True derived from N.Hemisphere=True"),
        (a1.get('api_type') == 'aurorawatch', "api_type=aurorawatch"),
        (a1.get('poll_interval') == '120', "poll_interval=120"),
        (a1.get('data_path') == '/var/www/html/divumwx/jsondata/aurora.txt', "data_path derived"),
    ]
    for passed, label in checks1:
        print(f"  {'OK' if passed else 'FAIL'}: {label}")
        ok = ok and passed

    # --- Scenario 2: fresh install, Southern Hemisphere ---
    shutil.copy('test.conf', 'test.conf.weatherapi_aurora_sh')
    cfg2 = configobj.ConfigObj('test.conf.weatherapi_aurora_sh', encoding='utf-8', file_error=True)
    report2 = apply_weatherapi_aurorawatch_merge(
        cfg2, html_root='/var/www/html/divumwx', in_northern_hemisphere=False)
    cfg2.write()

    print("\n=== Scenario 2: fresh install, in_northern_hemisphere=False ===")
    print(f"  {report2}")

    print("\n  --- verification (scenario 2) ---")
    v2 = configobj.ConfigObj('test.conf.weatherapi_aurora_sh', encoding='utf-8', file_error=True)
    if v2['WeatherAPI']['AuroraWatch'].get('enabled') != 'False':
        print("  FAIL: expected enabled=False for Southern Hemisphere")
        ok = False
    else:
        print("  OK: enabled=False derived correctly")

    # --- Scenario 3: reinstall — Southern Hemisphere user manually enabled
    #     it anyway (maybe for curiosity) and tuned poll_interval to 60.
    #     Both respected. ---
    cfg3 = configobj.ConfigObj('test.conf.weatherapi_aurora_sh', encoding='utf-8', file_error=True)
    cfg3['WeatherAPI']['AuroraWatch']['enabled'] = 'True'
    cfg3['WeatherAPI']['AuroraWatch']['poll_interval'] = '60'
    cfg3.write()

    cfg3_reload = configobj.ConfigObj('test.conf.weatherapi_aurora_sh', encoding='utf-8', file_error=True)
    report3 = apply_weatherapi_aurorawatch_merge(
        cfg3_reload, html_root='/var/www/html/divumwx', in_northern_hemisphere=False, poll_interval=120)
    cfg3_reload.write()

    print("\n=== Scenario 3: reinstall, Southern Hemisphere user manually enabled + tuned poll_interval ===")
    print(f"  {report3}")

    print("\n  --- verification (scenario 3) ---")
    v3 = configobj.ConfigObj('test.conf.weatherapi_aurora_sh', encoding='utf-8', file_error=True)
    a3 = v3['WeatherAPI']['AuroraWatch']
    if a3.get('enabled') != 'True':
        print("  FAIL: user's manual enabled=True override was reset")
        ok = False
    else:
        print("  OK: user's manual enabled override respected")
    if a3.get('poll_interval') != '60':
        print("  FAIL: user's poll_interval=60 was overwritten")
        ok = False
    else:
        print("  OK: user's poll_interval=60 respected")

    print("\n" + ("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED"))


# =====================================================================
# [WeatherAPI][[HeatAlert]] and [[ColdAlert]] merge
# =====================================================================
#
# Both are England-conditional (same treatment as Flood's enabled) and
# both need a health-alert location_code. Their placeholder text in the
# additions file is identical ('<your.health.alert.location.code>'), so
# treated as ONE prompted value reused for both — same pattern as
# HTML_ROOT being reused across sections. NOTE: HeatAlert has no 'enabled'
# line in the additions file (flagged earlier as a likely oversight);
# it's added here to match ColdAlert's shape — confirm when finalizing.

DIVUMWX_WEATHERAPI_HEATALERT_API_TYPE = 'heatalert'
DIVUMWX_WEATHERAPI_HEATALERT_PATH_SUFFIX = 'jsondata/heat.txt'
DIVUMWX_WEATHERAPI_COLDALERT_API_TYPE = 'coldalert'
DIVUMWX_WEATHERAPI_COLDALERT_PATH_SUFFIX = 'jsondata/cold.txt'

# UKHSA health-alert region codes (England only) — shared by HeatAlert
# and ColdAlert, same code -> label validation pattern as
# DIVUMWX_METOFFICE_REGIONS.
DIVUMWX_HEALTH_ALERT_LOCATIONS = {
    'E12000001': 'North East',
    'E12000002': 'North West',
    'E12000003': 'Yorkshire and The Humber',
    'E12000004': 'East Midlands',
    'E12000005': 'West Midlands',
    'E12000006': 'East of England',
    'E12000007': 'London',
    'E12000008': 'South East',
    'E12000009': 'South West',
}


def apply_weatherapi_health_alert_merge(cfg, section_name, api_type, path_suffix,
                                          html_root=None, in_england=None, location_code=None):
    """
    Generic merge for the England-conditional + location_code shape,
    used for both HeatAlert and ColdAlert. Returns a report dict.

    location_code: must be one of DIVUMWX_HEALTH_ALERT_LOCATIONS' keys.
    An unrecognized code is rejected (not written), reported as
    location_code_invalid, and treated the same as if nothing was
    supplied — same validation pattern as MetOfficeRSS's region_code.
    """
    if 'WeatherAPI' not in cfg:
        cfg['WeatherAPI'] = {}
    if section_name not in cfg['WeatherAPI']:
        cfg['WeatherAPI'][section_name] = {}
        created = True
    else:
        created = False
    sub = cfg['WeatherAPI'][section_name]

    report = {
        'created_subsection': created,
        'api_type_enforced': False,
        'enabled_defaulted': False,
        'enabled_needs_prompt': False,
        'location_code_set': False,
        'location_code_needs_prompt': False,
        'location_code_invalid': False,
        'data_path_set': False,
        'data_path_needs_prompt': False,
    }

    if sub.get('api_type') != api_type:
        sub['api_type'] = api_type
        report['api_type_enforced'] = True

    # enabled: set-once default derived from England region answer
    if 'enabled' not in sub:
        if in_england is not None:
            sub['enabled'] = 'True' if in_england else 'False'
            report['enabled_defaulted'] = True
        else:
            report['enabled_needs_prompt'] = True

    # location_code: validated menu choice, set-once, never invented
    if 'location_code' not in sub or not sub['location_code']:
        if location_code and location_code in DIVUMWX_HEALTH_ALERT_LOCATIONS:
            sub['location_code'] = location_code
            report['location_code_set'] = True
        elif location_code:
            # supplied but not a recognized code — reject rather than
            # write bad data, treat as still needing a valid prompt answer
            report['location_code_invalid'] = True
            report['location_code_needs_prompt'] = True
        else:
            report['location_code_needs_prompt'] = True

    # data_path: derived from html_root, set-once
    if 'data_path' not in sub or not sub['data_path']:
        if html_root:
            sub['data_path'] = html_root.rstrip('/') + '/' + path_suffix
            report['data_path_set'] = True
        else:
            report['data_path_needs_prompt'] = True

    return report


def apply_weatherapi_heatalert_merge(cfg, html_root=None, in_england=None, location_code=None):
    return apply_weatherapi_health_alert_merge(
        cfg, 'HeatAlert', DIVUMWX_WEATHERAPI_HEATALERT_API_TYPE, DIVUMWX_WEATHERAPI_HEATALERT_PATH_SUFFIX,
        html_root, in_england, location_code)


def apply_weatherapi_coldalert_merge(cfg, html_root=None, in_england=None, location_code=None):
    return apply_weatherapi_health_alert_merge(
        cfg, 'ColdAlert', DIVUMWX_WEATHERAPI_COLDALERT_API_TYPE, DIVUMWX_WEATHERAPI_COLDALERT_PATH_SUFFIX,
        html_root, in_england, location_code)


def _run_weatherapi_health_alert_demo():
    import configobj
    import shutil

    ok = True

    # --- Scenario 1: fresh install, England, location_code known —
    #     applied to BOTH sections using the SAME location_code value ---
    shutil.copy('test.conf', 'test.conf.weatherapi_health_england')
    cfg1 = configobj.ConfigObj('test.conf.weatherapi_health_england', encoding='utf-8', file_error=True)
    heat_report1 = apply_weatherapi_heatalert_merge(
        cfg1, html_root='/var/www/html/divumwx', in_england=True, location_code='E12000004')
    cold_report1 = apply_weatherapi_coldalert_merge(
        cfg1, html_root='/var/www/html/divumwx', in_england=True, location_code='E12000004')
    cfg1.write()

    print("=== Scenario 1: fresh install, England, shared location_code ===")
    print(f"  HeatAlert: {heat_report1}")
    print(f"  ColdAlert: {cold_report1}")

    print("\n  --- verification (scenario 1) ---")
    v1 = configobj.ConfigObj('test.conf.weatherapi_health_england', encoding='utf-8', file_error=True)
    h1 = v1['WeatherAPI']['HeatAlert']
    c1 = v1['WeatherAPI']['ColdAlert']
    checks1 = [
        (h1.get('enabled') == 'True', "HeatAlert enabled=True (added, matching ColdAlert's shape)"),
        (h1.get('api_type') == 'heatalert', "HeatAlert api_type=heatalert"),
        (h1.get('location_code') == 'E12000004', "HeatAlert location_code set"),
        (h1.get('data_path') == '/var/www/html/divumwx/jsondata/heat.txt', "HeatAlert data_path derived"),
        (c1.get('enabled') == 'True', "ColdAlert enabled=True"),
        (c1.get('api_type') == 'coldalert', "ColdAlert api_type=coldalert"),
        (c1.get('location_code') == 'E12000004', "ColdAlert location_code set (same shared value)"),
        (c1.get('data_path') == '/var/www/html/divumwx/jsondata/cold.txt', "ColdAlert data_path derived"),
    ]
    for passed, label in checks1:
        print(f"  {'OK' if passed else 'FAIL'}: {label}")
        ok = ok and passed

    # --- Scenario 2: fresh install, not England ---
    shutil.copy('test.conf', 'test.conf.weatherapi_health_notengland')
    cfg2 = configobj.ConfigObj('test.conf.weatherapi_health_notengland', encoding='utf-8', file_error=True)
    heat_report2 = apply_weatherapi_heatalert_merge(cfg2, html_root='/var/www/html/divumwx', in_england=False)
    cold_report2 = apply_weatherapi_coldalert_merge(cfg2, html_root='/var/www/html/divumwx', in_england=False)
    cfg2.write()

    print("\n=== Scenario 2: fresh install, not England (no location_code needed/supplied) ===")
    print(f"  HeatAlert: {heat_report2}")
    print(f"  ColdAlert: {cold_report2}")

    print("\n  --- verification (scenario 2) ---")
    v2 = configobj.ConfigObj('test.conf.weatherapi_health_notengland', encoding='utf-8', file_error=True)
    if v2['WeatherAPI']['HeatAlert'].get('enabled') != 'False' or v2['WeatherAPI']['ColdAlert'].get('enabled') != 'False':
        print("  FAIL: expected enabled=False for both when not in England")
        ok = False
    else:
        print("  OK: both HeatAlert and ColdAlert default to enabled=False outside England")
    if 'location_code' in v2['WeatherAPI']['HeatAlert']:
        print("  FAIL: location_code should not have been invented")
        ok = False
    else:
        print("  OK: location_code correctly left unset, flagged needs_prompt")

    # --- Scenario 3: reinstall — user hand-edited location_code (moved
    #     region) on ColdAlert only; HeatAlert's should stay as originally set. ---
    cfg3 = configobj.ConfigObj('test.conf.weatherapi_health_england', encoding='utf-8', file_error=True)
    cfg3['WeatherAPI']['ColdAlert']['location_code'] = 'E12000007'  # user moved
    cfg3.write()

    cfg3_reload = configobj.ConfigObj('test.conf.weatherapi_health_england', encoding='utf-8', file_error=True)
    apply_weatherapi_heatalert_merge(
        cfg3_reload, html_root='/var/www/html/divumwx', in_england=True, location_code='E12000004')
    apply_weatherapi_coldalert_merge(
        cfg3_reload, html_root='/var/www/html/divumwx', in_england=True, location_code='E12000004')
    cfg3_reload.write()

    print("\n=== Scenario 3: reinstall, user changed ColdAlert's location_code by hand ===")
    v3 = configobj.ConfigObj('test.conf.weatherapi_health_england', encoding='utf-8', file_error=True)
    if v3['WeatherAPI']['ColdAlert'].get('location_code') != 'E12000007':
        print("  FAIL: user's hand-edited ColdAlert location_code was overwritten")
        ok = False
    else:
        print("  OK: user's hand-edited ColdAlert location_code respected")
    if v3['WeatherAPI']['HeatAlert'].get('location_code') != 'E12000004':
        print("  FAIL: HeatAlert location_code unexpectedly changed")
        ok = False
    else:
        print("  OK: HeatAlert location_code unaffected by ColdAlert's independent edit")

    print("\n" + ("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED"))


# =====================================================================
# [WeatherAPI][[MetOfficeRSS]] merge
# =====================================================================
#
# UK-conditional (not England-specific — uses in_uk, distinct from
# Flood/HeatAlert/ColdAlert's in_england). region_code is chosen from a
# fixed 16-region menu; built here directly from the Met Office RSS URLs
# so it's unambiguous regardless of list-ordering slips. No data_path —
# this section genuinely has none in the additions file, unlike every
# other WeatherAPI sub-section.

DIVUMWX_WEATHERAPI_METOFFICERSS_API_TYPE = 'metofficerss'

# code -> region label, built from the Met Office RSS URLs directly
DIVUMWX_METOFFICE_REGIONS = {
    'os': 'Orkney & Shetland',
    'he': 'Highlands & Eilean Siar',
    'gr': 'Grampian',
    'st': 'Strathclyde',
    'ta': 'Central, Tayside & Fife',
    'dg': 'SW Scotland, Lothian & Borders',
    'ni': 'Northern Ireland',
    'wl': 'Wales',
    'nw': 'North West England',
    'ne': 'North East England',
    'yh': 'Yorkshire & Humber',
    'wm': 'West Midlands',
    'em': 'East Midlands',
    'ee': 'East of England',
    'sw': 'South West England',
    'se': 'London & South East England',
}


def apply_weatherapi_metofficerss_merge(cfg, in_uk=None, region_code=None):
    """
    Mutates cfg['WeatherAPI']['MetOfficeRSS'] in place. Returns a report dict.

    in_uk: True/False answer to install.py's "are you in the UK?" question
    (distinct from Flood/HeatAlert/ColdAlert's England-specific question).
    Set-once default for 'enabled', same treatment as Flood.

    region_code: must be one of DIVUMWX_METOFFICE_REGIONS' keys. An
    unrecognized code is rejected (not written), reported as
    region_code_invalid, and treated the same as if nothing was supplied.
    """
    if 'WeatherAPI' not in cfg:
        cfg['WeatherAPI'] = {}
    if 'MetOfficeRSS' not in cfg['WeatherAPI']:
        cfg['WeatherAPI']['MetOfficeRSS'] = {}
        created = True
    else:
        created = False
    mo = cfg['WeatherAPI']['MetOfficeRSS']

    report = {
        'created_subsection': created,
        'api_type_enforced': False,
        'enabled_defaulted': False,
        'enabled_needs_prompt': False,
        'region_code_set': False,
        'region_code_needs_prompt': False,
        'region_code_invalid': False,
    }

    if mo.get('api_type') != DIVUMWX_WEATHERAPI_METOFFICERSS_API_TYPE:
        mo['api_type'] = DIVUMWX_WEATHERAPI_METOFFICERSS_API_TYPE
        report['api_type_enforced'] = True

    # enabled: set-once default derived from UK region answer
    if 'enabled' not in mo:
        if in_uk is not None:
            mo['enabled'] = 'True' if in_uk else 'False'
            report['enabled_defaulted'] = True
        else:
            report['enabled_needs_prompt'] = True

    # region_code: validated menu choice, set-once, never invented
    if 'region_code' not in mo or not mo['region_code']:
        if region_code and region_code in DIVUMWX_METOFFICE_REGIONS:
            mo['region_code'] = region_code
            report['region_code_set'] = True
        elif region_code:
            # supplied but not a recognized code — reject rather than
            # write bad data, treat as still needing a valid prompt answer
            report['region_code_invalid'] = True
            report['region_code_needs_prompt'] = True
        else:
            report['region_code_needs_prompt'] = True

    # deliberately no data_path — matches the additions file exactly

    return report


def _run_weatherapi_metofficerss_demo():
    import configobj
    import shutil

    ok = True

    # --- Scenario 1: fresh install, in UK, valid region code (Wales) ---
    shutil.copy('test.conf', 'test.conf.weatherapi_metoffice_uk')
    cfg1 = configobj.ConfigObj('test.conf.weatherapi_metoffice_uk', encoding='utf-8', file_error=True)
    report1 = apply_weatherapi_metofficerss_merge(cfg1, in_uk=True, region_code='wl')
    cfg1.write()

    print("=== Scenario 1: fresh install, in_uk=True, region_code='wl' (Wales) ===")
    print(f"  {report1}")

    print("\n  --- verification (scenario 1) ---")
    v1 = configobj.ConfigObj('test.conf.weatherapi_metoffice_uk', encoding='utf-8', file_error=True)
    m1 = v1['WeatherAPI']['MetOfficeRSS']
    checks1 = [
        (m1.get('enabled') == 'True', "enabled=True derived from in_uk=True"),
        (m1.get('api_type') == 'metofficerss', "api_type=metofficerss"),
        (m1.get('region_code') == 'wl', "region_code=wl (Wales)"),
        ('data_path' not in m1, "no data_path (correctly absent, matching additions file)"),
    ]
    for passed, label in checks1:
        print(f"  {'OK' if passed else 'FAIL'}: {label}")
        ok = ok and passed

    # --- Scenario 2: fresh install, not UK ---
    shutil.copy('test.conf', 'test.conf.weatherapi_metoffice_notuk')
    cfg2 = configobj.ConfigObj('test.conf.weatherapi_metoffice_notuk', encoding='utf-8', file_error=True)
    report2 = apply_weatherapi_metofficerss_merge(cfg2, in_uk=False)
    cfg2.write()

    print("\n=== Scenario 2: fresh install, in_uk=False ===")
    print(f"  {report2}")

    print("\n  --- verification (scenario 2) ---")
    v2 = configobj.ConfigObj('test.conf.weatherapi_metoffice_notuk', encoding='utf-8', file_error=True)
    if v2['WeatherAPI']['MetOfficeRSS'].get('enabled') != 'False':
        print("  FAIL: expected enabled=False outside UK")
        ok = False
    else:
        print("  OK: enabled=False derived correctly")

    # --- Scenario 3: an invalid region code is rejected, not written ---
    shutil.copy('test.conf', 'test.conf.weatherapi_metoffice_badcode')
    cfg3 = configobj.ConfigObj('test.conf.weatherapi_metoffice_badcode', encoding='utf-8', file_error=True)
    report3 = apply_weatherapi_metofficerss_merge(cfg3, in_uk=True, region_code='xx')
    cfg3.write()

    print("\n=== Scenario 3: fresh install, invalid region_code='xx' ===")
    print(f"  {report3}")

    print("\n  --- verification (scenario 3) ---")
    v3 = configobj.ConfigObj('test.conf.weatherapi_metoffice_badcode', encoding='utf-8', file_error=True)
    if 'region_code' in v3['WeatherAPI']['MetOfficeRSS']:
        print("  FAIL: invalid region_code should not have been written")
        ok = False
    else:
        print("  OK: invalid region_code rejected, not written, flagged region_code_invalid")
    if not report3['region_code_invalid']:
        print("  FAIL: region_code_invalid should be True")
        ok = False

    # --- Scenario 4: reinstall — user later moved and hand-edited
    #     region_code to a different valid region; respected. ---
    cfg4 = configobj.ConfigObj('test.conf.weatherapi_metoffice_uk', encoding='utf-8', file_error=True)
    cfg4['WeatherAPI']['MetOfficeRSS']['region_code'] = 'se'  # moved to London & SE
    cfg4.write()

    cfg4_reload = configobj.ConfigObj('test.conf.weatherapi_metoffice_uk', encoding='utf-8', file_error=True)
    report4 = apply_weatherapi_metofficerss_merge(cfg4_reload, in_uk=True, region_code='wl')
    cfg4_reload.write()

    print("\n=== Scenario 4: reinstall, user moved region_code from 'wl' to 'se' by hand ===")
    print(f"  {report4}")

    print("\n  --- verification (scenario 4) ---")
    v4 = configobj.ConfigObj('test.conf.weatherapi_metoffice_uk', encoding='utf-8', file_error=True)
    if v4['WeatherAPI']['MetOfficeRSS'].get('region_code') != 'se':
        print("  FAIL: user's hand-edited region_code was overwritten")
        ok = False
    else:
        print("  OK: user's hand-edited region_code (se) respected, not reset to wl")

    print("\n" + ("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED"))
    print(f"\n  (Confirmed {len(DIVUMWX_METOFFICE_REGIONS)} regions loaded: "
          f"{', '.join(DIVUMWX_METOFFICE_REGIONS.keys())})")


# =====================================================================
# [WeatherAPI][[Xweather]] merge
# =====================================================================
#
# 'optional alternative source of forecast data and alerts' — unlike
# Flood/AuroraWatch/MetOfficeRSS, this isn't region-conditional. enabled
# is a set-once default derived from a direct opt-in answer ("do you want
# to configure Xweather?"), not a region flag. client_id/client_secret
# are genuine secrets: never invented, set-once, respect manual rotation.

DIVUMWX_WEATHERAPI_XWEATHER_API_TYPE = 'xweather'
DIVUMWX_WEATHERAPI_XWEATHER_PATH_SUFFIX = 'jsondata/xweather.txt'


def apply_weatherapi_xweather_merge(cfg, html_root=None, opted_in=None, client_id=None, client_secret=None):
    """
    Mutates cfg['WeatherAPI']['Xweather'] in place. Returns a report dict.

    opted_in: True/False answer to install.py's "configure Xweather as an
    alternative source?" question. Set-once default for 'enabled' — a
    later manual toggle is respected on reinstall, same as the other
    optional sections.

    client_id / client_secret: secrets, set-once, never invented.
    """
    if 'WeatherAPI' not in cfg:
        cfg['WeatherAPI'] = {}
    if 'Xweather' not in cfg['WeatherAPI']:
        cfg['WeatherAPI']['Xweather'] = {}
        created = True
    else:
        created = False
    xw = cfg['WeatherAPI']['Xweather']

    report = {
        'created_subsection': created,
        'api_type_enforced': False,
        'enabled_defaulted': False,
        'enabled_needs_prompt': False,
        'client_id_set': False,
        'client_id_needs_prompt': False,
        'client_secret_set': False,
        'client_secret_needs_prompt': False,
        'data_path_set': False,
        'data_path_needs_prompt': False,
    }

    if xw.get('api_type') != DIVUMWX_WEATHERAPI_XWEATHER_API_TYPE:
        xw['api_type'] = DIVUMWX_WEATHERAPI_XWEATHER_API_TYPE
        report['api_type_enforced'] = True

    # enabled: set-once default derived from direct opt-in answer
    if 'enabled' not in xw:
        if opted_in is not None:
            xw['enabled'] = 'True' if opted_in else 'False'
            report['enabled_defaulted'] = True
        else:
            report['enabled_needs_prompt'] = True

    # client_id: secret, set-once, never invented
    if 'client_id' not in xw or not xw['client_id']:
        if client_id:
            xw['client_id'] = client_id
            report['client_id_set'] = True
        else:
            report['client_id_needs_prompt'] = True

    # client_secret: secret, set-once, never invented
    if 'client_secret' not in xw or not xw['client_secret']:
        if client_secret:
            xw['client_secret'] = client_secret
            report['client_secret_set'] = True
        else:
            report['client_secret_needs_prompt'] = True

    # data_path: derived from html_root, set-once
    if 'data_path' not in xw or not xw['data_path']:
        if html_root:
            xw['data_path'] = html_root.rstrip('/') + '/' + DIVUMWX_WEATHERAPI_XWEATHER_PATH_SUFFIX
            report['data_path_set'] = True
        else:
            report['data_path_needs_prompt'] = True

    return report


def _run_weatherapi_xweather_demo():
    import configobj
    import shutil

    ok = True

    # --- Scenario 1: fresh install, opted in, all answers known ---
    shutil.copy('test.conf', 'test.conf.weatherapi_xweather_optedin')
    cfg1 = configobj.ConfigObj('test.conf.weatherapi_xweather_optedin', encoding='utf-8', file_error=True)
    report1 = apply_weatherapi_xweather_merge(
        cfg1, html_root='/var/www/html/divumwx', opted_in=True,
        client_id='xw-client-abc', client_secret='xw-secret-xyz')
    cfg1.write()

    print("=== Scenario 1: fresh install, opted_in=True, credentials supplied ===")
    print(f"  {report1}")

    print("\n  --- verification (scenario 1) ---")
    v1 = configobj.ConfigObj('test.conf.weatherapi_xweather_optedin', encoding='utf-8', file_error=True)
    x1 = v1['WeatherAPI']['Xweather']
    checks1 = [
        (x1.get('enabled') == 'True', "enabled=True derived from opted_in=True"),
        (x1.get('api_type') == 'xweather', "api_type=xweather"),
        (x1.get('client_id') == 'xw-client-abc', "client_id set"),
        (x1.get('client_secret') == 'xw-secret-xyz', "client_secret set"),
        (x1.get('data_path') == '/var/www/html/divumwx/jsondata/xweather.txt', "data_path derived"),
    ]
    for passed, label in checks1:
        print(f"  {'OK' if passed else 'FAIL'}: {label}")
        ok = ok and passed

    # --- Scenario 2: fresh install, declined (opted_in=False) ---
    shutil.copy('test.conf', 'test.conf.weatherapi_xweather_declined')
    cfg2 = configobj.ConfigObj('test.conf.weatherapi_xweather_declined', encoding='utf-8', file_error=True)
    report2 = apply_weatherapi_xweather_merge(cfg2, html_root='/var/www/html/divumwx', opted_in=False)
    cfg2.write()

    print("\n=== Scenario 2: fresh install, opted_in=False (declined) ===")
    print(f"  {report2}")

    print("\n  --- verification (scenario 2) ---")
    v2 = configobj.ConfigObj('test.conf.weatherapi_xweather_declined', encoding='utf-8', file_error=True)
    if v2['WeatherAPI']['Xweather'].get('enabled') != 'False':
        print("  FAIL: expected enabled=False when declined")
        ok = False
    else:
        print("  OK: enabled=False derived correctly from decline")
    if 'client_id' in v2['WeatherAPI']['Xweather']:
        print("  FAIL: client_id should not have been invented when declined")
        ok = False
    else:
        print("  OK: no credentials written when declined")

    # --- Scenario 3: reinstall — user rotated client_secret by hand ---
    cfg3 = configobj.ConfigObj('test.conf.weatherapi_xweather_optedin', encoding='utf-8', file_error=True)
    cfg3['WeatherAPI']['Xweather']['client_secret'] = 'rotated-secret-999'
    cfg3.write()

    cfg3_reload = configobj.ConfigObj('test.conf.weatherapi_xweather_optedin', encoding='utf-8', file_error=True)
    report3 = apply_weatherapi_xweather_merge(
        cfg3_reload, html_root='/var/www/html/divumwx', opted_in=True,
        client_id='xw-client-abc', client_secret='xw-secret-xyz')
    cfg3_reload.write()

    print("\n=== Scenario 3: reinstall, user rotated client_secret by hand ===")
    print(f"  {report3}")

    print("\n  --- verification (scenario 3) ---")
    v3 = configobj.ConfigObj('test.conf.weatherapi_xweather_optedin', encoding='utf-8', file_error=True)
    if v3['WeatherAPI']['Xweather'].get('client_secret') != 'rotated-secret-999':
        print("  FAIL: user's rotated client_secret was overwritten")
        ok = False
    else:
        print("  OK: user's rotated client_secret respected")

    print("\n" + ("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED"))


# =====================================================================
# [DivumWXCards] merge — dashboard card selection
# =====================================================================
#
# Not a WeeWX service/config concern like everything above — this is
# purely which dashboard cards get shown. Stored in its own weewx.conf
# stanza per decision, to be templated into archive.json for the
# frontend to read.
#
# 8 mandatory cards, no prompt. Card 9 is a 3-way rain sensor choice
# (tipping bucket / piezo / both). The remaining 15 optional cards are
# prompted in the actual dashboard's script-tag order (verified against
# index.html — there's no static grid of divs; cards self-mount at
# runtime in script order, so that IS the visual grid order). Prompting
# is a SOFT ceiling: stop once 20 total are reached, but fewer than 20
# is fine if the user declines enough (per decision).

DIVUMWX_MANDATORY_CARDS = [
    'cardClockOutlook', 'cardCurrent', 'cardForecast', 'cardTemperature',
    'cardAnemometer', 'cardWindCompass', 'cardBarometer', 'cardHumidity',
]

# Not reliant on the station's own hardware sensors — either pure
# astronomy/almanac calculations (no sensor needed at all) or sourced
# from the Airquality/Earthquakes WeatherAPI services, which are already
# mandatory-always-on regardless of what hardware the station has. Added
# silently, same as the 8 mandatory cards, kept as a separate constant so
# the reasoning stays traceable to the original numbered list.
DIVUMWX_NO_SENSOR_OPTIONAL_CARDS = [
    'cardEarthDaylight', 'cardSolarDial', 'cardGeocentric', 'cardMoonPhase',  # almanac/ephemeris
    'cardPollen', 'cardGreenhouseGas', 'cardEarthquake',                      # from mandatory APIs
]

DIVUMWX_RAIN_CARD_CHOICES = {
    'tipping': ['cardRainfall'],
    'piezo': ['cardPiezoRain'],
    'both': ['cardRainfall', 'cardPiezoRain'],
}

# The genuinely optional, prompted pool — order verified against
# index.html's actual script-tag sequence, with the 7 no-sensor-required
# cards above removed (they're silently included, not prompted).
DIVUMWX_OPTIONAL_CARDS_ORDER = [
    'cardSolarRadiation', 'cardUvIndex', 'cardLightning', 'cardAirquality',
    'cardVapourPressureDeficit', 'cardEvapoTranspiration', 'cardWebcam', 'cardStationImage',
]

DIVUMWX_CARD_TARGET_TOTAL = 20


def compute_enabled_cards(rain_choice, optional_selected):
    """
    Pure assembly function — the actual "stop at 20" interactive loop
    lives in install.py (it's inherently stateful/sequential); this just
    takes whatever was decided and assembles the final ordered list.

    rain_choice: one of 'tipping' / 'piezo' / 'both'
    optional_selected: any subset of DIVUMWX_OPTIONAL_CARDS_ORDER that
        were answered "yes" (order in the input doesn't matter — output
        preserves DIVUMWX_OPTIONAL_CARDS_ORDER's canonical order)

    Returns (enabled_cards_list, count). The 8 mandatory cards and the 7
    no-sensor-required cards are always included, no prompt needed for
    either group.
    """
    cards = list(DIVUMWX_MANDATORY_CARDS)
    cards += DIVUMWX_NO_SENSOR_OPTIONAL_CARDS
    cards += DIVUMWX_RAIN_CARD_CHOICES.get(rain_choice, [])
    selected_set = set(optional_selected or [])
    cards += [c for c in DIVUMWX_OPTIONAL_CARDS_ORDER if c in selected_set]
    return cards, len(cards)


def apply_divumwx_cards_merge(cfg, rain_choice=None, optional_selected=None):
    """
    Mutates cfg['DivumWXCards'] in place. Set-once — a later hand-edit or
    a previous install's answer is respected on reinstall, same treatment
    as every other prompted preference in this build.

    Returns a report dict.
    """
    if 'DivumWXCards' not in cfg:
        cfg['DivumWXCards'] = {}
        created = True
    else:
        created = False
    dc = cfg['DivumWXCards']

    report = {
        'created_subsection': created,
        'rain_sensor_set': False,
        'rain_sensor_invalid': False,
        'rain_sensor_needs_prompt': False,
        'enabled_cards_set': False,
        'enabled_cards_needs_prompt': False,
        'card_count': None,
        'invalid_optional_cards': [],
    }

    # rain_sensor: set-once, validated against the 3 valid choices
    if 'rain_sensor' not in dc or not dc['rain_sensor']:
        if rain_choice in DIVUMWX_RAIN_CARD_CHOICES:
            dc['rain_sensor'] = rain_choice
            report['rain_sensor_set'] = True
        elif rain_choice:
            report['rain_sensor_invalid'] = True
            report['rain_sensor_needs_prompt'] = True
        else:
            report['rain_sensor_needs_prompt'] = True

    # enabled_cards: set-once, derived from the (validated) rain choice
    # actually stored, plus whichever optional cards were selected
    if 'enabled_cards' not in dc or not dc['enabled_cards']:
        effective_rain_choice = dc.get('rain_sensor')
        if effective_rain_choice:
            optional_selected = optional_selected or []
            invalid = [c for c in optional_selected if c not in DIVUMWX_OPTIONAL_CARDS_ORDER]
            valid_selected = [c for c in optional_selected if c in DIVUMWX_OPTIONAL_CARDS_ORDER]
            report['invalid_optional_cards'] = invalid

            cards, count = compute_enabled_cards(effective_rain_choice, valid_selected)
            dc['enabled_cards'] = cards
            report['enabled_cards_set'] = True
            report['card_count'] = count
        else:
            report['enabled_cards_needs_prompt'] = True

    return report


def _run_divumwx_cards_demo():
    import configobj
    import shutil

    ok = True

    # --- Scenario 1: fresh install, tipping bucket, first 4 optional cards
    #     accepted (15 always-included + 1 rain + 4 optional = 20 exactly) ---
    shutil.copy('test.conf', 'test.conf.cards_fresh')
    cfg1 = configobj.ConfigObj('test.conf.cards_fresh', encoding='utf-8', file_error=True)
    accepted_4 = DIVUMWX_OPTIONAL_CARDS_ORDER[:4]
    report1 = apply_divumwx_cards_merge(cfg1, rain_choice='tipping', optional_selected=accepted_4)
    cfg1.write()

    print("=== Scenario 1: fresh install, tipping bucket, 4 optional cards accepted (=20 total) ===")
    print(f"  {report1}")

    print("\n  --- verification (scenario 1) ---")
    v1 = configobj.ConfigObj('test.conf.cards_fresh', encoding='utf-8', file_error=True)
    dc1 = v1['DivumWXCards']
    enabled1 = list(dc1['enabled_cards'])
    checks1 = [
        (dc1.get('rain_sensor') == 'tipping', "rain_sensor=tipping"),
        (len(enabled1) == 20, f"exactly 20 cards ({len(enabled1)})"),
        ('cardRainfall' in enabled1, "cardRainfall included (tipping)"),
        ('cardPiezoRain' not in enabled1, "cardPiezoRain NOT included (tipping only)"),
        (all(c in enabled1 for c in DIVUMWX_MANDATORY_CARDS), "all 8 mandatory cards present"),
        (all(c in enabled1 for c in DIVUMWX_NO_SENSOR_OPTIONAL_CARDS),
         "all 7 no-sensor-required cards silently included, unprompted"),
        (enabled1.index('cardSolarRadiation') < enabled1.index('cardUvIndex'),
         "optional cards preserve canonical order"),
    ]
    for passed, label in checks1:
        print(f"  {'OK' if passed else 'FAIL'}: {label}")
        ok = ok and passed

    # --- Scenario 2: soft ceiling — user declines most optional cards,
    #     ends up with fewer than 20. Should be accepted, not padded. ---
    shutil.copy('test.conf', 'test.conf.cards_fewer')
    cfg2 = configobj.ConfigObj('test.conf.cards_fewer', encoding='utf-8', file_error=True)
    report2 = apply_divumwx_cards_merge(
        cfg2, rain_choice='both', optional_selected=['cardLightning', 'cardWebcam'])
    cfg2.write()

    print("\n=== Scenario 2: soft ceiling, declined most optional cards (both rain sensors, 2 optional) ===")
    print(f"  {report2}")

    print("\n  --- verification (scenario 2) ---")
    v2 = configobj.ConfigObj('test.conf.cards_fewer', encoding='utf-8', file_error=True)
    enabled2 = list(v2['DivumWXCards']['enabled_cards'])
    # 8 mandatory + 7 no-sensor (silent) + 2 rain (both) + 2 optional = 19
    if len(enabled2) != 19:
        print(f"  FAIL: expected 19 cards, got {len(enabled2)}")
        ok = False
    else:
        print("  OK: 19 cards total, fewer than 20, accepted as-is (soft ceiling)")
    if 'cardRainfall' not in enabled2 or 'cardPiezoRain' not in enabled2:
        print("  FAIL: 'both' rain choice should include both cards")
        ok = False
    else:
        print("  OK: both rain sensor cards included")

    # --- Scenario 3: fresh install, rain choice not yet known ---
    shutil.copy('test.conf', 'test.conf.cards_norain')
    cfg3 = configobj.ConfigObj('test.conf.cards_norain', encoding='utf-8', file_error=True)
    report3 = apply_divumwx_cards_merge(cfg3)
    cfg3.write()

    print("\n=== Scenario 3: fresh install, rain_choice not yet supplied ===")
    print(f"  {report3}")

    print("\n  --- verification (scenario 3) ---")
    v3 = configobj.ConfigObj('test.conf.cards_norain', encoding='utf-8', file_error=True)
    if 'rain_sensor' in v3.get('DivumWXCards', {}) or 'enabled_cards' in v3.get('DivumWXCards', {}):
        print("  FAIL: nothing should have been set without a rain choice")
        ok = False
    else:
        print("  OK: correctly left unset, both flagged needs_prompt")

    # --- Scenario 4: an invalid rain choice is rejected ---
    shutil.copy('test.conf', 'test.conf.cards_badrain')
    cfg4 = configobj.ConfigObj('test.conf.cards_badrain', encoding='utf-8', file_error=True)
    report4 = apply_divumwx_cards_merge(cfg4, rain_choice='ultrasonic', optional_selected=[])
    cfg4.write()

    print("\n=== Scenario 4: fresh install, invalid rain_choice='ultrasonic' ===")
    print(f"  {report4}")

    print("\n  --- verification (scenario 4) ---")
    v4 = configobj.ConfigObj('test.conf.cards_badrain', encoding='utf-8', file_error=True)
    if 'rain_sensor' in v4.get('DivumWXCards', {}):
        print("  FAIL: invalid rain_sensor should not have been written")
        ok = False
    else:
        print("  OK: invalid rain_choice rejected, flagged invalid + needs_prompt")

    # --- Scenario 5: reinstall — user hand-edited their card list; respected ---
    cfg5 = configobj.ConfigObj('test.conf.cards_fresh', encoding='utf-8', file_error=True)
    cfg5['DivumWXCards']['enabled_cards'] = DIVUMWX_MANDATORY_CARDS + ['cardRainfall']  # user trimmed it down
    cfg5.write()

    cfg5_reload = configobj.ConfigObj('test.conf.cards_fresh', encoding='utf-8', file_error=True)
    report5 = apply_divumwx_cards_merge(cfg5_reload, rain_choice='tipping', optional_selected=accepted_4)
    cfg5_reload.write()

    print("\n=== Scenario 5: reinstall, user hand-trimmed their card list ===")
    print(f"  {report5}")

    print("\n  --- verification (scenario 5) ---")
    v5 = configobj.ConfigObj('test.conf.cards_fresh', encoding='utf-8', file_error=True)
    if len(list(v5['DivumWXCards']['enabled_cards'])) != 9:
        print("  FAIL: user's hand-trimmed card list was overwritten")
        ok = False
    else:
        print("  OK: user's hand-trimmed card list (9 cards) respected, not reset to 20")

    print("\n" + ("  All checks passed" if ok else "  ONE OR MORE CHECKS FAILED"))


if __name__ == '__main__':
    _run_services_demo()
    print()
    _run_calculations_demo()
    print()
    _run_report_demo()
    print()
    _run_databinding_database_demo()
    print()
    _run_standalone_sections_demo()
    print()
    _run_datainject_demo()
    print()
    _run_livedata_demo()
    print()
    _run_livedata_prompted_interval_demo()
    print()
    _run_weatherapi_alerts_demo()
    print()
    _run_weatherapi_simple_demo()
    print()
    _run_weatherapi_metar_demo()
    print()
    _run_weatherapi_flood_demo()
    print()
    _run_weatherapi_aurorawatch_demo()
    print()
    _run_weatherapi_health_alert_demo()
    print()
    _run_weatherapi_metofficerss_demo()
    print()
    _run_weatherapi_xweather_demo()
    print()
    _run_divumwx_cards_demo()
