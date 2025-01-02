import os
import sys
import logging
import argparse
import subprocess
import time
import datetime
import traceback
import json
import re
import getpass
import grp
import pwd
import shutil
import distutils
from datetime import datetime

version = "4.5.25.000"
srvGenURL = 'https://www.divumwx.org/settingsGen/'

# Define ANSI codes
reset = "\033[0m"
bold = "\033[1m"
underline = "\033[4m"
black = "\033[30m"
red = "\033[31m"
green = "\033[32m"
yellow = "\033[33m"
blue = "\033[34m"
magenta = "\033[35m"
cyan = "\033[36m"
white = "\033[37m"

def setup_logging():
    timestamp = datetime.now().strftime("%Y%m%d-%H%M")
    parser = argparse.ArgumentParser(description="DivumWX Installation Script - This script installs the DivumWX skin for the Amateur Weather Station software, weewx.")
    parser.add_argument("--debug", action="store_true", help="Enable logging at DEBUG level")
    parser.add_argument("--info", action="store_true", help="Enable logging at INFO level")
    args = parser.parse_args()

    if args.debug:
        logging.basicConfig(
            filename=f'./dvmwxInstall_{timestamp}.log',
            level=logging.DEBUG,
            format='[%(asctime)s] [%(levelname)-5s] [%(message)s]',
            datefmt='%Y-%m-%d %H:%M:%S',
        )
    elif args.info:
        logging.basicConfig(
            filename=f'./dvmwxInstall_{timestamp}.log',
            level=logging.INFO,
            format='[%(asctime)s] [%(levelname)-5s] [%(message)s]',
            datefmt='%Y-%m-%d %H:%M:%S',
        )
    else:
        logging.basicConfig(level=logging.CRITICAL)  # Suppress all logs by default

    return args
args = setup_logging()
os.system("clear")
logging.debug("+-------------------- Debug logging enabled for DivumWX Installer --------------------+")
logging.debug(f"+----------------------- DivumWX Installer Version: {version} -----------------------+")
print(f"{white}DIS {cyan}(DivumWX Installation Script) {white}starting.....{reset}")
print(f"{yellow}Standby, importing and verifying required python modules...{reset}")

def modMissing(module_name):
    logging.debug(f"Module '{module_name}' is not installed or is incorrect.")
    print(f"{red}Module '{module_name}' is not installed or is incorrect.{reset}")
    install = input(f"Do you want to install the missing or correct version of module '{module_name}'? (y/n): ").strip().lower()
    if install == 'y':
        try:
            print(f"Installing '{module_name}'...")
            logging.debug(f"Installing '{module_name}'...")
            subprocess.check_call([sys.executable, "-m", "pip", "install", "--upgrade", module_name])
            print(f"{green}Module '{module_name}' installed successfully.{reset}")
            logging.debug(f"Module '{module_name}' installed successfully.")
            print(f"{green}Restarting script...{reset}")
            logging.debug(f"Restarting script...")
            os.execl(sys.executable, sys.executable, *sys.argv)
        except subprocess.CalledProcessError as e:
            logging.debug(f"Failed to install module '{module_name}': {e}")
            print(f"{red}Failed to install module '{module_name}': {e}{reset}")
            logging.debug(f"Exiting the script.")
            sys.exit(1)
    else:
        print(f"{red}The module '{module_name}' was not installed. The script cannot continue and will exit.{reset}")
        logging.debug(f"Exiting the script.")
        sys.exit(1)
try:
    import readchar
except ImportError:
    modMissing("readchar")
    import readchar
try:
    import curses
except ImportError:
    modMissing("curses")
    import curses
try:
    from configobj import ConfigObj
except ImportError:
    modMissing("configobj")
    from configobj import ConfigObj
try:
    from crontab import CronTab
    if not hasattr(CronTab, '__version__') or "python-crontab" not in str(CronTab):
        import pkg_resources
        installed_crontab = pkg_resources.get_distribution("crontab")
        invalid_package_name = f"{installed_crontab.project_name}-{installed_crontab.version}-py3-none-any"
        print(f"{red}Incorrect crontab module detected: {invalid_package_name}. It must be 'python-crontab'.{reset}")
        logging.debug(f"Incorrect crontab module detected: {invalid_package_name}.")
        print(f"{yellow}Removing the incorrect crontab module...{reset}")
        logging.debug("Attempting to uninstall the incorrect crontab module...")
        subprocess.check_call([sys.executable, "-m", "pip", "uninstall", "-y", "crontab"])
        print(f"{green}Incorrect crontab module removed successfully.{reset}")
        logging.debug("Incorrect crontab module removed successfully.")
        raise ImportError("Removed incorrect crontab module. Installing python-crontab.")
except ImportError:
    modMissing("python-crontab")
    from crontab import CronTab
print(f"{green}Python module import complete...{reset}")

class DVMInstaller:
    def __init__(self, conf_file=None):
        self.waitFKP()
        self.viewFile('./INSTALLATION_GUIDE.md', 40)
        self.services_file = self.chkSvcFile()
        self.prnWelMsg()
        self.conf_files = {}
        self.chkPyVer()
        self.chkVenv()
        conf_file = 'installData.json'
        self.run_installer(conf_file)

    def waitFKP(self):
        print("Press any key to continue:")
        key = readchar.readkey()

    def viewFile(self, file_path, lines_per_page=20):
        logging.debug(f"Displaying Installation guide")
        banner_text = "Installation Guide (Up/Down arrows to navigate, 'q' to quit)"
        curses.wrapper(self.file_viewer, file_path, banner_text, lines_per_page)

    def chkSvcFile(self):
        logging.debug(f"Checking for services.json file")
        services_file = None
        if os.path.exists("../services.json"):
            services_file = "../services.json"
            logging.debug(f"services.json has been located")
        elif os.path.exists("services.json"):
            services_file = "services.json"
            logging.debug(f"services.json has been located")
        else:
            print(f"{yellow}You are missing the required services.json file. Please go to {red}[{white}{srvGenURL}{red}]{white}{reset}")
            print(f"{yellow}and follow the instructions there to create the services.json file for your station. Copy that file to the {green}same{reset}")
            print(f"{yellow}directory that {white}dvmInstaller.py{yellow} is in and run {white}dvmInstaller.py{yellow} again.{reset}")
            logging.debug(f"services.json was not located, script exiting.")
            sys.exit(1)
        return services_file
    
    def file_viewer(self, stdscr, file_path, banner_text, lines_per_page=20):
        curses.curs_set(0)
        stdscr.clear()
        curses.start_color()

        curses.init_pair(1, curses.COLOR_YELLOW, curses.COLOR_BLACK)
        curses.init_pair(2, curses.COLOR_WHITE, curses.COLOR_BLACK)
        curses.init_pair(3, curses.COLOR_RED, curses.COLOR_BLACK)
        
        try:
            with open(file_path, 'r') as file:
                lines = file.readlines()

            total_lines = len(lines)
            max_y, max_x = stdscr.getmaxyx()
            lines_per_page = min(lines_per_page, max_y - 2)
            current_line = 0

            while True:
                stdscr.clear()
                stdscr.addstr(0, 0, banner_text[:max_x-1], curses.color_pair(1) | curses.A_BOLD | curses.A_UNDERLINE)

                for i in range(lines_per_page):
                    line_number = current_line + i
                    if line_number < total_lines:
                        stdscr.addstr(i + 1, 0, lines[line_number][:max_x-1], curses.color_pair(2))  # Use text color

                stdscr.refresh()
                key = stdscr.getch()
                
                if key == curses.KEY_DOWN or key == ord('j'):
                    if current_line + lines_per_page < total_lines:
                        current_line += 1
                elif key == curses.KEY_UP or key == ord('k'):
                    if current_line > 0:
                        current_line -= 1
                elif key == ord('q'):
                    break

        except FileNotFoundError:
            stdscr.addstr(0, 0, "Error: File not found. Press any key to exit.", curses.color_pair(3))
            stdscr.refresh()
            stdscr.getch()

    def prnWelMsg(self):
        logging.debug(f"Displaying Welcome Message")
        os.system("clear")
        print(f"{green}Welcome to the DivumWX Skin Installer {white}v{cyan}{version}{reset}")
        print(f"\n{white}Overview{reset}\n")
        print("The `dvm_installer.py` script is a powerful and automated tool designed to streamline the installation")
        print("and configuration of the DivumWX skin for the WeeWX amateur weather software.")
        print("This script helps in setting up essential components, updating configurations, and ensuring smooth")
        print("deployment of the weather data management system, which integrates seamlessly with WeeWX.\n")
        print(f"{white}Features:{reset}")
        print(f" - {yellow}Automated Installation{reset}: Automates the extraction and placement of necessary files from a provided zip file.")
        print(f" - {yellow}Configuration Management{reset}: Updates configuration files with specified settings, ensuring your system is")
        print("     correctly configured for operation.")
        print(f" - {yellow}Permissions Management{reset}: Recursively applies appropriate file and directory permissions.")
        print(f" - {yellow}Interactive Prompts{reset}: Guides you through critical decisions with easy-to-follow prompts, ensuring clarity")
        print("     and user control.")
        print(f" - {yellow}Error Handling{reset}: Provides comprehensive error messages and handles exceptions gracefully to aid in")
        print("     troubleshooting during installation.\n")
        print(f"{white}Let's get started!{reset}\n")
        self.waitFKP()

    def chkPyVer(self):
        logging.debug(f"Checking Python version")
        current_version = sys.version_info
        required_version = (3, 10)
        if current_version >= required_version:
            logging.debug(f"Correct python version found")
            print(f"{yellow}Python version {white}{current_version.major}.{current_version.minor}.{current_version.micro} {yellow}meets the minimum required version of {green}3.10.x. {yellow}Proceeding with installation.{reset}")
        else:
            logging.debug(f"Incorrect python version found, script exiting.")
            print(f"{red}Python version {white}{current_version.major}.{current_version.minor}.{current_version.micro} {red}does not meet the minimum required version of {white}3.10.x{red}. Installation aborted.{reset}")
            sys.exit(1)

    def chkVenv(self):
        logging.debug(f"Checking Python virtual environment")
        if not hasattr(sys, 'real_prefix') and not (hasattr(sys, 'base_prefix') and sys.base_prefix != sys.prefix):
            print(f"{red}This script is not running inside a Python virtual environment. Please ensure that you have activated the virtual WeeWX environment by running the following command before proceeding.{reset}")
            print(f"{white}source /home/weewx/bin/activate{reset}")
            logging.debug(f"The script is not running inside the Python Virtual Environment, script exiting.")
            sys.exit(1)
        else:
            logging.debug(f"The script is operating inside a Python Virtual Environment, proceeding")
            print(f"{white}This script is running inside a Python virtual environment. Proceeding with installation.{reset}")

        weewxd_path = os.path.expanduser('~/weewx-venv/bin/weewxd')
        weewx_version = subprocess.run([weewxd_path, '--version'], capture_output=True, text=True).stdout.strip()
        required_version = [5, 1, 0]
        current_version = list(map(int, weewx_version.split('.')))
        if current_version < required_version:
            print(f"{red}WeeWX version {weewx_version} is not supported. Please upgrade to version 5.1.0 or higher and try again.{reset}")
            sys.exit(1)
        else:
            print(f"{white}WeeWX version {yellow}{weewx_version}{white} is supported. Proceeding with installation.{reset}")
        
    def chkWebsrv(self, wsName=None, wsOwner=None, wsGroup=None):
        try:
            result = subprocess.run(
                ["ps", "-eo", "comm=,user=,group=,ppid="],
                capture_output=True,
                text=True
            )

            if result.returncode == 0:
                processes = result.stdout.splitlines()
                web_server_parent = None
                web_server_child = None

                for line in processes:
                    parts = line.split()
                    if len(parts) < 4:
                        continue

                    command, user, group, ppid = parts[0], parts[1], parts[2], parts[3]
                    if command in ["apache2", "nginx"] and user == "root":
                        logging.debug(f"Found webserver root process")
                        web_server_parent = (command, user, group)

                    if command in ["apache2", "nginx"] and user != "root":
                        logging.debug(f"Found webserver child process")
                        web_server_child = (command, user, group)

                # If parameters are provided, validate against them
                if wsName or wsOwner or wsGroup:
                    if web_server_parent and web_server_parent[0] != wsName:
                        return False, None, None, None, None, None
                    if web_server_child and web_server_child[1] != wsOwner:
                        return False, None, None, None, None, None
                    if web_server_parent and web_server_parent[2] != wsGroup:
                        return False, None, None, None, None, None

                if web_server_parent and web_server_child:
                    logging.debug(f"Webserver Type: {web_server_parent[0]}, Webserver Root: {web_server_parent[1]}:{web_server_parent[2]}, Webserver Child: {web_server_child[1]}:{web_server_child[2]}")
                    return True, web_server_parent[0], web_server_parent[1], web_server_parent[2], web_server_child[1], web_server_child[2]
                logging.debug(f"Webserver Type: None, Webserver Root: None:None, Webserver Child: None:None")
                return False, None, None, None, None, None

            else:
                logging.debug(f"Webserver Type: None, Webserver Root: None:None, Webserver Child: None:None")
                return False, None, None, None, None, None

        except Exception:
            logging.debug(f"Webserver Type: None, Webserver Root: None:None, Webserver Child: None:None")
            return False, None, None, None, None, None


    def getWebRoot(self):
        def find_file(search_paths, filename):
            for path in search_paths:
                potential_path = os.path.join(path, filename)
                if os.path.exists(potential_path):
                    return potential_path
            return None

        main_document_root = None
        document_roots = {}

        possible_paths = [
            "/etc/apache2",
            "/etc/httpd/conf",
            "/usr/local/apache2/conf",
            "/etc/nginx"
        ]
        
        httpd_conf_path = find_file(possible_paths, "httpd.conf")
        nginx_conf_path = find_file(possible_paths, "nginx.conf")
        
        if httpd_conf_path:
            try:
                with open(httpd_conf_path, 'r') as file:
                    for line in file:
                        match = re.match(r'^\s*DocumentRoot\s+(.+)', line)
                        if match:
                            main_document_root = match.group(1).strip()
                            break
            except Exception:
                pass

        if nginx_conf_path and not main_document_root:
            try:
                with open(nginx_conf_path, 'r') as file:
                    for line in file:
                        match = re.match(r'^\s*root\s+(.+);', line)
                        if match:
                            main_document_root = match.group(1).strip()
                            break
            except Exception:
                pass

        sites_enabled_dirs = [
            "/etc/apache2/sites-enabled",
            "/usr/local/apache2/sites-enabled",
            "/etc/nginx/sites-enabled"
        ]

        for sites_enabled_dir in sites_enabled_dirs:
            if os.path.exists(sites_enabled_dir) and os.path.isdir(sites_enabled_dir):
                try:
                    doc_root_counter = 1
                    for filename in os.listdir(sites_enabled_dir):
                        filepath = os.path.join(sites_enabled_dir, filename)

                        if os.path.isfile(filepath):
                            with open(filepath, 'r') as file:
                                for line in file:
                                    match = re.match(r'^\s*(DocumentRoot|root)\s+(.+);?', line)
                                    if match:
                                        document_root = match.group(2).strip()
                                        variable_name = f"DocumentRoot{doc_root_counter}"
                                        document_roots[variable_name] = document_root
                                        doc_root_counter += 1
                                        break

                except Exception:
                    pass

        return main_document_root, document_roots

    def chkWeewx(self, wxName='weewx'):
        try:
            result = subprocess.run(
                ["ps", "-eo", "user:20,group:20,args"],
                capture_output=True,
                text=True
            )
            if result.returncode == 0:
                processes = result.stdout.splitlines()
                for line in processes:
                    if wxName in line:
                        fields = line.split(None, 2)
                        wxOwner = fields[0]
                        wxGroup = fields[1]
                        return True, wxName, wxOwner, wxGroup
                return False, wxName, None, None
            else:
                return False, wxName, None, None
        except Exception:
            return False, wxName, None, None

    def createCron(self, html_root):
        logging.debug("Entering CronTab setup function")
        username = os.getlogin()
        home_dir = os.path.expanduser("~")
        script_dir = os.path.join(home_dir, "scripts")
        script_path = os.path.join(script_dir, "getWeewxVersion.sh")
        output_file = os.path.join(html_root, "weewxVer.txt")
        os.makedirs(script_dir, exist_ok=True)
        
        bash_script_content = f"""#!/bin/bash
    # Enter the pip virtual env and get the WeeWX version
    cd ~/weewx-venv/bin || exit 1
    source ~/weewx-venv/bin/activate
    ./weewxd --version > {output_file} 2>&1
    deactivate
    """
        logging.debug("Writing bash script for cron job")
        with open(script_path, "w") as script_file:
            script_file.write(bash_script_content)
        
        os.chmod(script_path, 0o755)
        
        logging.debug("Creating cron entry")
        cron = CronTab(user=username)
        job = cron.new(command=f"{script_path}", comment="WeeWX Version Fetch")
        job.setall("0 2 * * *")
        cron.write()
        
        logging.debug("Test run of script to grab initial WeeWX version")
        subprocess.run([script_path], check=True)
        
        if os.path.exists(output_file):
            with open(output_file, "r") as version_file:
                version = version_file.read().strip()
                logging.debug(f"Current WeeWX version: {version}")
                return version
        else:
            logging.debug("Version file was not created by the script.")
            return "Unknown"

    def dirNotEmpty(self, path):
        return os.path.isdir(path) and bool(os.listdir(path))

    def deleteContents(self, path):
        for root, dirs, files in os.walk(path):
            for file in files:
                file_path = os.path.join(root, file)
                os.remove(file_path)

            for dir in dirs:
                dir_path = os.path.join(root, dir)
                shutil.rmtree(dir_path)

    def chgPermRecur(self, path_list, uid_gid):
        user, group = uid_gid
        try:
            uid = pwd.getpwnam(user).pw_uid
            gid = grp.getgrnam(group).gr_gid
        except KeyError:
            print(f"User or group {uid_gid} not found, skipping permission change.")
            return

        for p in path_list:
            os.chmod(p, 0o755)
            os.chown(p, uid, gid)
            for root, dirs, files in os.walk(p, topdown=False):
                for directory in (os.path.join(root, d) for d in dirs):
                    os.chmod(directory, 0o775)
                    os.chown(directory, uid, gid)
                    if "json_day" in directory:
                        os.chmod(directory, 0o777)
                for filename in (os.path.join(root, f) for f in files):
                    os.chmod(filename, 0o777 if "dvm_reports" in filename else 0o755)
                    os.chown(filename, uid, gid)

    def setfnlOwner(self, locations):
        logging.debug(f"Setting Final Ownership for DivumWX files and directories")
        websrvDRpth = locations.get("www")
        db_path_web = os.path.join(websrvDRpth, "admin/db/dvmAdmin.db3")


        chkPaths = [websrvDRpth]

        for path in chkPaths:
            if not os.path.exists(path):
                continue

            dvmFilePath = os.path.join(path, "dvmVersion.php")
            if os.path.isfile(dvmFilePath):
                try:
                    logging.debug(f"Attempting to change ownership of {path} to {self.wsCOwner}:{self.wsCGroup}")
                    print(f"{yellow}Attempting to change ownership of {path} to {self.wsCOwner}:{self.wsCGroup}{reset}")
                    os.system(f"sudo chown -R {self.wsCOwner}:{self.wsCGroup} {path}")
                    logging.debug(f"Successfully changed ownership of {path} to {self.wsCOwner}:{self.wsCGroup}")
                    print(f"{green}Successfully changed ownership of {path} to {self.wsCOwner}:{self.wsCGroup}{reset}\n")
                    logging.debug(f"Attempting to change permissions of {path} to 0775")
                    print(f"{yellow}Attempting to change permissions of {path} to 0775{reset}")
                    os.system(f"sudo chmod -R 0775 {path}")
                    logging.debug(f"Successfully changed permissions of {path} to 0775")
                    print(f"{green}Successfully changed permissions of {path} to 0775{reset}\n")
                    logging.debug(f"Attempting to change permissions of {db_path_web} to 0666")
                    print(f"{yellow}Attempting to change permissions of {db_path_web} to 0666{reset}")
                    os.system(f"sudo chmod 0666 {db_path_web}")
                    logging.debug(f"Successfully changed permissions of {db_path_web} to 0666")
                    print(f"{green}Successfully changed permissions of {db_path_web} to 0666{reset}\n")

                except Exception as e:
                    print(f"{red}Error: {str(e)}{reset}")
                    logging.debug(f"There was an error attempting to change ownership or permissions for {path}")
                    print(f"{red}There was an error attempting to change ownership or permissions for {path}.{reset}")
                    print(f"{red}Your web pages will not be able to be displayed unless these commands are run successfully.{reset}")

                    chown_command = f"sudo chown -R {self.wsCOwner}:{self.wsCGroup} {path}"
                    chmod_command = f"sudo chmod -R 0775 {path}"
                    chmod_command_db = f"sudo chmod 0666 {db_path_web}"

                    print(f"\n{yellow}To manually attempt the changes, run the following commands for {path}:{reset}\n")
                    print(f"  - Change ownership to: {green}{self.wsCOwner}:{self.wsCGroup}{reset}")
                    print(f"  - Command: {cyan}{chown_command}{reset}")
                    print(f"  - Change permissions to: {green}0775{reset}")
                    print(f"  - Command: {cyan}{chmod_command}{reset}")
                    print(f"\nFile: {blue}{chmod_command_db}{reset}")
                    print(f"  - Change permissions to: {green}0666{reset}")
                    print(f"\n{red}IMPORTANT:{reset} Your web pages will not be displayed until the above commands are executed successfully.\n")
                break
    
    def chkUgrp(self):
        self.user = getpass.getuser()
        try:
            user_info = pwd.getpwnam(self.user)
            uid = user_info.pw_uid
            gid = user_info.pw_gid
            self.group = grp.getgrgid(gid).gr_name
        except KeyError:
            print(f"Cannot find user or group information for {self.user}")
            sys.exit(1)
        logging.debug(f"For the purposes of file permissions and ownership, we will be using {self.user} and {self.group}.")
        print(f"\n{white}For the purposes of file permissions and ownership, we will be using the below listed user and group.{reset}")
        print(f"Detected user: {white}{self.user}{reset}")
        print(f"Detected group: {white}{self.group}{reset}")
        confirm = input("Is this correct? (yes/no): ").strip().lower()
        if confirm in ['yes', 'y']:
            return (self.user, self.group)
        else:
            self.user = input("Please enter the correct user: ").strip()
            self.group = input("Please enter the correct group: ").strip()
            logging.debug(f"User entered information")
            logging.debug(f"For the purposes of file permissions and ownership, we will be using {self.user} and {self.group}.")
            return (self.user, self.group)

    def chgHroot(self, d, html_root):
        def recursive_update(d, key_to_update, new_value):
            for key, value in d.items():
                if isinstance(value, dict):
                    recursive_update(value, key_to_update, new_value)
                elif key == key_to_update:
                    if not value:
                        d[key] = new_value
                    else:
                        if not value.startswith("/"):
                            d[key] = f"{new_value}/{value}"
                        else:
                            d[key] = f"{new_value}{value}"
        recursive_update(d, "HTML_ROOT", html_root)
        recursive_update(d, "filename", html_root)
        
    def addStanza(self, config_data, entries):
        if logging.getLogger().isEnabledFor(logging.DEBUG):
            logging.debug(f"Adding new DivumWX specific stanzas to weewx config")
        for i in range(5, 11):  # config_entries4 to config_entries9
            if logging.getLogger().isEnabledFor(logging.DEBUG):
                logging.debug(f"Adding stanza #{i}")
            entry = entries[f'config_entries{i}']
            for section, values in entry.items():
                for key, value in values.items():
                    if isinstance(value, dict):
                        for sub_key, sub_value in value.items():
                            value[sub_key] = sub_value
                    values[key] = value
                config_data[section] = values

    def addBkpStanza(self, config_data, entries):
        logging.info(f"Backup Service stanza added")
        entry = entries['backupEntry1']
        for section, values in entry.items():
            for key, value in values.items():
                if isinstance(value, dict):
                    for sub_key, sub_value in value.items():
                        value[sub_key] = sub_value
                values[key] = value
            config_data[section] = values

    def appendStanza(self, config_data, entries, do_overwrite):
        if logging.getLogger().isEnabledFor(logging.DEBUG):
            logging.debug(f"Adding DivumWX specific stanzas to weewx config")
        for i in range(5):  # config_entries0 to config_entries4
            if logging.getLogger().isEnabledFor(logging.DEBUG):
                logging.debug(f"Adding entry #{i}")
            entry = entries[f'config_entries{i}']
            for section, values in entry.items():
                if section in config_data:
                    for key, value in values.items():
                        if isinstance(value, dict):
                            if key in config_data[section]:
                                for sub_key, sub_value in value.items():
                                    if sub_key in config_data[section][key]:
                                        existing_value = config_data[section][key][sub_key]
                                        if do_overwrite or existing_value != sub_value:
                                            config_data[section][key][sub_key] = sub_value
                                    else:
                                        config_data[section][key][sub_key] = sub_value
                            else:
                                config_data[section][key] = value
                        else:
                            if key in config_data[section]:
                                existing_value = config_data[section][key]
                                if isinstance(existing_value, list):
                                    existing_value.append(value)
                                else:
                                    config_data[section][key] = f"{existing_value}, {value}"
                            else:
                                config_data[section][key] = value
                else:
                    config_data[section] = values

    def appendSvcs(self, config_data, entries):
        if logging.getLogger().isEnabledFor(logging.DEBUG):
            logging.debug(f"Appending DivumWX specific services to weewx config")
        count = 0
        for key, value in entries.items():
            count += 1
            if logging.getLogger().isEnabledFor(logging.DEBUG):
                logging.debug(f"Appending entry #{count}")
            if key.startswith("config_entries_append"):
                service_type, service_value = value.split("=")
                service_type = service_type.strip()
                service_value = service_value.strip()

                if service_type in config_data['Engine']['Services']:
                    existing_value = config_data['Engine']['Services'][service_type]
                    if existing_value in [',', '""']:
                        config_data['Engine']['Services'][service_type] = service_value
                    else:
                        config_data['Engine']['Services'][service_type] += f", {service_value}"
                else:
                    config_data['Engine']['Services'][service_type] = service_value
                if config_data['Engine']['Services'].get('data_services') == ',':
                    config_data['Engine']['Services']['data_services'] = '""'
        if logging.getLogger().isEnabledFor(logging.DEBUG):
            logging.debug(f"Total entries appended: {count}")

    def appendBkpSvcs(self, config_data, entries):
        for key, value in entries.items():
            logging.info(f"DivumWX Backup service appended to weewx config file")
            if key.startswith("backupScvs"):
                service_type, service_value = value.split("=")
                service_type = service_type.strip()
                service_value = service_value.strip()

                if service_type in config_data['Engine']['Services']:
                    existing_value = config_data['Engine']['Services'][service_type]
                    if existing_value in [',', '""']:
                        config_data['Engine']['Services'][service_type] = service_value
                    else:
                        config_data['Engine']['Services'][service_type] += f", {service_value}"
                else:
                    config_data['Engine']['Services'][service_type] = service_value

                if config_data['Engine']['Services'].get('data_services') == ',':
                    config_data['Engine']['Services']['data_services'] = '""'

    def updDatabase(self):
        logging.debug(f"Updating weewx database with additional columns for DivumWX Skin")
        logging.debug(f"The following columns will be added to the database to support the DivumWX Skin:")
        logging.debug(f"AirDensity, stormRain, threshold, cloudcover, is_sunshine & sunshine_time")
        print(f"{white}Updating weewx database with additional columns for DivumWX Skin{reset}")
        print(f"{white}The following columns will be added to the database to support the DivumWX Skin:{reset}")
        print(f"{cyan}AirDensity, stormRain, threshold, cloudcover, is_sunshine & sunshine_time{reset}")
        columns = ["AirDensity", "stormRain", "threshold", "cloudcover", "is_sunshine", "sunshine_time"]
        current_user = os.getenv("USER")
        base_command = f"/home/{current_user}/weewx-venv/bin/weectl database add-column"

        for column in columns:
            try:
                command = f"{base_command} {column} -y"
                result = subprocess.run(command, shell=True, capture_output=True, text=True)

                if result.returncode == 0:
                    print(f"New column {column} of type REAL added to database.")
                    logging.debug(f"New column {column} of type REAL added to database.")
                else:
                    if "duplicate column name" in result.stderr:
                        logging.debug(f"Column {column} already exists in the database.")
                        print(f"Column {column} already exists in the database.")
                    else:
                        logging.debug(f"Error occurred while adding column {column}: {result.stderr}")
                        print(f"Error occurred while adding column {column}: {result.stderr}")

            except Exception as e:
                print(f"An exception occurred: {e}")

    def startWeewx(self):
        restart = input("Are you ready to start the WeeWX service to apply changes? (yes/no): ").strip().lower()
        if restart in ['yes', 'y']:
            try:
                print("Attempting to restart WeeWX service...")
                logging.debug(f"Attempting to restart WeeWX service...")
                subprocess.check_call(["sudo", "systemctl", "start", "weewx"])
                print("WeeWX service started successfully.")
                logging.debug(f"WeeWX service started successfully.")
            except subprocess.CalledProcessError as e:
                print(f"Failed to start the WeeWX service: {e}")
                logging.debug(f"Failed to start the WeeWX service: {e}")
        else:
            print("WeeWX service start was not requested. Please remember to start WeeWX manually for changes to take effect.")
            logging.debug(f"WeeWX service start was not requested. Please remember to start WeeWX manually for changes to take effect.")

    def stopWeewx(self):
        try:
            print("Stopping the WeeWX service...")
            logging.debug(f"Stopping the WeeWX service...")
            subprocess.check_call(["sudo", "systemctl", "stop", "weewx"])
        except subprocess.CalledProcessError as e:
            logging.debug(f"Failed to stop the WeeWX service: {e}")
            print(f"Failed to stop the WeeWX service: {e}")
    
    def getEnabledSkins(self, weewx_config_file):
        report_configs = []
        inside_std_report = False
        current_section = None
        
        with open(weewx_config_file, 'r') as file:
            lines = file.readlines()

        for line in lines:
            if "[StdReport]" in line:
                inside_std_report = True
                continue
            
            if "[StdConvert]" in line:
                break

            if inside_std_report:
                if line.strip().startswith("[[") and line.strip().endswith("]]"):
                    current_section = line.strip()[2:-2]
                elif current_section and "enable" in line:
                    parts = line.strip().split('=')
                    if len(parts) == 2:
                        key, value = parts[0].strip(), parts[1].strip().lower()
                        if key == "enable":
                            report_configs.append({"section": current_section, "enabled": value})
                            current_section = None

        return report_configs
            
    def run_installer(self, conf_file):
        self.user, self.group = self.chkUgrp()
        weewxRunning, self.wxName, self.wxOwner, self.wxGroup = self.chkWeewx()
        if weewxRunning:
            logging.debug(f"Process {self.wxName} is running, owned by {self.wxOwner}, in group {self.wxGroup}.")
        else:
            logging.debug(f"Process {self.wxName} is not running or there was an issue finding the weewx process.")
        runningWbsrv, wsName, wsPOwner, wsPGroup, wsCOwner, wsCGroup = self.chkWebsrv()
        if runningWbsrv:
            print(f"{white}I think that you're running the {yellow}{wsName}{white} webserver.{reset}")
            print(f"{white}It's parent process is {green}{wsPOwner}:{wsPGroup}{reset}")
            print(f"{white}And its child process is {green}{wsCOwner}:{wsCGroup}{reset}")
            print(f"{cyan}Is this correct?{reset}")
            wsIsCorrect = input("yes/no: ").strip().lower()
            if wsIsCorrect in ['yes', 'y']:
                self.wsName = wsName
                self.wsCOwner = wsCOwner
                self.wsCGroup = wsCGroup
                mainDocumentRoot, siteDocumentRoots = self.getWebRoot()
                if mainDocumentRoot:
                    logging.debug(f"Main DocumentRoot: {mainDocumentRoot}")
                else:
                    logging.debug(f"No Main DocumentRoot")
                if siteDocumentRoots:
                    for key, value in siteDocumentRoots.items():
                        logging.debug(f"{key}: {value}")
                else:
                    logging.debug("No site-specific DocumentRoots found.")
            else:
                while True:
                    self.wsName = input("Please enter the web server name (or 'q' to quit): ").strip()
                    logging.debug(f"User entered wsName: {self.wsName}")
                    if self.wsName.lower() == 'q':
                        logging.debug(f"Exiting script.")
                        print("Exiting script.")
                        sys.exit(0)
                    self.wsOwner = input("Please enter the web server owner (or 'q' to quit): ").strip()
                    logging.debug(f"User entered wsOwner: {self.wsOwner}")
                    if self.wsOwner.lower() == 'q':
                        logging.debug(f"Exiting script")
                        print("Exiting script.")
                        sys.exit(0)
                    self.wsGroup = input("Please enter the web server group (or 'q' to quit): ").strip()
                    logging.debug(f"User entered wsGroup: {self.wsGroup}")
                    if self.wsGroup.lower() == 'q':
                        logging.debug(f"Exiting script")
                        print("Exiting script.")
                        sys.exit(0)
                    if self.chkWebsrv(self.wsName, self.wsOwner, self.wsGroup):
                        logging.debug(f"User entered webserver found")
                        break
                    else:
                        print(f"{red}The web server '{self.wsName}' owned by '{self.wsOwner}' of '{self.wsGroup}' is not running. Please check the details and try again.{reset}")
                        logging.debug(f"User entered web server not found")
        else:
            print(f"{red}I was unable to locate either Apache2 or Nginx running. Please enter the webserver name and the user and group that owns the process.{reset}")
            logging.debug(f"Unable to locate either Apache2 or Nginx running on this server")
        print(f"{white}Loading installation configuration files.....{reset}")
        logging.debug(f"Loading installation configuration files.....")
        try:
            with open(conf_file) as infile:
                logging.debug(f"Loading installData.json")
                conf_content = infile.read()
                conf_content = re.sub(r'##.*\n', '', conf_content).replace("\n", "").replace("\t", "")
                d = json.loads(conf_content)
            with open(self.services_file) as infile:
                logging.debug(f"Loading services.json")
                services_content = infile.read()
                services_content = re.sub(r'##.*\n', '', services_content).replace("\n", "").replace("\t", "")
                d.update(json.loads(services_content))
            logging.debug(f"Setting user, skins and weewx_conf paths")
            user_path = os.path.expanduser(d["user"])
            skins_path = os.path.expanduser(d["skins"])
            weewx_config_file = os.path.expanduser(d["weewx_config_file"])
            weewx_www_root = os.path.expanduser(d["weewx_www_root"])
            if logging.getLogger().isEnabledFor(logging.DEBUG):
                logging.debug(f"Conf Paths:")
                logging.debug(f"        user_path: {user_path}")
                logging.debug(f"       skins_path: {skins_path}")
                logging.debug(f"weewx_config_file: {weewx_config_file}")
                logging.debug(f"   weewx_www_root: {weewx_www_root}")
            config_data = ConfigObj(weewx_config_file, encoding='utf8', list_values=False, write_empty_values=True)
            os.system("clear")
            logging.debug(f"Determining if any other skins are enabled already.")
            allSkins = self.getEnabledSkins(weewx_config_file)
            enabledSkins = [skin["section"] for skin in allSkins if skin["enabled"] == "true"]
            print(f"{green}+-------------------------------------------------------------------------------+{reset}")
            print(f"{white}The following skins are enabled on your system:{reset}")
            logging.debug(f"The following skins are enabled")
            for skin in enabledSkins:
                logging.debug(f"     - {skin}")
                print(f"{cyan} - {skin}{reset}")
            print("")
            print(f"{white}Your weewx.conf file [StdReport] section has {yellow}{config_data['StdReport'].get('HTML_ROOT')}{white} as your HTML_ROOT setting.{reset}", end="\n")
            logging.debug(f"The HTML_ROOT setting in weewx.conf is {config_data['StdReport'].get('HTML_ROOT')}")
            print(f"{green}To preserve any existing skin that you have, we will be adding {magenta}/divumwx{white} to the{reset}")
            print(f"{green}current {yellow}HTML_ROOT{white} setting.{reset}", end="\n\n")
            wxConfHTMLRoot = config_data['StdReport'].get('HTML_ROOT')
            if wxConfHTMLRoot.lower() == 'public_html':
                html_root = os.path.join(weewx_www_root , "divumwx")
            else:
                html_root = os.path.join(wxConfHTMLRoot, "divumwx")
            logging.debug(f"DivumWX new HTML_ROOT: {html_root}")
            print(f"Final HTML_ROOT: {html_root}")
            locations = {
                "user": user_path,
                "skins": skins_path,
                "www": html_root
            }
            user_path_exists = os.path.exists(user_path)
            skins_path_exists = os.path.exists(skins_path)
            html_root_exists = os.path.exists(html_root)
            html_root_created = False
            if not html_root_exists:
                try:
                    logging.debug(f"Creating DivumWX html directory")
                    os.makedirs(html_root)
                    html_root_exists = True
                    html_root_created = True
                except Exception as e:
                    logging.debug(f"Error creating DivumWX directory")
                    print(f"Error creating directory {html_root}: {e}")
            weewx_config_file_exists = os.path.exists(weewx_config_file)
            self.waitFKP()
            if html_root_exists and html_root_created:
                html_root_status_message = "HTML_ROOT was created"
                html_root_status_color = yellow
            elif html_root_exists:
                html_root_status_message = "Path exists"
                html_root_status_color = green
            else:
                html_root_status_message = "Path does not exist or could not be created"
                html_root_status_color = red
            os.system("clear")
            bkpWXSrv = False
            print(f"{green}+-------------------------------------------------------------------------------+{reset}")
            bkpWX = input('Do you wish to use the Divum Backup Service to backup your weewx database? (yes/no): ').strip().lower()
            if bkpWX in ['yes', 'y']:
                logging.debug(f"DivumWX Backup service enabled")
                bkpWXSrv = True
                home_directory = os.path.expanduser('~')
                subdirectory_path = 'weewx-data/archive'
                file_name = 'weewx.sdb'
                while True:
                    try:
                        dir_path = input('Please enter the directory path for your backup file (Directory Path only, no filename): ').strip()
                        if not os.path.isdir(dir_path):
                            print("The directory does not exist. Please enter a valid directory path.")
                        else:
                            break
                    except Exception as e:
                        print(f"An error occurred: {e}. Please try again.") 
                default_name = "weewx.sdb.bak"
                while True:
                    try:
                        file_name = input(f"Please enter the filename for your backup file (default: {default_name}): ").strip()
                        if not file_name:
                            file_name = default_name
                        bkpName = os.path.join(dir_path, file_name)
                        if os.path.isfile(bkpName):
                            print(f"The file '{file_name}' already exists in the directory.")
                        else:
                            break
                    except Exception as e:
                        print(f"An error occurred: {e}. Please try again.") 
                while True:
                    bkpTime = input('Please enter the time you want the backup to take place (e.g., 03:00): ').strip()
                    if re.match(r'^([01][0-9]|2[0-3]):([0-5][0-9])$', bkpTime):
                        try:
                            datetime.strptime(bkpTime, '%H:%M')
                            break
                        except ValueError:
                            print("The time is not valid. Please ensure it is a valid 24-hour time.")
                    else:
                        print("Invalid time format. Please enter the time in HH:MM format (24-hour clock).")
                        
                wxMainDB =  os.path.join(home_directory, subdirectory_path, file_name)
                wxBkpDB = bkpName
                wxBkpTime = bkpTime

                d["backupEntry1"]["DVM_DB_Backup"]["databases"] = wxMainDB
                d["backupEntry1"]["DVM_DB_Backup"]["backups"] = wxBkpDB
                d["backupEntry1"]["DVM_DB_Backup"]["backup_times"] = wxBkpTime
            else:
                print(f"{yellow}DivumWX Backup service disabled{reset}")
                self.waitFKP()
            os.system("clear")
            print(f"{blue}+--------------------------------------------------------------------------------------+{reset}")
            print(f"{green}Summary of Gathered Information:{reset}\n")
            print(f"        {cyan}User Path:{reset} {green}{user_path}{reset}")
            print(f"           Status: {green if user_path_exists else red}{'Path Exists' if user_path_exists else 'Path does not exist'}{reset}")
            print(f"       {cyan}Skins Path:{reset} {green}{skins_path}{reset}")
            print(f"           Status: {green if skins_path_exists else red}{'Path Exists' if skins_path_exists else 'Path does not exist'}{reset}")
            print(f"   {cyan}HTML Root Path:{reset} {green}{html_root}{reset}")
            print(f"           Status: {html_root_status_color}{html_root_status_message}{reset}")
            if html_root_created:
                print(f"      Note: {white}Your webserver DOCUMENT_ROOT may need to be changed to point to:{reset}")
                print(f"            {green}{html_root}{reset}")
            print(f"{cyan}WeeWX Config File:{reset} {green}{weewx_config_file}{reset}")
            print(f"           Status: {green}{'Yes' if weewx_config_file_exists else f'{red}No'}{reset}")
            print(f"{blue}+--------------------------------------------------------------------------------------+{reset}")
            print(f"            {cyan}Detected user:{reset} {white}{self.user}{reset}")
            print(f"      {cyan}Detected user group:{reset} {white}{self.group}{reset}")
            print(f"          {cyan}Webserver:{reset} {green}{self.wsName}{reset}")
            print(f"    {cyan}Webserver Owner:{reset} {green}{self.wsCOwner}{reset}")
            print(f"    {cyan}Webserver Group:{reset} {green}{self.wsCGroup}{reset}")
            print(f"{blue}+--------------------------------------------------------------------------------------+{reset}")
            print(f"{cyan}DivumWX Backup Service Status:{reset}\n")
            if bkpWXSrv:
                print(f"        {white}DivumWX Backup Service:  {green}Enabled{reset}")
                print(f"{white}DivumWX Backup Path & Filename:  {green}{bkpName}{reset}")
                print(f"     {white}DivumWX Backup Start Time:  {green}{bkpTime}{reset}")
            else:
                print(f"        {white}DivumWX Backup Service:  {yellow}Disabled{reset}")
            print("\n\nExplanation of Next Steps:\n")
            print(f"{green}The script will now perform the following actions:{reset}")
            print(f"{yellow}1. Check that the destination html_root path is empty.{reset}")
            print(f"{yellow}2. Copy the existing/extracted files to the appropriate directories.{reset}")
            print(f"{yellow}3. Apply the correct file and directory permissions.{reset}")
            print(f"{yellow}4. Update the WeeWX configuration based on the provided settings.{reset}")
            print(f"{yellow}5. Verify new directory and file user/group ownership settings for webserver access.{reset}")
            print(f"{yellow}6. Add additional columns to weewx database to support DivumWX skin .{reset}\n")
            logging.debug(f"+--------------------------------------------------------------------------------------+")
            logging.debug(f"Summary of Gathered Information:")
            logging.debug(f"        User Path: {user_path}")
            logging.debug(f"           Status: {'Path Exists' if user_path_exists else 'Path does not exist'}")
            logging.debug(f"       Skins Path: {skins_path}")
            logging.debug(f"           Status: {'Path Exists' if skins_path_exists else 'Path does not exist'}")
            logging.debug(f"   HTML Root Path: {html_root}")
            logging.debug(f"           Status: {html_root_status_message}")
            if html_root_created:
                logging.debug(f"      Note: Your webserver DOCUMENT_ROOT may need to be changed to point to:")
                logging.debug(f"            {html_root}")
            logging.debug(f"WeeWX Config File: {weewx_config_file}")
            logging.debug(f"           Status: {'Yes' if weewx_config_file_exists else 'No'}")
            logging.debug(f"+--------------------------------------------------------------------------------------+")
            logging.debug(f"            Detected user: {self.user}")
            logging.debug(f"      Detected user group: {self.group}")
            logging.debug(f"          Webserver: {self.wsName}")
            logging.debug(f"    Webserver Owner: {self.wsCOwner}")
            logging.debug(f"    Webserver Group: {self.wsCGroup}")
            logging.debug(f"+--------------------------------------------------------------------------------------+")
            logging.debug(f"DivumWX Backup Service Status:")
            if bkpWXSrv:
                logging.debug(f"        DivumWX Backup Service:  Enabled")
                logging.debug(f"DivumWX Backup Path & Filename:  {bkpName}")
                logging.debug(f"     DivumWX Backup Start Time:  {bkpTime}")
            else:
                logging.debug(f"        DivumWX Backup Service:  Disabled")
            continue_install = input(f"{yellow}Do you want to start the installation process? (yes/no): {reset}").strip().lower()
            if continue_install not in ['yes', 'y']:
                logging.debug(f"Installation aborted by user")
                print(f"{red}Installation aborted by user.{reset}")
                sys.exit(1)

            do_overwrite = d["over_write"] == "True"
            self.stopWeewx()
            try:
                if os.path.exists("user"):
                    logging.debug(f"Copying user directory files")    
                    print(f"{white}Copying {yellow}user{white} directory....")
                    if self.dirNotEmpty('user'):
                        distutils.dir_util.copy_tree("user", locations["user"], update=do_overwrite)
                        logging.debug(f"Copied user directory successfully")
                        print(f"{green}Copied {white}user {green}directory successfully{reset}")
                    else:
                        logging.debug(f"Directory 'user' is empty, unable to copy files.")
                        print(f"{red}Directory 'user' is empty, unable to copy files.{reset}")
                        sys.exit(1)
                else:
                    logging.debug(f"Directory 'user' does not exist.")
                    print(f"{red}Directory 'user' does not exist.{reset}")
                    sys.exit(1)
                if os.path.exists("skins"):
                    logging.debug(f"Copying skins directory files") 
                    print(f"{white}Copying {yellow}skins{white} directory....")
                    if self.dirNotEmpty('skins'):
                        logging.debug(f"Copied skins directory successfully")
                        distutils.dir_util.copy_tree("skins", locations["skins"], update=do_overwrite)
                        print(f"{green}Copied {white}skins {green}directory successfully{reset}")
                    else:
                        logging.debug(f"Directory 'skins' is empty, unable to copy files.")
                        print(f"{red}Directory 'skins' is empty, unable to copy files.{reset}")
                        sys.exit(1)
                else:
                    logging.debug(f"Directory 'skins' does not exist.")
                    print(f"{red}Directory 'skins' does not exist.{reset}")
                    sys.exit(1)
                if os.path.exists("www"):
                    if self.dirNotEmpty(html_root):
                        while True:
                            logging.debug(f"The www directory is not empty")
                            print(f"{yellow}\n\nThe {white}html_root{yellow} path contains files and should be empty.{reset}")
                            print(f"{yellow}The {white}html_root{yellow} path that was created earlier should be a.{reset}")
                            print(f"{yellow}new directory and therefore, empty. If it is not empty, this installation{reset}")
                            print(f"{yellow}can not continue as the DivumWX files are meant to be installed into an empty{reset}")
                            print(f"{yellow}directory.{reset}")
                            print(f"{red}Answering NO to the question below will terminate the installation program.\n\n{reset}")
                            user_input = input("Do you want to empty the directory first? (y/n): ").strip().lower()
                            if user_input == 'y':
                                logging.debug(f"Emptying the www directory prior to new file installation.")
                                self.deleteContents(html_root)
                                print(f"{green}Directory {html_root} has been emptied.{reset}")
                                print(f"{white}Copying {yellow}www{white} directory....")
                                distutils.dir_util.copy_tree("www", locations["www"], update=do_overwrite)
                                print(f"{green}Copied {white}www {green}directory successfully{reset}")
                                self.chgPermRecur([locations["www"]], (self.user, self.wsOwner))
                                print(f"{green}File permissions and ownership in the {white}www {green}directory successfully set{reset}")
                                break
                            elif user_input == 'n':
                                logging.debug(f"www directory not emptied. Exiting the installation program.")
                                print(f"{red}Not emptying the HTML_ROOT directory of the previous skin's files {reset}")
                                print(f"{red}can cause {white}SERIOUS{red} issues with DivumWX. You will need to empty the{reset}")
                                print(f"{red}the directory manually or add a sub-directory to install DivumWX to.{reset}")
                                print(f"{red}Exiting the installation program{reset}")
                                sys.exit(1)
                            else:
                                print(f"{red}Invalid input. Please enter 'y' or 'n'.{reset}")
                    else:
                        logging.debug(f"Copying www directory files")
                        print(f"{white}Copying {yellow}www{white} directory....")
                        distutils.dir_util.copy_tree("www", locations["www"], update=do_overwrite)
                        print(f"{green}Copied {white}www {green}directory successfully{reset}")
                else:
                    logging.debug(f"Directory 'www' does not exist.")
                    print(f"{red}Directory 'www' does not exist.{reset}")
                    sys.exit(1)
            except Exception as e:
                print(e)
            logging.debug(f"Backing up weewx config file")
            print('Backing up weewx config file')
            timestamp = time.strftime('%Y%m%d%H%M%S')
            backup_file = f"{weewx_config_file}.{timestamp}"
            distutils.file_util.copy_file(weewx_config_file, backup_file)
            print(f"Backup of weewx config created: {backup_file}")
            time.sleep(1)
            logging.debug(f"Verifying HTML_ROOT for added/updated stanzas")
            print(f"{white}Verifying HTML_ROOT for added/updated stanzas{reset}")
            self.chgHroot(d, html_root)
            logging.debug(f"Appending to existing stanzas")
            print(f"{white}Appending to existing stanzas{reset}")
            self.appendStanza(config_data, d, do_overwrite)
            logging.debug(f"Adding new stanzas")
            print(f"{white}Adding new stanzas{reset}")
            self.addStanza(config_data, d)
            logging.debug(f"Updating Services stanza")
            print(f"{white}Updating Services stanza{reset}")
            self.appendSvcs(config_data, d)
            logging.debug(f"Checking if DivumWS backup service is enabled")
            if bkpWX:
                logging.info(f"Adding DivumWX Backup Stanza")
                print(f"{white}Adding DivumWX Backup Stanza")
                self.addBkpStanza(config_data, d)
                logging.info(f"Adding DivumWX Backup Service")
                print(f"{white}Adding DivumWX Backup Service")
                self.appendBkpSvcs(config_data, d)
            else:
                logging.debug(f"DivumWX Backup Service not enabled")    
                print(f"{white}DivumWX Backup Service not enabled")
            logging.debug(f"Saving new weewx.conf file")    
            print(f"{white}Saving new weewx.conf file{reset}")
            config_data.write()
            logging.debug(f"Performing cleanup tasks.......")    
            print(f"{white}\n\nPerforming cleanup tasks.......{reset}")
            time.sleep(1)
            targets_and_texts = [
                ("[[DivumWXReport]]", "\n    # The DivumWXReport uses the 'DivumWX' skin, which contains the\n    # images, templates and plots for the report.\n"),
                ("[[dvmHighcharts]]", "\n"),
                ("[DivumWXWebServices]", "\n##############################################################################\n#\n"),
                ("[DivumWXCloudCover]", "\n##############################################################################\n#\n"),
                ("[LastNonZero]", "\n##############################################################################\n#\n"),
                ("[DivumWXRealTime]", "\n##############################################################################\n#\n"),
                ("[AirDensity]", "\n##############################################################################\n#\n"),
                ("[FilePile]", "\n##############################################################################\n#\n"),
                ("[DVM_DB_Backup]", "\n##############################################################################\n#\n")
            ]
            with open(weewx_config_file, 'r') as file:
                lines = file.readlines()
            for target, insert_text in targets_and_texts:
                for i, line in enumerate(lines):
                    if target in line:
                        lines.insert(i, insert_text)
                        break
            logging.debug(f"Saving weewx config file")    
            print(f"{green}Saving weewx config file{reset}")
            with open(weewx_config_file, 'w') as file:
                file.writelines(lines)
            self.updDatabase()
            self.setfnlOwner(locations)
            weewxVersion = self.createCron(html_root)
            print(f"{green}Weewx Version: {weewxVersion}{reset}")
            logging.debug(f"WeeWX Version: {weewxVersion}")
            print(f"{white}Done! WeeWX must be {yellow}started{white} for changes to become active{reset}")
            print(f"{green}You will need to ensure that your webserver is set to deliver the DivumWX skin{reset}")
            logging.debug(f"DivumWX Installation has been completed.")
            self.startWeewx()
            
        except Exception as e:
            traceback.print_exc()
            print(e)

if __name__ == '__main__':
    DVMInstaller(sys.argv[1] if len(sys.argv) > 1 else None)