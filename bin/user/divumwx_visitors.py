"""
##############################################################################################
# divumwx_visitors.py version 1.0.0
#  Copyright (C) 2026 Ian Millard
#  GPLv3
##############################################################################################

Site visitor figures for DivumWX, from Cloudflare Web Analytics.

Cloudflare Web Analytics counts page views in each visitor's browser, so the
figures are right whatever sits between Cloudflare and the station: a
Cloudflare Tunnel (cloudflared), a reverse proxy, a NAT router. Nothing is
read from the web server's log and no IP address reaches the station.

VisitorsService runs on its own thread. Every poll_interval seconds it asks
Cloudflare's GraphQL Analytics API for the last 30 days and writes
jsondata/visitors.json in the DivumWX site: visits and page views for today,
7 days, 30 days and since the service started, a daily series, monthly
totals, and the top pages, countries, referring sites, browsers, operating
systems and device types. siteHeader.js shows "Visits: N today" in the footer
and visitorReport.js draws the report when that line is clicked.

It does nothing until an API token is set:

[DivumWXVisitors]
    api_token = <a Cloudflare API token with Account > Account Analytics > Read>
    # all optional
    account_id = auto      # found from the token when it reaches one account
    site_tag = auto        # the Web Analytics site; found from hostname
    hostname = auto        # the site's public hostname; auto = [Station] station_url
    poll_interval = 900    # seconds (minimum 300)
    site_root = divumwx    # DivumWX site folder below HTML_ROOT ('' if HTML_ROOT is it)
    json_file = jsondata/visitors.json

Daily totals are kept in divumwx_visitors_state.json beside the WeeWX
database, so "since" totals go back further than Cloudflare's own history.

Test a token without WeeWX:

    python3 divumwx_visitors.py --token <token> [--account <id>] [--site-tag <tag>] [--hostname <host>]
"""

import json
import logging
import os
import re
import tempfile
import threading
import time
import urllib.error
import urllib.request
from datetime import date, datetime, timedelta, timezone
from urllib.parse import urlparse

try:
    from weewx.engine import StdService
except ImportError:  # running standalone
    StdService = object

log = logging.getLogger(__name__)

VERSION = "1.0.0"
API_BASE = "https://api.cloudflare.com/client/v4"
USER_AGENT = "DivumWX-visitors/%s" % VERSION
DAYS = 30            # Cloudflare's free plans can be queried 30 days at a time
TOP_N = 15
HEX32 = re.compile(r"^[0-9a-f]{32}$")


def clean_token(raw):
    """(token, problem). Undoes the usual copy-and-paste slips: spaces or a line
    break inside it, quotes, the <...> from the example, a 'Bearer ' prefix.
    problem says why the value can't be an API token (None if it can be)."""
    t = re.sub(r"\s+", "", str(raw or ""))
    for _ in range(3):
        t = t.strip("\"'<>")
        if t.lower().startswith("bearer"):
            t = t[6:].lstrip(":")
    if not t:
        return t, "[DivumWXVisitors] api_token is empty"
    if re.fullmatch(r"[0-9a-f]{37}", t):
        return t, ("api_token looks like your Global API Key, which can't be used here. Create an "
                   "API token instead (My Profile > API Tokens > Create Token) with Account > "
                   "Account Analytics > Read")
    if t.lower().startswith("v1.0-"):
        return t, ("api_token looks like an Origin CA key, not an API token. Create an API token "
                   "(My Profile > API Tokens > Create Token) with Account > Account Analytics > Read")
    bad = sorted(set(re.sub(r"[A-Za-z0-9_.\-]", "", t)))
    if bad:
        return t, ("api_token contains %s, which an API token never has: copy it again from "
                   "Cloudflare" % " ".join(repr(c) for c in bad[:5]))
    return t, None


class CloudflareError(Exception):
    pass


def _gql_str(s):
    """A GraphQL string literal (JSON's quoting is valid GraphQL)."""
    return json.dumps(str(s))


class Cloudflare:
    """Just enough of Cloudflare's API to read Web Analytics figures."""

    def __init__(self, token, api_base=API_BASE, timeout=30):
        self.token, self.token_problem = clean_token(token)
        self.api_base = api_base.rstrip("/")
        self.timeout = timeout

    def _request(self, path, body=None):
        if self.token_problem:
            raise CloudflareError(self.token_problem)
        req = urllib.request.Request(
            self.api_base + path,
            data=None if body is None else json.dumps(body).encode("utf-8"),
            headers={"Authorization": "Bearer " + self.token, "User-Agent": USER_AGENT,
                     "Content-Type": "application/json", "Accept": "application/json"})
        try:
            with urllib.request.urlopen(req, timeout=self.timeout) as r:
                return json.loads(r.read().decode("utf-8"))
        except urllib.error.HTTPError as e:
            try:
                doc = json.loads(e.read().decode("utf-8"))
            except Exception:
                raise CloudflareError("Cloudflare answered HTTP %s" % e.code)
            errs = [m for m in (doc.get("errors") or []) if isinstance(m, dict)]
            msgs = []
            for m in errs:   # Cloudflare's reason is often in error_chain ('Invalid format for Authorization header')
                chain = [c.get("message", "") for c in m.get("error_chain") or [] if isinstance(c, dict)]
                msgs.append(" - ".join(filter(None, [m.get("message", "")] + chain)))
            codes = {m.get("code") for m in errs} | {c.get("code") for m in errs
                                                     for c in m.get("error_chain") or [] if isinstance(c, dict)}
            if codes & {6003, 6100, 6101, 6102, 6103, 6111}:
                raise CloudflareError("Cloudflare couldn't read the API token (%s). Use an API token, not "
                                      "the Global API Key, and copy all of it into [DivumWXVisitors] "
                                      "api_token on one line, with nothing around it."
                                      % ("; ".join(filter(None, msgs)) or "HTTP %s" % e.code))
            if e.code in (401, 403):
                raise CloudflareError("Cloudflare refused the API token (%s). Check that it has "
                                      "Account > Account Analytics > Read."
                                      % ("; ".join(filter(None, msgs)) or "HTTP %s" % e.code))
            raise CloudflareError("; ".join(filter(None, msgs)) or "Cloudflare answered HTTP %s" % e.code)
        except (urllib.error.URLError, OSError) as e:
            raise CloudflareError("Couldn't reach Cloudflare: %s" % getattr(e, "reason", e))
        except ValueError:
            raise CloudflareError("Cloudflare sent something that isn't JSON")

    def accounts(self):
        doc = self._request("/accounts?per_page=50")
        if not doc.get("success", True):
            raise CloudflareError("; ".join(m.get("message", "") for m in doc.get("errors") or []) or "accounts lookup failed")
        return [(a.get("id"), a.get("name")) for a in doc.get("result") or [] if a.get("id")]

    def graphql(self, query):
        doc = self._request("/graphql", {"query": query})
        errors = doc.get("errors") or []
        if errors:
            raise CloudflareError("; ".join(str(e.get("message", e)) if isinstance(e, dict) else str(e)
                                            for e in errors))
        data = doc.get("data") or {}
        accts = (data.get("viewer") or {}).get("accounts") or []
        if not accts:
            raise CloudflareError("Cloudflare returned no account data: is the account ID right, and "
                                  "does the token cover that account?")
        return accts[0]


# ----------------------------------------------------------------------------- queries

def _filter(site_tag, start, end, bot, extra=None):
    parts = []
    if site_tag:
        parts.append("siteTag: %s" % _gql_str(site_tag))
    if extra:
        parts.append(extra)
    else:
        parts.append("date_geq: %s, date_leq: %s" % (_gql_str(start), _gql_str(end)))
    if bot:
        parts.append("bot: 0")
    return "{ %s }" % ", ".join(parts)


def _group(alias, flt, dims, limit, extra=""):
    d = " dimensions { %s }" % dims if dims else ""
    return ("%s: rumPageloadEventsAdaptiveGroups(filter: %s, limit: %d%s) "
            "{ count sum { visits }%s%s }" % (alias, flt, limit,
                                             ", orderBy: [count_DESC]" if dims else "", d, extra))


def build_query(account_id, site_tag, start, end, bot=True):
    f = _filter(site_tag, start, end, bot)
    groups = [
        _group("daily", f, "date", 60, " avg { sampleInterval }"),
        _group("pages", f, "requestPath", 200),
        _group("countries", f, "countryName", 200),
        _group("referrers", f, "refererHost", 200),
        _group("browsers", f, "userAgentBrowser", 100),
        _group("os", f, "userAgentOS", 100),
        _group("devices", f, "deviceType", 20),
        _group("hosts", f, "requestHost", 20),
    ]
    return "{ viewer { accounts(filter: { accountTag: %s }) { %s } } }" % (_gql_str(account_id), " ".join(groups))


def build_today_query(account_id, site_tag, since_utc, bot=True):
    f = _filter(site_tag, None, None, bot, "datetime_geq: %s" % _gql_str(since_utc))
    return "{ viewer { accounts(filter: { accountTag: %s }) { %s } } }" % (
        _gql_str(account_id), _group("today", f, None, 1))


def build_sites_query(account_id, start, end):
    f = _filter(None, start, end, False)
    return "{ viewer { accounts(filter: { accountTag: %s }) { %s } } }" % (
        _gql_str(account_id), _group("sites", f, "siteTag requestHost", 100))


# ----------------------------------------------------------------------------- the report

def _rows(acct, alias):
    return acct.get(alias) or []


def _num(row):
    return int(row.get("count") or 0), int((row.get("sum") or {}).get("visits") or 0)


def _top(rows, key, skip=()):
    out = []
    for r in rows:
        name = ((r.get("dimensions") or {}).get(key))
        name = "" if name is None else str(name)
        if name in skip:
            continue
        views, visits = _num(r)
        out.append({"name": name, "views": views, "visits": visits})
    out.sort(key=lambda x: (-x["views"], -x["visits"], x["name"]))
    return out[:TOP_N]


def _span(days_map, today, n):
    v = s = 0
    for i in range(n):
        views, visits = days_map.get((today - timedelta(days=i)).isoformat(), (0, 0))
        v += views
        s += visits
    return {"views": v, "visits": s}


def build_document(acct, today_row, history, now=None, host=None, notes=None):
    """visitors.json from Cloudflare's answer, merging the daily rows into history."""
    now = now or datetime.now(timezone.utc)
    utc_today = now.date()
    fresh = {}
    sample = 1.0
    for r in _rows(acct, "daily"):
        d = (r.get("dimensions") or {}).get("date")
        if d:
            fresh[d] = list(_num(r))
            sample = max(sample, float((r.get("avg") or {}).get("sampleInterval") or 1))
    history.update(fresh)                    # newest wins; older days stay
    days_map = {d: tuple(v) for d, v in history.items()}
    daily = []
    for i in range(DAYS - 1, -1, -1):
        d = (utc_today - timedelta(days=i)).isoformat()
        views, visits = days_map.get(d, (0, 0))
        daily.append({"date": d, "views": views, "visits": visits})
    months = {}
    for d, (views, visits) in days_map.items():
        m = months.setdefault(d[:7], [0, 0])
        m[0] += views
        m[1] += visits
    monthly = [{"month": m, "views": v[0], "visits": v[1]} for m, v in sorted(months.items())][-12:]
    today = _span(days_map, utc_today, 1)
    if today_row is not None:                # since the station's midnight, not UTC's
        views, visits = _num(today_row)
        today = {"views": views, "visits": visits}
    own_hosts = {str((r.get("dimensions") or {}).get("requestHost") or "") for r in _rows(acct, "hosts")}
    own_hosts.discard("")
    return {
        "version": VERSION,
        "generated": int(time.time()),
        "source": "cloudflare",
        "error": None,
        "notes": notes or [],
        "host": host or (sorted(own_hosts)[0] if own_hosts else None),
        "sampled": sample > 1,
        "sample_interval": sample,
        "today": today,
        "week": _span(days_map, utc_today, 7),
        "month": _span(days_map, utc_today, DAYS),
        "total": {"views": sum(v[0] for v in days_map.values()),
                  "visits": sum(v[1] for v in days_map.values()),
                  "since": min(days_map) if days_map else None},
        "daily": daily,
        "monthly": monthly,
        "pages": _top(_rows(acct, "pages"), "requestPath"),
        "countries": _top(_rows(acct, "countries"), "countryName"),
        # visits arriving from the site's own pages are navigation, not referrals
        "referrers": _top(_rows(acct, "referrers"), "refererHost", skip=own_hosts),
        "browsers": _top(_rows(acct, "browsers"), "userAgentBrowser"),
        "os": _top(_rows(acct, "os"), "userAgentOS"),
        "devices": _top(_rows(acct, "devices"), "deviceType"),
    }


# ----------------------------------------------------------------------------- fetching

class Visitors:
    def __init__(self, token, account_id="auto", site_tag="auto", hostname="", api_base=API_BASE,
                 state=None):
        self.cf = Cloudflare(token, api_base)
        self.account_id = (account_id or "auto").strip().lower()
        self.site_tag = (site_tag or "auto").strip().lower()
        self.hostnames = [h.strip().lower() for h in re.split(r"[,\s]+", hostname or "") if h.strip()]
        self.state = state if state is not None else {}
        self.use_bot_filter = True
        self.notes = []

    def resolve_account(self):
        if self.account_id not in ("", "auto"):
            if not HEX32.match(self.account_id):
                raise CloudflareError("account_id should be the 32-character Account ID from the "
                                      "Cloudflare dashboard")
            return self.account_id
        if HEX32.match(self.state.get("account_id") or ""):
            return self.state["account_id"]
        accts = self.cf.accounts()
        if not accts:
            raise CloudflareError("The API token doesn't reach any Cloudflare account")
        if len(accts) > 1:
            raise CloudflareError("The API token reaches %d accounts (%s): set account_id in "
                                  "[DivumWXVisitors]" % (len(accts), ", ".join(n or i for i, n in accts)))
        self.state["account_id"] = accts[0][0]
        return accts[0][0]

    def resolve_site(self, account, today):
        if self.site_tag not in ("", "auto"):
            return self.site_tag
        cached = self.state.get("site_tag")
        if cached and self.state.get("site_hosts") == self.hostnames:
            return cached
        acct = self.cf.graphql(build_sites_query(account, (today - timedelta(days=DAYS - 1)).isoformat(),
                                                 today.isoformat()))
        tags = {}
        for r in _rows(acct, "sites"):
            dims = r.get("dimensions") or {}
            tag, host = dims.get("siteTag"), str(dims.get("requestHost") or "").lower()
            if not tag:
                continue
            t = tags.setdefault(tag, {"views": 0, "hosts": set()})
            t["views"] += int(r.get("count") or 0)
            t["hosts"].add(host)
        if not tags:
            raise CloudflareError("Cloudflare has no Web Analytics data for this account in the last %d days. "
                                  "Turn on Web Analytics for the site (Cloudflare dashboard > Web Analytics > "
                                  "Add a site), then visit it once." % DAYS)
        chosen = None
        if self.hostnames:
            matches = [t for t, v in tags.items() if v["hosts"] & set(self.hostnames)]
            if not matches:
                seen = sorted(set().union(*(v["hosts"] for v in tags.values())) - {""})
                raise CloudflareError("No Web Analytics data for %s; Cloudflare has data for: %s. "
                                      "Set hostname in [DivumWXVisitors] to the site's address."
                                      % (", ".join(self.hostnames), ", ".join(seen) or "-"))
            chosen = max(matches, key=lambda t: tags[t]["views"])
        else:
            chosen = max(tags, key=lambda t: tags[t]["views"])
            if len(tags) > 1:
                self.notes.append("This Cloudflare account has %d Web Analytics sites; showing the busiest "
                                  "(%s). Set hostname in [DivumWXVisitors] to choose."
                                  % (len(tags), ", ".join(sorted(tags[chosen]["hosts"] - {""})) or chosen))
        self.state["site_tag"] = chosen
        self.state["site_hosts"] = self.hostnames
        return chosen

    def _graphql(self, make):
        try:
            return self.cf.graphql(make(self.use_bot_filter))
        except CloudflareError as e:
            if self.use_bot_filter and "bot" in str(e).lower():
                self.use_bot_filter = False       # schema without the bot field: Cloudflare filters bots anyway
                return self.cf.graphql(make(False))
            raise

    def fetch(self, now=None):
        self.notes = []
        now = now or datetime.now(timezone.utc)
        utc_today = now.date()
        account = self.resolve_account()
        site = self.resolve_site(account, utc_today)
        start = (utc_today - timedelta(days=DAYS - 1)).isoformat()
        acct = self._graphql(lambda bot: build_query(account, site, start, utc_today.isoformat(), bot))
        today_row = None
        try:
            local_midnight = datetime.now().astimezone().replace(hour=0, minute=0, second=0, microsecond=0)
            since = local_midnight.astimezone(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")
            rows = _rows(self._graphql(lambda bot: build_today_query(account, site, since, bot)), "today")
            today_row = rows[0] if rows else {"count": 0, "sum": {"visits": 0}}
        except CloudflareError as e:
            log.debug("visitors: today since local midnight unavailable (%s); using the UTC day", e)
        history = self.state.setdefault("days", {})
        host = self.hostnames[0] if self.hostnames else None
        return build_document(acct, today_row, history, now=now, host=host, notes=self.notes)


# ----------------------------------------------------------------------------- files

def write_json(path, doc):
    folder = os.path.dirname(path)
    os.makedirs(folder, exist_ok=True)
    fd, tmp = tempfile.mkstemp(prefix=".visitors-", suffix=".tmp", dir=folder)
    with os.fdopen(fd, "w", encoding="utf-8") as fh:
        json.dump(doc, fh, ensure_ascii=False, separators=(",", ":"))
    os.chmod(tmp, 0o644)   # readable by the web server, like the other jsondata files
    os.replace(tmp, path)


def read_json(path, default):
    try:
        with open(path, encoding="utf-8") as fh:
            v = json.load(fh)
        return v if isinstance(v, type(default)) else default
    except (OSError, ValueError):
        return default


def _host_of(url):
    try:
        return (urlparse(str(url)).hostname or "").lower()
    except ValueError:
        return ""


class VisitorsService(StdService):
    """Writes jsondata/visitors.json from Cloudflare Web Analytics."""

    def __init__(self, engine, config_dict):
        super().__init__(engine, config_dict)
        own = config_dict.get("DivumWXVisitors", {}) or {}
        self._stop = threading.Event()
        token = str(own.get("api_token", "") or "").strip()
        html_root = config_dict.get("StdReport", {}).get("HTML_ROOT", "public_html")
        weewx_root = config_dict.get("WEEWX_ROOT", ".")
        if not os.path.isabs(html_root):
            html_root = os.path.join(weewx_root, html_root)
        site_root = str(own.get("site_root", "divumwx")).strip()
        site = os.path.join(html_root, site_root) if site_root else html_root
        self.json_file = os.path.join(site, own.get("json_file", "jsondata/visitors.json"))
        sqlite_root = config_dict.get("DatabaseTypes", {}).get("SQLite", {}).get("SQLITE_ROOT", "archive")
        if not os.path.isabs(sqlite_root):
            sqlite_root = os.path.join(weewx_root, sqlite_root)
        self.state_file = own.get("state_file") or os.path.join(sqlite_root, "divumwx_visitors_state.json")
        try:
            self.poll_interval = max(300, int(own.get("poll_interval", 900)))
        except ValueError:
            self.poll_interval = 900
        if not token:
            log.debug("visitors: no [DivumWXVisitors] api_token; not running")
            return
        hostname = str(own.get("hostname", "auto") or "auto").strip()
        if hostname.lower() == "auto":
            hostname = _host_of(config_dict.get("Station", {}).get("station_url", ""))
        self.visitors = Visitors(token, own.get("account_id", "auto"), own.get("site_tag", "auto"),
                                 hostname, own.get("api_base", API_BASE),
                                 state=read_json(self.state_file, {}))
        self._thread = threading.Thread(target=self._run, name="DivumWXVisitors", daemon=True)
        self._thread.start()
        log.info("visitors %s: Cloudflare Web Analytics%s, writing %s every %ss", VERSION,
                 " for " + hostname if hostname else "", self.json_file, self.poll_interval)

    def shutDown(self):
        self._stop.set()

    def _run(self):
        while not self._stop.is_set():
            try:
                self.update()
            except Exception as e:  # never take WeeWX down
                log.exception("visitors: update failed: %s", e)
            self._stop.wait(self.poll_interval)

    def update(self):
        try:
            doc = self.visitors.fetch()
        except CloudflareError as e:
            log.error("visitors: %s", e)
            # keep the last good figures, with the reason they've stopped changing
            doc = read_json(self.json_file, {})
            doc.update({"version": VERSION, "source": "cloudflare", "error": str(e)})
            doc.setdefault("generated", None)
        else:
            try:
                write_json(self.state_file, self.visitors.state)
            except OSError as e:
                log.error("visitors: cannot write %s: %s", self.state_file, e)
        try:
            write_json(self.json_file, doc)
        except OSError as e:
            log.error("visitors: cannot write %s: %s", self.json_file, e)
        return doc


# ----------------------------------------------------------------------------- command line

if __name__ == "__main__":
    import argparse
    import sys

    p = argparse.ArgumentParser(description="Check a Cloudflare API token and print the visitor "
                                            "report DivumWX would publish.")
    p.add_argument("--token", required=True, help="API token with Account > Account Analytics > Read")
    p.add_argument("--account", default="auto", help="Account ID (default: found from the token)")
    p.add_argument("--site-tag", default="auto", help="Web Analytics site tag (default: found)")
    p.add_argument("--hostname", default="", help="the site's public hostname, e.g. weather.example.com")
    p.add_argument("--api-base", default=API_BASE, help=argparse.SUPPRESS)
    p.add_argument("--json", action="store_true", help="print the whole visitors.json document")
    args = p.parse_args()
    logging.basicConfig(level=logging.INFO)
    v = Visitors(args.token, args.account, args.site_tag, args.hostname, args.api_base)
    try:
        doc = v.fetch()
    except CloudflareError as e:
        print("Error:", e)
        sys.exit(1)
    if args.json:
        print(json.dumps(doc, ensure_ascii=False, indent=1))
        sys.exit(0)
    print("account_id = %s" % v.state.get("account_id", args.account))
    print("site_tag   = %s%s" % (v.state.get("site_tag", args.site_tag),
                                 "   (host %s)" % doc["host"] if doc.get("host") else ""))
    for n in doc["notes"]:
        print("note:", n)
    print("today: %(visits)d visits, %(views)d page views" % doc["today"])
    print("7 days: %(visits)d visits, %(views)d page views" % doc["week"])
    print("30 days: %(visits)d visits, %(views)d page views" % doc["month"])
    if doc["sampled"]:
        print("(Cloudflare sampled 1 in %g page loads; figures are estimates)" % doc["sample_interval"])
    for key in ("pages", "countries", "referrers"):
        print("%s: %s" % (key, ", ".join("%s %d" % (r["name"] or "-", r["views"]) for r in doc[key][:5]) or "-"))
