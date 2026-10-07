# Installer for the EcowittConsoleEmulator skin (WeeWX 5) - weewx-ecowitt_console_emulator
#
# Copyright (c) Ian Millard 2026
# Distributed under the terms of the GNU Public License (GPLv3)
#
#   weectl extension install weewx-ecowitt_console_emulator.zip
#
# Works with any driver for an Ecowitt gateway or console. If the optional
# weewx-EcowittGateway driver/service is installed, its settings are used too.
#
# During the install you are asked which rain sensor(s) the station has (with
# weewx-EcowittGateway, the default is its rain_source answer):
#   tipping  - traditional tipping-bucket gauge (WH40, WS69/WH65 array, WS80...)
#   piezo    - piezoelectric haptic gauge (WS90, WS85, WH40H...)
#   both     - both kinds; the dashboard's rain section gets a column for each
#
# and, only when weewx-EcowittGateway is installed, where live data comes from
# (otherwise it is always the skin's own live.json):
#   driver - the driver's ecwLoop.json (its [[loop_json]] option; turned on if needed)
#   skin   - the skin's own live.json, written by the EcowittConsoleEmulatorLive service
#
# The skin keeps its own database (binding ecce_binding). The installer
# checks whether that binding, database entry and database already exist and
# leaves any it finds untouched; WeeWX creates the database on first start only
# if it doesn't exist, with the same schema as the station's main database.
#
# For unattended installs give the answers on the command line:
#   weectl extension install weewx-ecowitt_console_emulator.zip --yes --rain=both --live=driver
#
import os
import posixpath
import sys

from weecfg.extension import ExtensionInstaller

VERSION = "1.0.0"
REPORT = 'EcowittConsoleEmulatorReport'
RAIN_CHOICES = ('tipping', 'piezo', 'both')
LIVE_CHOICES = ('driver', 'skin')
# Translations in skins/EcowittConsoleEmulator/lang (English is built into the page)
LANGUAGES = ('ar', 'br', 'ca', 'cn', 'cy', 'cz', 'da', 'de', 'en_US', 'es', 'eu', 'fi', 'fr', 'gr', 'hi',
             'hu', 'is', 'it', 'ja', 'nl', 'no', 'pl', 'pt', 'sv', 'ta', 'th', 'tr', 'uk', 'ur')
DRIVER_SECTIONS = ('EcowittGateway', 'EcowittHttp')
LOOP_NAME = 'ecwLoop.json'
BINDING = 'ecce_binding'
DB_SQLITE, DB_MYSQL = 'ecce_sqlite', 'ecce_mysql'
DB_FILE, DB_MYSQL_NAME = 'ecowitt_console_emulator.sdb', 'ecowitt_console_emulator'


def loader():
    return EcowittConsoleEmulatorInstaller()


class EcowittConsoleEmulatorInstaller(ExtensionInstaller):
    def __init__(self):
        skin = 'skins/EcowittConsoleEmulator'
        super().__init__(
            version=VERSION,
            name='ecowitt_console_emulator',
            description='Ecowitt console emulator: full-screen dashboard in the style of the Ecowitt HP2560 console, '
                        'for any Ecowitt gateway device.',
            author='Ian Millard',
            archive_services='user.ecowitt_console_emulator.EcowittConsoleEmulatorArchive',
            report_services='user.ecowitt_console_emulator.EcowittConsoleEmulatorLive',
            config={
                'StdReport': {
                    REPORT: {
                        'skin': 'EcowittConsoleEmulator',
                        'HTML_ROOT': 'console',
                        'enable': 'true',
                        # the dashboard and graphs read the skin's own database
                        'data_binding': BINDING,
                        'Extras': {},
                    }
                },
                'EcowittConsoleEmulator': {
                    'enable': 'false',
                    'report': REPORT,
                    'interval': '5',
                    'filename': 'live.json',
                    # the skin's own database, kept by EcowittConsoleEmulatorArchive
                    'data_binding': BINDING,
                    'source_binding': 'wx_binding',
                    'backfill_days': '400',
                    'driver_fields': 'true',
                },
                'DataBindings': {
                    BINDING: {
                        'database': DB_SQLITE,
                        'table_name': 'archive',
                        'manager': 'weewx.manager.DaySummaryManager',
                        'schema': 'user.ecowitt_console_emulator.schema',
                    },
                },
                'Databases': {
                    DB_SQLITE: {'database_name': DB_FILE, 'database_type': 'SQLite'},
                    DB_MYSQL: {'database_name': DB_MYSQL_NAME, 'database_type': 'MySQL'},
                },
            },
            files=[
                ('bin/user', ['bin/user/ecowitt_console_emulator.py']),
                (skin, [skin + '/skin.conf',
                        skin + '/index.html.tmpl',
                        skin + '/chart_day.json.tmpl',
                        skin + '/chart_week.json.tmpl',
                        skin + '/chart_month.json.tmpl',
                        skin + '/chart_year.json.tmpl',
                        skin + '/ecowitt.json.tmpl',
                        skin + '/ecce_payload.inc',
                        skin + '/manifest.json']),
                (skin + '/css', [skin + '/css/console.css']),
                (skin + '/js', [skin + '/js/console.js']),
                (skin + '/icons', [skin + '/icons/icon.svg']),
                (skin + '/lang', [skin + '/lang/%s.json' % c for c in LANGUAGES]),
            ],
        )
        self.rain_arg = None
        self.live_arg = None

    def process_args(self, args):
        """Accept --rain=tipping|piezo|both and --live=driver|skin for unattended installs."""
        for i, a in enumerate(args or []):
            for opt, choices, attr in (('--rain', RAIN_CHOICES, 'rain_arg'), ('--live', LIVE_CHOICES, 'live_arg')):
                val = None
                if a.startswith(opt + '='):
                    val = a.split('=', 1)[1]
                elif a == opt and i + 1 < len(args):
                    val = args[i + 1]
                if val is not None:
                    val = val.strip().lower()
                    if val not in choices:
                        raise ValueError("%s must be one of: %s" % (opt, ', '.join(choices)))
                    setattr(self, attr, val)

    def configure(self, engine):
        cfg = engine.config_dict
        driver = next((cfg[n] for n in DRIVER_SECTIONS if n in cfg), None)
        report = cfg.get('StdReport', {}).get(REPORT)
        extras = report.get('Extras', {}) if report is not None else {}
        interactive = sys.stdin is not None and sys.stdin.isatty()
        out = engine.printer.out
        changed = False

        if driver is None:
            out("EcowittConsoleEmulator: weewx-EcowittGateway not found; live data will come from the "
                "skin's own live.json")

        # --- rain sensor(s): default to the driver's own answer -------------
        current = extras.get('rain_sensors')
        drv_rain = str(driver.get('rain_source', '')).lower() if driver is not None else ''
        default = current if current in RAIN_CHOICES else drv_rain if drv_rain in RAIN_CHOICES else 'tipping'
        rain = self.rain_arg
        if rain is None:
            if interactive:
                print("\nWhich rain sensor does this station have?")
                print("  tipping - traditional tipping-bucket gauge (WH40, WS69, WN20 ...)")
                print("  piezo   - piezoelectric haptic gauge (WS90, WS85 ...)")
                print("  both    - both; the rain section shows a column for each")
                if drv_rain in RAIN_CHOICES:
                    print("  (the EcowittGateway driver is set to rain_source = %s)" % drv_rain)
                rain = _ask("Rain sensor (tipping/piezo/both)", default, RAIN_CHOICES)
            else:
                rain = default
        if rain == 'both' and drv_rain in ('tipping', 'piezo'):
            out("EcowittConsoleEmulator: note - the driver records only one gauge (rain_source = %s). Set its "
                "rain_source = both to archive the second gauge too." % drv_rain)
        out("EcowittConsoleEmulator: rain sensor(s) set to '%s'" % rain)

        # --- live data: the driver's ecwLoop.json or the skin's live.json ----
        loop = driver.get('loop_json', {}) if driver is not None else {}
        live_url, loop_settings = None, None
        if driver is not None:
            loop_path = _loop_json_path(cfg, loop if str(loop.get('enable', 'False')).lower() == 'true' else {})
            live_url = _url_from_page(cfg, loop_path)
        default_live = (extras.get('live_url') and ('skin' if extras.get('live_url') == 'live.json' else 'driver')) \
            or ('driver' if live_url else 'skin')
        live = self.live_arg
        if live is None:
            if interactive and driver is not None:
                print("\nWhere should the tablet get live (every few seconds) data from?")
                print("  driver - the driver's %s (its loop_json option%s)" %
                      (LOOP_NAME, ', already on' if str(loop.get('enable', 'False')).lower() == 'true'
                       else ', will be turned on'))
                print("  skin   - this skin's own live.json")
                live = _ask("Live data source (driver/skin)", default_live, LIVE_CHOICES)
            else:
                live = default_live if driver is not None else 'skin'
        loop_on = str(loop.get('enable', 'False')).lower() == 'true'
        if live == 'driver' and driver is None:
            out("EcowittConsoleEmulator: weewx-EcowittGateway not found, so using the skin's live.json")
            live = 'skin'
        elif live == 'driver' and not loop_on:
            # Turn the driver's loop file on, in the web folder; the skin reads any units
            loop_settings = {'enable': 'True', 'path': LOOP_NAME, 'units': 'metricwx'}
            live_url = _url_from_page(cfg, _loop_json_path(cfg, loop_settings))
        elif live == 'driver' and not live_url:
            out("EcowittConsoleEmulator: the driver's %s is written outside the web folder (%s), so the tablet "
                "can't read it. Using the skin's live.json instead." % (LOOP_NAME, loop.get('path')))
            live = 'skin'
        out("EcowittConsoleEmulator: live data from %s" %
            ("the driver's %s (%s)" % (LOOP_NAME, live_url) if live == 'driver' else "the skin's live.json"))

        # --- the skin's own database: check what already exists ------------
        changed |= self._configure_database(engine, cfg, out)

        # --- write the answers ----------------------------------------------
        answers = {'rain_sensors': rain, 'live_url': live_url if live == 'driver' else 'live.json'}
        service_enable = 'false' if live == 'driver' else 'true'
        self['config']['StdReport'][REPORT]['Extras'].update(answers)
        self['config']['EcowittConsoleEmulator']['enable'] = service_enable
        if engine.dry_run:
            return False
        if report is not None:
            report.setdefault('Extras', {})
            for k, v in answers.items():
                if report['Extras'].get(k) != v:
                    report['Extras'][k] = v
                    changed = True
        if 'EcowittConsoleEmulator' in cfg and cfg['EcowittConsoleEmulator'].get('enable') != service_enable:
            cfg['EcowittConsoleEmulator']['enable'] = service_enable
            changed = True
        if loop_settings:
            driver.setdefault('loop_json', {}).update(loop_settings)
            out("EcowittConsoleEmulator: turned on the driver's %s in the web folder" % LOOP_NAME)
            changed = True
        return changed


    def _configure_database(self, engine, cfg, out):
        """Set up the skin's database binding, leaving anything that already exists alone."""
        bindings = cfg.get('DataBindings', {})
        databases = cfg.get('Databases', {})
        if BINDING in bindings:
            db_name = bindings[BINDING].get('database')
            out("EcowittConsoleEmulator: binding [%s] already exists (database %s); leaving it unchanged" % (BINDING, db_name))
            db_entry = databases.get(db_name)
        else:
            # Same database type as the station's main database
            main_db = bindings.get('wx_binding', {}).get('database')
            main_type = str(databases.get(main_db, {}).get('database_type', 'SQLite'))
            db_name = DB_MYSQL if main_type.lower() == 'mysql' else DB_SQLITE
            self['config']['DataBindings'][BINDING]['database'] = db_name
            db_entry = databases.get(db_name) or self['config']['Databases'][db_name]
            if db_name in databases:
                out("EcowittConsoleEmulator: database entry [%s] already exists; leaving it unchanged" % db_name)
            out("EcowittConsoleEmulator: adding binding [%s] -> database %s (%s, same type as the main database)"
                % (BINDING, db_name, db_entry.get('database_type')))
        state = _database_state(cfg, db_name, db_entry)
        if state is True:
            out("EcowittConsoleEmulator: database '%s' already exists; it will be used as it is, not recreated"
                % db_entry.get('database_name'))
        elif state is False:
            out("EcowittConsoleEmulator: database '%s' doesn't exist yet; WeeWX will create it at the next start "
                "with the main database's schema, and copy recent history into it" % db_entry.get('database_name'))
        else:
            out("EcowittConsoleEmulator: couldn't check database '%s' (%s); WeeWX will check at start-up "
                "and create it only if it is missing" % (db_entry.get('database_name'), state))
        return False


def _database_state(cfg, db_name, db_entry):
    """True if the database exists, False if not, or a message if it can't be checked."""
    try:
        import weedb
        import weewx.manager
        probe = {'WEEWX_ROOT': cfg.get('WEEWX_ROOT', ''), 'DatabaseTypes': cfg.get('DatabaseTypes', {}),
                 'Databases': {db_name: dict(db_entry)}}
        db_dict = weewx.manager.get_database_dict_from_config(probe, db_name)
        try:
            weedb.connect(db_dict).close()
            return True
        except weedb.NoDatabaseError:
            return False
    except Exception as e:
        return str(e) or e.__class__.__name__


def _web_root(cfg):
    return os.path.normpath(os.path.join(cfg.get('WEEWX_ROOT', ''),
                                         cfg.get('StdReport', {}).get('HTML_ROOT', 'public_html')))


def _loop_json_path(cfg, loop):
    """Absolute path of the driver's ecwLoop.json, or None if it isn't enabled."""
    if str(loop.get('enable', 'False')).lower() != 'true':
        return None
    path = os.path.expanduser(str(loop.get('path', LOOP_NAME)))
    if not os.path.isabs(path):
        path = os.path.join(_web_root(cfg), path)
    if path.endswith(os.sep) or os.path.isdir(path) or not path.endswith('.json'):
        path = os.path.join(path, LOOP_NAME)
    return os.path.normpath(path)


def _url_from_page(cfg, loop_path):
    """URL of the loop file relative to the dashboard page, or None if it isn't in the web folder."""
    if not loop_path:
        return None
    web = _web_root(cfg)
    if os.path.commonpath([web, loop_path]) != web:
        return None
    report = cfg.get('StdReport', {}).get(REPORT, {})
    page = os.path.normpath(os.path.join(cfg.get('WEEWX_ROOT', ''), report.get('HTML_ROOT', os.path.join(web, 'console'))))
    return posixpath.join(*os.path.relpath(loop_path, page).split(os.sep))


def _ask(prompt, default, choices):
    while True:
        try:
            ans = input("%s [%s]: " % (prompt, default)).strip().lower()
        except EOFError:
            return default
        if not ans:
            return default
        for c in choices:
            if c.startswith(ans):          # accept the first letter(s)
                return c
        print("Please answer %s." % ' or '.join(choices))
