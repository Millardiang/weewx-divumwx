import os
import pwd
import grp
import sys
import glob
import logging
import argparse
import subprocess
import time
import datetime
import json
import re
import getpass
import textwrap
import shutil
from datetime import datetime
from pathlib import Path

version = "5.5.55.555"
srvGenURL = 'https://www.divumwx.org/settingsGen/'

reset = "\033[0m"
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
    parser.add_argument("-v", "--version", action="store_true", help="Display the script's version and exit")
    args = parser.parse_args()

    if args.version:
        print(f"DivumWX Installer Version: {version}")
        sys.exit(0)

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
        logging.basicConfig(level=logging.INFO)

    return args
args = setup_logging()
os.system("clear")
logging.debug("+-------------------- Debug logging enabled for DivumWX Installer --------------------+")
logging.debug(f"+----------------------- DivumWX Installer Version: {version} -----------------------+")
print(f"{white}DIS {cyan}(DivumWX Installation Script) {green}version:{magenta}{version} {white}starting.....{reset}")
print(f"{cyan}Standby, verifying system requirements{reset}")
logging.debug(f"Standby, verifying system requirements")
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
logging.debug(f"Checking Python virtual environment")
if not hasattr(sys, 'real_prefix') and not (hasattr(sys, 'base_prefix') and sys.base_prefix != sys.prefix):
    print(f"{red}This script is not running inside a Python virtual environment. Please ensure that you have{reset}")
    print(f"{red}activated the virtual WeeWX environment by running the following command before proceeding.{reset}")
    logging.debug(f"The script is not running inside the Python Virtual Environment, script exiting.")
    sys.exit(1)
else:
    logging.debug(f"The script is operating inside a Python Virtual Environment, proceeding")
    print(f"{white}This script is running inside a Python virtual environment. Proceeding with installation.{reset}")
weewxd_path = os.path.expanduser('~/weewx-venv/bin/weewxd')
try:
    weewx_version = subprocess.run([weewxd_path, '--version'], capture_output=True, text=True).stdout.strip()
except FileNotFoundError:
    print(f"{red}WeeWX executable not found at {weewxd_path}. Please verify your installation.{reset}")
    logging.debug(f"WeeWX executable not found at {weewxd_path}. Please verify your installation.")
    sys.exit(1)
required_version = [5, 1, 0]
current_version = list(map(int, weewx_version.split('.')))
if current_version < required_version:
    logging.debug(f"WeeWX version {weewx_version} is does not meet the minimum requirement.")
    print(f"{red}WeeWX version {weewx_version} is does not meet the minimum requirement. Please upgrade to version {'.'.join(map(str, required_version))} or higher.{reset}")
    sys.exit(1)
elif current_version > required_version:
    logging.debug(f"WeeWX version {weewx_version} exceeds the minimum requirement. Proceeding with installation.")
    print(f"{green}WeeWX version {weewx_version} exceeds the minimum requirement. Proceeding with installation.{reset}")
else:
    logging.debug(f"WeeWX version {weewx_version} matches the minimum requirement. Proceeding with installation.")
    print(f"{white}WeeWX version {yellow}{weewx_version}{white} matches the minimum requirement. Proceeding with installation.{reset}")
print(f"{yellow}Standby, importing and verifying required python modules...{reset}")
def modMissing(module_name, force_restart=False):
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
            if force_restart:
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
    modMissing("readchar", force_restart=True)
try:
    import curses
except ImportError:
    modMissing("curses", force_restart=True)
try:
    from configobj import ConfigObj
except ImportError:
    modMissing("configobj", force_restart=True)
print(f"{green}Python module import complete...{reset}")

class DVMInstaller:
    def __init__(self, conf_file=None):
        self.waitFKP()
        self.viewFile('./INSTALLATION_GUIDE.md', 40)
        self.services_file = self.chkSvcFile()
        self.prnWelMsg()
        self.conf_files = {}
        conf_file = 'installData.json'
        self.run_installer(conf_file)

    def chkBackup(self, config_file):
        base_name = os.path.basename(config_file)
        dir_name = os.path.dirname(config_file) or "."
        pattern = os.path.join(dir_name, f"{base_name}_dvm.*")
        return bool(glob.glob(pattern))
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
                raw_lines = file.readlines()

            max_y, max_x = stdscr.getmaxyx()
            lines_per_page = min(lines_per_page, max_y - 2)
            wrapped_lines = []

            # Wrap lines but preserve empty lines
            for line in raw_lines:
                if line.strip() == "":  # Preserve empty lines as-is
                    wrapped_lines.append("")
                else:
                    wrapped_lines.extend(textwrap.wrap(line.strip(), max_x - 1))

            total_lines = len(wrapped_lines)
            current_line = 0

            while True:
                stdscr.clear()
                stdscr.addstr(0, 0, banner_text[:max_x-1], curses.color_pair(1) | curses.A_BOLD | curses.A_UNDERLINE)

                for i in range(lines_per_page):
                    line_number = current_line + i
                    if line_number < total_lines:
                        stdscr.addstr(i + 1, 0, wrapped_lines[line_number], curses.color_pair(2))

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
        print(f" - {yellow}Automated Installation{reset}: Automates the placement of the necessary files within the weewx pip hierarchy.")
        print(f" - {yellow}Configuration Management{reset}: Updates configuration files with specified settings, ensuring your system is")
        print("     correctly configured for operation.")
        print(f" - {yellow}Permissions Management{reset}: Recursively applies appropriate file and directory permissions.")
        print(f" - {yellow}Interactive Prompts{reset}: Guides you through critical decisions with easy-to-follow prompts, ensuring clarity")
        print("     and user control.")
        print(f" - {yellow}Error Handling{reset}: Provides comprehensive error messages and handles exceptions gracefully to aid in")
        print("     troubleshooting during installation.\n")
        print(f"{white}Let's get started!{reset}\n")
        self.waitFKP()

    def getWebSrvInfo(self, wsName=None, wsOwner=None, wsGroup=None):
        logging.debug("Entering Web Server verification and webroot check")

        def find_file(search_paths, filename):
            for path in search_paths:
                potential_path = os.path.join(path, filename)
                if os.path.exists(potential_path):
                    return potential_path
            return None

        try:
            result = subprocess.run(
                ["ps", "-eo", "comm=,user=,group=,ppid="],
                capture_output=True,
                text=True
            )
            if result.returncode != 0:
                logging.debug("Process list command failed")
                return False, None, None, None, None, None, None, None

            processes = result.stdout.splitlines()
            web_server_parent = None
            web_server_child = None

            for line in processes:
                parts = line.split()
                if len(parts) < 4:
                    continue

                command, user, group, ppid = parts[0], parts[1], parts[2], parts[3]
                if command in ["apache2", "nginx"] and user == "root":
                    logging.debug(f"Found the root process for webserver: {command}")
                    web_server_parent = (command, user, group)
                if command in ["apache2", "nginx"] and user != "root":
                    logging.debug(f"Found the child process for webserver: {command}")
                    web_server_child = (command, user, group)

            if wsName or wsOwner or wsGroup:
                if web_server_parent and web_server_parent[0] != wsName:
                    return False, None, None, None, None, None, None, None
                if web_server_child and web_server_child[1] != wsOwner:
                    return False, None, None, None, None, None, None, None
                if web_server_parent and web_server_parent[2] != wsGroup:
                    return False, None, None, None, None, None, None, None

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
                        for filename in os.listdir(sites_enabled_dir):
                            filepath = os.path.join(sites_enabled_dir, filename)
                            if os.path.isfile(filepath):
                                with open(filepath, 'r') as file:
                                    for line in file:
                                        match = re.match(r'^\s*(DocumentRoot|root)\s+(.+);?', line)
                                        if match:
                                            document_root = match.group(2).strip()
                                            site_name = os.path.splitext(filename)[0]
                                            document_roots[site_name] = document_root
                                            break
                    except Exception as e:
                        logging.debug(f"Error parsing site config in {sites_enabled_dir}: {e}")

            if web_server_parent and web_server_child:
                logging.debug(f"Webserver Type: {web_server_parent[0]}, Webserver Root: {web_server_parent[1]}:{web_server_parent[2]}, Webserver Child: {web_server_child[1]}:{web_server_child[2]}")
                return True, web_server_parent[0], web_server_parent[1], web_server_parent[2], web_server_child[1], web_server_child[2], main_document_root, document_roots

            logging.debug("No web server found or configured")
            return False, None, None, None, None, None, main_document_root, document_roots

        except Exception as e:
            logging.debug(f"An error occurred during the web server verification or webroot check: {e}")
            return False, None, None, None, None, None, None, None

    def getSrvDetails(self):
        while True:
            wsName = input("Please enter the web server name (or 'q' to quit): ").strip()
            logging.debug(f"User entered wsName: {wsName}")
            if wsName.lower() == 'q':
                logging.debug("Exiting script.")
                print("Exiting script.")
                sys.exit(0)

            wsOwner = input("Please enter the web server owner (or 'q' to quit): ").strip()
            logging.debug(f"User entered wsOwner: {wsOwner}")
            if wsOwner.lower() == 'q':
                logging.debug("Exiting script.")
                print("Exiting script.")
                sys.exit(0)

            wsGroup = input("Please enter the web server group (or 'q' to quit): ").strip()
            logging.debug(f"User entered wsGroup: {wsGroup}")
            if wsGroup.lower() == 'q':
                logging.debug("Exiting script.")
                print("Exiting script.")
                sys.exit(0)

            print("\nPlease confirm your inputs:")
            print(f"Web Server Name: {wsName}")
            print(f"Web Server Owner: {wsOwner}")
            print(f"Web Server Group: {wsGroup}")
            confirm = input("Is this correct? (y/n): ").strip().lower()
            if confirm in ['y', 'yes']:
                logging.debug("User confirmed web server details.")
                return
            else:
                logging.debug("User declined inputs. Re-entering details.")
                print("Please re-enter the details.")

    def chkSymlnk(self, document_root, symlink_name, expected_target, found_symlinks):
        symlink_path = Path(document_root) / symlink_name
        if symlink_path.is_symlink():
            actual_target = symlink_path.resolve()
            if actual_target == expected_target:
                logging.debug(f"Symlink '{symlink_name}' exists in '{document_root}' and points to '{expected_target}'.")
                found_symlinks.append(symlink_path)
                return True
            else:
                logging.debug(f"Symlink '{symlink_name}' exists in '{document_root}' but points to '{actual_target}' instead of '{expected_target}'.")
        else:
            logging.debug(f"No symlink named '{symlink_name}' found in '{document_root}'.")
        return False
    def chkWeewx(self, wxName='weewx'):
        logging.debug(f"Entering check weewx location routine")
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
                        logging.debug(f"{wxName} located. Owner: {wxOwner}, Group: {wxGroup}")
                        return True, wxName, wxOwner, wxGroup
                logging.debug(f"weewx not found")
                return False, wxName, None, None
            else:
                logging.debug(f"weewx not found")
                return False, wxName, None, None
        except Exception:
            logging.debug(f"weewx not found")
            return False, wxName, None, None

    def dirNotEmpty(self, path):
        logging.debug(f"Checking for empty directory {path}")
        return os.path.isdir(path) and bool(os.listdir(path))

    def copyDir(self, src, dst, overwrite=False):
        logging.debug(f"Entering Directory copy function")
        if not os.path.exists(dst):
            os.makedirs(dst)
        for item in os.listdir(src):
            source_path = os.path.join(src, item)
            dest_path = os.path.join(dst, item)
            if os.path.isdir(source_path):
                self.copyDir(source_path, dest_path, overwrite)
            else:
                if not os.path.exists(dest_path) or overwrite:
                    shutil.copy2(source_path, dest_path)
    def getPerms(self, path):
        try:
            stat_info = os.stat(path)
            return {
                "permissions": oct(stat_info.st_mode & 0o777),
                "owner": pwd.getpwuid(stat_info.st_uid).pw_name,
                "group": grp.getgrgid(stat_info.st_gid).gr_name,
                "error": None
            }
        except FileNotFoundError:
            return {"error": "FileNotFoundError", "message": "Path not found"}
        except PermissionError:
            return {"error": "PermissionError", "message": "Permission denied"}
        except OSError as e:
            return {"error": "OSError", "message": str(e)}
    def fixPerms(self, path, perms, owner, group):
        try:
            if not os.path.exists(path):
                logging.debug(f"Directory does not exist, attempting to create: {path}")
                print(f"{cyan}Directory {path} does not exist. Creating it...{reset}")
                try:
                    os.makedirs(path, exist_ok=True)
                except PermissionError:
                    logging.debug(f"Permission denied creating {path}, retrying with sudo.")
                    print(f"{yellow}Permission denied when creating {path}. Retrying with sudo...{reset}")
                    try:
                        print(f"{cyan}Attempting to create directory {path} using sudo...{reset}")
                        subprocess.run(["sudo", "mkdir", "-p", path], check=True, stdin=subprocess.PIPE)
                    except subprocess.CalledProcessError as e:
                        logging.debug(f"Failed to create directory with sudo: {e}")
                        return {"success": False, "message": "Failed to create directory with sudo.", "error": str(e)}
            uid = pwd.getpwnam(owner).pw_uid
            gid = grp.getgrnam(group).gr_gid
            if int(oct(os.stat(path).st_mode & 0o777), 8) != perms:
                logging.debug(f"Updating permissions for {path} to {oct(perms)}")
                print(f"{cyan}Updating permissions for {path} to {oct(perms)}{reset}")
                os.chmod(path, perms)
            current_uid = os.stat(path).st_uid
            current_gid = os.stat(path).st_gid
            if current_uid != uid or current_gid != gid:
                logging.debug(f"Updating ownership for {path} to {owner}:{group}")
                print(f"{cyan}Updating ownership for {path} to {owner}:{group}{reset}")
                os.chown(path, uid, gid)
            logging.debug(f"Updated {path} to {oct(perms)} with {owner}:{group}")
            print(f"{green}Updated {path} with permissions {oct(perms)} and owner:group {owner}:{group}{reset}")
            return {"success": True, "message": "Permissions updated successfully.", "error": None}

        except FileNotFoundError as e:
            logging.debug(f"FileNotFoundError: {e}")
            print(f"{red}FileNotFoundError: {e}{reset}")
            return {"success": False, "message": "Path not found and could not be created.", "error": str(e)}
        except PermissionError:
            logging.debug(f"Permission error on {path}, retrying with sudo.")
            print(f"{yellow}Permission error on {path}. Retrying with sudo...{reset}")
            try:
                subprocess.run(["sudo", "chmod", f"{perms:o}", path], check=True, stdin=subprocess.PIPE)
                subprocess.run(["sudo", "chown", f"{owner}:{group}", path], check=True, stdin=subprocess.PIPE)
                logging.debug(f"Fixed {path} with sudo.")
                print(f"{green}Fixed permissions for {path} using sudo.{reset}")
                return {"success": True, "message": "Permissions fixed using sudo.", "error": None}
            except subprocess.CalledProcessError as e:
                logging.debug(f"Failed to fix {path} with sudo. Error: {e}")
                print(f"{red}Failed to update permissions for {path} using sudo.{reset}")
                return {"success": False, "message": "Failed to update permissions with sudo.", "error": str(e)}
        except Exception as e:
            logging.debug(f"Unexpected error on {path}: {e}")
            print(f"{red}Unexpected error on {path}: {e}{reset}")
            return {"success": False, "message": "Unexpected error occurred.", "error": str(e)}
    def chkDefaultPerms(self, dirDefaults):
        print(f"\n{white}Checking Directory Permissions{reset}\n")
        print(f"{cyan}{'{:<50} {:<10} {:<10} {:<10} {:<10} {:<10} {:<10} {:<5}'.format('Path', 'Exp Perm', 'Curr Perm', 'Exp Own', 'Curr Own', 'Exp Grp', 'Curr Grp', 'Fix')}{reset}")
        print(f"{cyan}{'-' * 110}{reset}")
        
        for index, (path, settings) in enumerate(dirDefaults.items()):
            current_perms = self.getPerms(path)
            
            if current_perms.get("error"):
                settings["fix"] = "True"
                print(f"{yellow}{'{:<50} {:<10} {:<10} {:<10} {:<10} {:<10} {:<10} {}'.format(path, oct(settings['permissions']), '-', settings['owner'], '-', settings['group'], '-', red + 'X' + reset)}")
                continue
            
            expected_perms = settings["permissions"]
            expected_owner = settings["owner"]
            expected_group = settings["group"]
            
            perm_color = green if int(current_perms["permissions"], 8) == expected_perms else red
            owner_color = green if current_perms["owner"] == expected_owner else red
            group_color = green if current_perms["group"] == expected_group else red
            
            fix_needed = green + "N" + reset
            if perm_color == red or owner_color == red or group_color == red:
                fix_needed = red + "X" + reset
                settings["fix"] = "True"
                fix_needed = red + "X" + reset
            else:
                settings["fix"] = "False"
                fix_needed = green + "N" + reset
            
            print(f"{yellow}{'{:<50} {:<10} {}{:<10}{} {:<10} {}{:<10}{} {:<10} {}{:<10}{} {}'.format(path, oct(expected_perms), perm_color, current_perms['permissions'], reset, expected_owner, owner_color, current_perms['owner'], reset, expected_group, group_color, current_perms['group'], reset, fix_needed)}")
        print(f"{cyan}{'-' * 110}{reset}")
    def chkUgrp(self):
        logging.debug(f"Verifying user/group")
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
        logging.debug(f"Changing HTML_ROOT")
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
        logging.debug(f"Adding new DivumWX specific stanzas to weewx config")
        for i in range(5, 11):  # config_entries4 to config_entries10
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
        logging.debug(f"Backup Service stanza added")
        entry = entries['backupEntry1']
        for section, values in entry.items():
            for key, value in values.items():
                if isinstance(value, dict):
                    for sub_key, sub_value in value.items():
                        value[sub_key] = sub_value
                values[key] = value
            config_data[section] = values

    def appendStanza(self, config_data, entries, do_overwrite):
        logging.debug(f"Appending DivumWX specific stanzas to weewx config [StdReport] area")
        for i in range(5):  # config_entries0 to config_entries4
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
        logging.debug(f"Appending DivumWX specific services to weewx config")
        count = 0
        for key, value in entries.items():
            count += 1
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
        logging.debug(f"Total entries appended: {count}")

    def appendBkpSvcs(self, config_data, entries):
        if self.bkpWXSrv:
            logging.debug(f"Adding DivumWX Backup service to weewx config file")
            for key, value in entries.items():
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
        logging.debug("Updating weewx database with additional columns for DivumWX Skin")
        logging.debug("The following columns will be added to the database to support the DivumWX Skin:")
        logging.debug("AirDensity, stormRain, threshold, cloudcover, is_sunshine, sunshine_time, sunshine_time_hours, co, no2, so2, o3, nh3, aerosol_optical_depth, dust, alder_pollen, birch_pollen, olive_pollen, grass_pollen, mugwort_pollen, ragweed_pollen, cloudcover, lightning_last_det_time")

        print(f"{white}Updating weewx database with additional columns for DivumWX Skin{reset}")
        print(f"{white}The following columns will be added to the database to support the DivumWX Skin:{reset}")
        print(f"{cyan}AirDensity, stormRain, threshold, cloudcover, is_sunshine, sunshine_time, sunshine_time_hours, co, no2, so2, o3, nh3, aerosol_optical_depth, dust, alder_pollen, birch_pollen, olive_pollen, grass_pollen, mugwort_pollen, ragweed_pollen, cloudcover, lightning_last_det_time{reset}")

        columns = [
            "AirDensity", "stormRain", "threshold", "cloudcover", "is_sunshine",
            "sunshine_time", "sunshine_time_hours", "co", "no2", "so2", "o3", "nh3",
            "aerosol_optical_depth", "dust", "alder_pollen", "birch_pollen", "olive_pollen",
            "grass_pollen", "mugwort_pollen", "ragweed_pollen", "cloudcover", "lightning_last_det_time"
        ]

        current_user = os.getenv("USER")
        weectl_path = f"/home/{current_user}/weewx-venv/bin/weectl"

        for column in columns:
            try:
                if column == "lightning_last_det_time":
                    command = f"{weectl_path} database add-column {column} --type INTEGER -y"
                    column_type = "INTEGER"
                else:
                    command = f"{weectl_path} database add-column {column} -y"
                    column_type = "REAL"

                result = subprocess.run(command, shell=True, capture_output=True, text=True)

                if result.returncode == 0:
                    print(f"New column {column} of type {column_type} added to database.")
                    logging.debug(f"New column {column} of type {column_type} added to database.")
                else:
                    if "duplicate column name" in result.stderr:
                        logging.debug(f"Column {column} already exists in the database.")
                        print(f"Column {column} already exists in the database.")
                    else:
                        logging.debug(f"Error occurred while adding column {column}: {result.stderr}")
                        print(f"Error occurred while adding column {column}: {result.stderr}")

            except Exception as e:
                logging.error(f"An exception occurred while adding column {column}: {e}")
                print(f"An exception occurred while adding column {column}: {e}")

    def ctrlWeewx(self, wxCmd):
        if wxCmd.lower() == "start":
            confirm = input("Are you ready to start the WeeWX service to apply changes? (yes/no): ").strip().lower()
            if confirm in ['yes', 'y']:
                try:
                    print("Attempting to start the WeeWX service...")
                    logging.debug("Attempting to start the WeeWX service...")
                    subprocess.check_call(["sudo", "systemctl", "start", "weewx"])
                    print("WeeWX service started successfully.")
                    logging.debug("WeeWX service started successfully.")
                except subprocess.CalledProcessError as e:
                    print(f"Failed to start the WeeWX service: {e}")
                    logging.debug(f"Failed to start the WeeWX service: {e}")
            else:
                print("WeeWX service start was not requested. Please remember to start WeeWX manually for changes to take effect.")
                logging.debug("WeeWX service start was not requested. Please remember to start WeeWX manually for changes to take effect.")
        elif wxCmd.lower() == "stop":
            try:
                print("Stopping the WeeWX service...")
                logging.debug("Stopping the WeeWX service...")
                subprocess.check_call(["sudo", "systemctl", "stop", "weewx"])
                print("WeeWX service stopped successfully.")
                logging.debug("WeeWX service stopped successfully.")
            except subprocess.CalledProcessError as e:
                print(f"Failed to stop the WeeWX service: {e}")
                logging.debug(f"Failed to stop the WeeWX service: {e}")

    def getEnabledSkins(self, weewx_config_file):
        logging.debug("Getting currently enabled skins")
        report_configs = []
        inside_std_report = False
        current_section = None

        try:
            with open(weewx_config_file, 'r') as file:
                for line in file:
                    if "[StdReport]" in line:
                        inside_std_report = True
                        logging.debug("Entering [StdReport] section")
                        continue
                    if "[StdConvert]" in line:
                        logging.debug("Exiting [StdReport] section at [StdConvert]")
                        break
                    if inside_std_report:
                        if line.strip().startswith("[[") and line.strip().endswith("]]"):
                            current_section = line.strip()[2:-2]
                            logging.debug(f"Found skin section: {current_section}")
                        elif current_section and "enable" in line:
                            parts = line.strip().split('=')
                            if len(parts) == 2:
                                key, value = parts[0].strip().lower(), parts[1].strip().lower()
                                if key == "enable":
                                    enabled = value == "true"
                                    report_configs.append({"section": current_section, "enabled": enabled})
                                    logging.debug(f"Skin '{current_section}' enabled: {enabled}")
                                    current_section = None
        except (FileNotFoundError, IOError) as e:
            logging.error(f"Error reading the configuration file: {e}")
            raise
        return report_configs
        
    def run_installer(self, conf_file):
        os.system('clear')
        print(f"{white}Loading installation configuration files.....{reset}")
        logging.debug(f"Loading installation configuration files.....")
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
        bkupDBPath = os.path.expanduser(d["bkupDBPath"])
        config_data = ConfigObj(weewx_config_file, encoding='utf8', list_values=False, write_empty_values=True)
        weewx_config_file_exists = True
        if self.chkBackup(weewx_config_file):
            print(f"{red}A previous weewx.conf backup file from dvmInstaller.py script found. Running this script again{reset}")
            print(f"{red}will corrupt your weewx.conf file, and cause the fleas of a thousand neurotic camels to infest{reset}")
            print(f"{red}your favorite accoutrement.{reset}")
            print(f"{red}This backup must be rolled back if you wish to run this script again.{reset}")
            print(f"{cyan}Exiting script.{reset}")
            raise SystemExit("Backup already exists. The script has run before.")
        else:
            print(f"{green}No previous dvmInstaller backup file found, proceeding....{reset}")
        self.chkUgrp()
        global weewxRunning, wxName, wxOwner, wxGroup, wsStatus, wsName, wsOwner, wsGroup, mainDocRoot, docRoots, docRootType, siteDocRoots, symlinks, symlinkStatus
        print(f"Checking weewx status")
        weewxRunning, wxName, wxOwner, wxGroup = self.chkWeewx()
        if weewxRunning:
            logging.debug(f"WeeWX process '{wxName}' is running. Owned by '{wxOwner}:{wxGroup}'.")
            print(f"{white}WeeWX process {cyan}'{wxName}' {white}is running. Owned by {cyan}'{wxOwner}:{wxGroup}'{reset}.")
        else:
            logging.debug(f"No running weewx process was found.")
            print(f"{red}No running weewx process was found.")
        print(f"Checking Web Server status")
        wsStatus, detectedName, parentOwner, parentGroup, childOwner, childGroup, mainDocRoot, siteDocRoots = self.getWebSrvInfo()
        if not wsStatus:
            logging.debug("No web server detected.")
            print(f"{red}No web server detected.")
            self.getSrvDetails()
            return
        wsName = detectedName
        wsOwner = childOwner if parentOwner == "root" else parentOwner
        wsGroup = childGroup if parentOwner == "root" else parentGroup
        logging.debug(f"Web server '{wsName}' detected. Owner: '{wsOwner}:{wsGroup}'.")
        print(f"{white}Web server {cyan}'{wsName}'{white} detected. Owner: {cyan}'{wsOwner}:{wsGroup}'{reset}.")
        docRoots = []
        if mainDocRoot:
            foundSymlinks = []
            mainSymlinkStatus = self.chkSymlnk(mainDocRoot, 'public_html', Path('~/weewx-data/public_html').expanduser(), foundSymlinks)
            docRoots.append({
                "type": "Main",
                "path": mainDocRoot,
                "symlinkStatus": mainSymlinkStatus,
                "symlinks": foundSymlinks,
            })
            logging.debug(f"Main document root: {mainDocRoot}, symlinkStatus: {mainSymlinkStatus}, symlinks: {foundSymlinks}")
            print(f"Main document root: {green}{mainDocRoot}{reset}, symlinkStatus: {green}{mainSymlinkStatus}{reset}, symlinks: {green}{foundSymlinks}{reset}")
        if siteDocRoots:
            if len(siteDocRoots) > 1:
                logging.debug(f"Multiple site-specific document roots detected: {len(siteDocRoots)}")
                print(f"{yellow}Multiple site-specific document roots detected. Checking all roots for symlinks.{reset}")
            for siteName, siteDocRoot in siteDocRoots.items():
                foundSymlinks = []
                siteSymlinkStatus = self.chkSymlnk(siteDocRoot, 'public_html', Path('~/weewx-data/public_html').expanduser(), foundSymlinks)
                docRoots.append({
                    "type": f"Site ({siteName})",
                    "path": siteDocRoot,
                    "symlinkStatus": siteSymlinkStatus,
                    "symlinks": foundSymlinks,
                })
                logging.debug(f"Site document root: {siteDocRoot}, symlinkStatus: {siteSymlinkStatus}, symlinks: {foundSymlinks}")
        if not mainDocRoot:
            if docRoots:
                mainDocRoot = docRoots[0]["path"]
                logging.debug(f"Fallback: mainDocRoot set to first detected root in docRoots: {mainDocRoot}")
            elif siteDocRoots:
                mainDocRoot = next(iter(siteDocRoots.values()))
                logging.debug(f"Fallback: mainDocRoot set to first detected root in siteDocRoots: {mainDocRoot}")
            else:
                logging.debug("mainDocRoot is not set and no fallback could be determined.")
        if docRoots and not mainDocRoot:
            mainDocRoot = docRoots[0]["path"]
            logging.debug(f"mainDocRoot aligned with first entry in docRoots: {mainDocRoot}")
        if siteDocRoots and not mainDocRoot:
            mainDocRoot = next(iter(siteDocRoots.values()))
            logging.debug(f"mainDocRoot aligned with first entry in siteDocRoots: {mainDocRoot}")
        www_root = os.path.expanduser(d["www_root"])
        print(f"{cyan}+---------------------------------------------------------------------------------------+")
        print(f" {white}Webserver Information:")
        if wsStatus:
            print(f" {wsName} is running under {wsOwner}:{wsGroup}")
        else:
            print(f" No webserver was found, either automatically or manually.")
        print(f" Document Root and symlink information")
        for root in docRoots:
            print(f"      Type: {root['type']}")
            print(f"      Path: {root['path']}")
            print(f"      Symlink Status: {root['symlinkStatus']}")
            print(f"      Symlinks: {', '.join(root['symlinks']) if root['symlinks'] else 'None'}")
            print("-" * 50)
        print(f"{cyan}+---------------------------------------------------------------------------------------+")
        print(f"{white}dvmInstaller installData file Paths:")
        print(f"        user_path: {user_path}")
        print(f"       skins_path: {skins_path}")
        print(f"weewx_config_file: {weewx_config_file}")
        print(f"         www_root: {www_root}")
        print(f"      mainDocRoot: {mainDocRoot}")
        if www_root == mainDocRoot:
            logging.debug(f"Main DocumentRoot {mainDocRoot} matches www_root {www_root}")
        else:
            logging.debug(f"Main DocumentRoot {mainDocRoot} does not match  www_root {www_root}")
            www_root = mainDocRoot
            logging.debug(f"Main DocumentRoot is now - {mainDocRoot}  and www_root is now - {www_root}")
        logging.debug(f"+---------------------------------------------------------------------------------------+")
        logging.debug(f" Webserver Information:")
        if wsStatus:
            logging.debug(f" {wsName} is running under {wsOwner}:{wsGroup}")
        else:
            logging.debug(f" No webserver was found, either automatically or manually.")
        logging.debug(f" Document Root and symlink information")
        for root in docRoots:
            logging.debug(f"      Type: {root['type']}")
            logging.debug(f"      Path: {root['path']}")
            logging.debug(f"      Symlink Status: {root['symlinkStatus']}")
            logging.debug(f"      Symlinks: {', '.join(root['symlinks']) if root['symlinks'] else 'None'}")
            logging.debug("-" * 50)
        logging.debug(f"+---------------------------------------------------------------------------------------+")
        logging.debug(f"Conf Paths:")
        logging.debug(f"        user_path: {user_path}")
        logging.debug(f"       skins_path: {skins_path}")
        logging.debug(f"weewx_config_file: {weewx_config_file}")
        logging.debug(f"         www_root: {www_root}")
        self.waitFKP()
        os.system("clear")
        logging.debug(f"Determining if any other skins are also enabled.")
        print(f"{green}Determining if any other skins are also enabled.{reset}")
        allSkins = self.getEnabledSkins(weewx_config_file)
        enabledSkins = [skin["section"] for skin in allSkins if skin["enabled"] is True]
        print(f"{green}+-------------------------------------------------------------------------------+{reset}")
        print(f"{white}The following skins are enabled on your system:{reset}")
        if enabledSkins:
            for skin in enabledSkins:
                print(f"{green}  - {skin}{reset}")
        else:
            print(f"{yellow}  No enabled skins found.{reset}")
        print(f"{green}+-------------------------------------------------------------------------------+{reset}")
        logging.debug(f"+-------------------------------------------------------------------------------+")
        print(f"{white}Your weewx.conf file [StdReport] section has {yellow}{config_data['StdReport'].get('HTML_ROOT')}{white} as your HTML_ROOT setting.{reset}", end="\n")
        logging.debug(f"The HTML_ROOT setting in weewx.conf is {config_data['StdReport'].get('HTML_ROOT')}")
        if www_root == mainDocRoot:
            logging.debug(f"www_root ({www_root}) from the dvmInstaller config file matches the Web Server DocumentRoot ({mainDocRoot}).")
            print(f"{green}Match found!{reset} www_root from the dvmInstaller config file matches Web Server DocumentRoot.{reset}")
            print(f"{white}www_root:{reset} {cyan}{www_root}{reset}")
            print(f"{white}Web Server DocumentRoot:{reset} {cyan}{mainDocRoot}{reset}")
            print(f"{green}This is a good thing (tm)!!{reset}")
        else:
            logging.debug(f"www_root ({www_root}) does not match DocumentRoot ({mainDocRoot}).")
            print(f"{red}Mismatch detected!{reset} www_root and DocumentRoot do not match.{reset}")
            print(f"{red}www_root:{reset} {yellow}{www_root}{reset}")
            print(f"{red}Web Server DocumentRoot:{reset} {yellow}{mainDocRoot}{reset}")
        self.waitFKP()
        html_root = os.path.join(www_root , "divumwx")
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
        dirDefaults = {
            www_root: {
                "permissions": 0o775,
                "owner": "www-data",
                "group": "www-data",
                "fix": "False"
            },
            f"{www_root}/divumwx": {
                "permissions": 0o775,
                "owner": "www-data",
                "group": self.user,
                "fix": "False"
            },
        }
        print(f"{green}Gathering current path information{reset}")
        logging.debug(f"Gathering current path information")
        print(f"Current Directory Status")
        print(f"      User Path Exists: {user_path_exists}")
        print(f"     Skins Path Exists: {skins_path_exists}")
        print(f"      HTML Root Exists: {html_root_exists}")
        self.chkDefaultPerms(dirDefaults)
        print(f"{white}Any items that are marked with a {green}N{white} are good, any that are marked with a {red}X{white} will be fixed next.")
        self.waitFKP()
        print(f"{cyan}Scanning for paths that need fixing.\n")
        logging.debug("Scanning for paths that need fixing.")
        for path, settings in dirDefaults.items():
            if settings["fix"] == "True":
                print(f"{yellow}Fixing: {path}{reset}")
                logging.debug(f"Fixing: {path}")
                self.fixPerms(path, settings["permissions"], settings["owner"], settings["group"])
            else:
                print(f"{green}No fixes are needed, ready to proceed{reset}")
                logging.debug(f"No fixes are needed, ready to proceed")
        self.waitFKP()
        os.system("clear")
        self.bkpWXSrv = False
        print(f"{green}+-------------------------------------------------------------------------------+{reset}")
        bkpWX = input('Do you wish to use the Divum Backup Service to backup your weewx database? (yes/no): ').strip().lower()
        if bkpWX in ['yes', 'y']:
            logging.debug(f"DivumWX Backup service enabled")
            self.bkpWXSrv = True
            default_dir = bkupDBPath
            main_file_name = 'weewx.sdb'
            default_name = "weewx.sdb.bak"
            default_time = "03:00"
            while True:
                try:
                    dir_path = input(f"Please enter the directory path for your backup file (default: {default_dir}): ").strip()
                    if not dir_path:
                        dir_path = default_dir
                    if not os.path.isdir(dir_path):
                        print("The directory does not exist. Please enter a valid directory path.")
                    else:
                        break
                except Exception as e:
                    print(f"An error occurred: {e}. Please try again.")
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
                bkpTime = input(f"Please enter the time you want the backup to take place (default: {default_time}): ").strip()
                if not bkpTime:
                    bkpTime = default_time
                if re.match(r'^([01][0-9]|2[0-3]):([0-5][0-9])$', bkpTime):
                    try:
                        datetime.strptime(bkpTime, '%H:%M')
                        break
                    except ValueError:
                        print("The time is not valid. Please ensure it is a valid 24-hour time.")
                else:
                    print("Invalid time format. Please enter the time in HH:MM format (24-hour clock).")
            wxMainDB =  os.path.join(dir_path, main_file_name)
            wxBkpDB = os.path.join(dir_path, bkpName)
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
        print(f"      {white}Info: Location where we will copy the DivumWX User files to{reset}")
        print(f"           Status: {green if user_path_exists else red}{'Path Exists' if user_path_exists else 'Path does not exist'}{reset}")
        print(f"       {cyan}Skins Path:{reset} {green}{skins_path}{reset}")
        print(f"      {white}Info: Location where we will copy the DivumWX Skin files to{reset}")
        print(f"           Status: {green if skins_path_exists else red}{'Path Exists' if skins_path_exists else 'Path does not exist'}{reset}")
        print(f"   {cyan}HTML Root Path:{reset} {green}{html_root}{reset}")
        print(f"      {white}Info: Location where we will copy the DivumWX Web Server files to{reset}")
        print(f"{cyan}WeeWX Config File:{reset} {green}{weewx_config_file}{reset}")
        print(f"           Status: {green}{'Yes' if weewx_config_file_exists else f'{red}No'}{reset}")
        print(f"{blue}+--------------------------------------------------------------------------------------+{reset}")
        print(f"            {cyan}Detected user:{reset} {white}{self.user}{reset}")
        print(f"      {cyan}Detected user group:{reset} {white}{self.group}{reset}")
        print(f"          {cyan}Webserver:{reset} {green}{wsName}{reset}")
        print(f"    {cyan}Webserver Owner:{reset} {green}{wsOwner}{reset}")
        print(f"    {cyan}Webserver Group:{reset} {green}{wsGroup}{reset}")
        print(f"{blue}+--------------------------------------------------------------------------------------+{reset}")
        print(f"{cyan}DivumWX Backup Service Status:{reset}\n")
        if self.bkpWXSrv:
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
        logging.debug(f"WeeWX Config File: {weewx_config_file}")
        logging.debug(f"           Status: {'Yes' if weewx_config_file_exists else 'No'}")
        logging.debug(f"+--------------------------------------------------------------------------------------+")
        logging.debug(f"            Detected user: {self.user}")
        logging.debug(f"      Detected user group: {self.group}")
        logging.debug(f"          Webserver: {wsName}")
        logging.debug(f"    Webserver Owner: {wsOwner}")
        logging.debug(f"    Webserver Group: {wsGroup}")
        logging.debug(f"+--------------------------------------------------------------------------------------+")
        logging.debug(f"DivumWX Backup Service Status:")
        if self.bkpWXSrv:
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
        os.system('clear')
        do_overwrite = d["over_write"] == "True"
        self.ctrlWeewx("stop")
        try:
            if os.path.exists("user"):
                logging.debug(f"Copying user directory files")
                print(f"{white}Copying {yellow}user{white} directory....")
                if self.dirNotEmpty("user"):
                    self.copyDir("user", locations["user"], do_overwrite)
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
                if self.dirNotEmpty("skins"):
                    self.copyDir("skins", locations["skins"], do_overwrite)
                    logging.debug(f"Copied skins directory successfully")
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
                logging.debug(f"Copying www directory files")
                print(f"{white}Copying {yellow}www{white} directory....")
                if self.dirNotEmpty("www"):
                    self.copyDir("www", locations["www"], do_overwrite)
                    logging.debug(f"Copied www directory successfully")
                    print(f"{green}Copied {white}www {green}directory successfully{reset}")
                else:
                    logging.debug(f"Directory 'www' is empty, unable to copy files.")
                    print(f"{red}Directory 'www' is empty, unable to copy files.{reset}")
                    sys.exit(1)
            else:
                logging.debug(f"Directory 'www' does not exist.")
                print(f"{red}Directory 'www' does not exist.{reset}")
                sys.exit(1)
        except Exception as e:
            logging.error(f"An error occurred: {str(e)}")
            print(f"{red}An error occurred: {str(e)}{reset}")
        logging.debug(f"Backing up weewx config file")
        print('Backing up weewx config file')
        timestamp = time.strftime('%Y%m%d%H%M%S')
        backup_file = f"{weewx_config_file}_dvm.{timestamp}"
        shutil.copy2(weewx_config_file, backup_file)
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
        if self.bkpWXSrv:
            logging.debug(f"Adding DivumWX Backup Stanza")
            print(f"{white}Adding DivumWX Backup Stanza")
            self.addBkpStanza(config_data, d)
            logging.debug(f"Adding DivumWX Backup Service")
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
        wxVerFile = os.path.join(html_root, "weewxVer.txt")
        command = "~/weewx-venv/bin/weewxd --version"
        weewx_version = subprocess.check_output(command, shell=True, stderr=subprocess.STDOUT, text=True).strip()
        with open(wxVerFile, "w") as file:
            file.write(weewx_version)
        print(f"{cyan}Weewx Version: {weewx_version}{reset}")
        logging.debug(f"Current Weewx version, {weewx_version}, saved to '{wxVerFile}'.")
        print(f"Weewx version saved to '{wxVerFile}'.")
        print(f"{white}Performing final directory permissions check")
        logging.debug(f"Performing final directory permissions check")
        dirDefaults = {
            www_root: {
                "permissions": 0o755,
                "owner": "www-data",
                "group": self.user,
                "fix": "False"
            },
            f"{www_root}/divumwx": {
                "permissions": 0o775,
                "owner": "www-data",
                "group": self.user,
                "fix": "False"
            },
            f"{www_root}/divumwx/admin": {
                "permissions": 0o775,
                "owner": "www-data",
                "group": self.user,
                "fix": "False"
            },
            f"{www_root}/divumwx/admin/archives": {
                "permissions": 0o775,
                "owner": "www-data",
                "group": self.user,
                "fix": "False"
            },
            f"{www_root}/divumwx/admin/assets": {
                "permissions": 0o775,
                "owner": "www-data",
                "group": self.user,
                "fix": "False"
            },
            f"{www_root}/divumwx/admin/db": {
                "permissions": 0o775,
                "owner": "www-data",
                "group": self.user,
                "fix": "False"
            },
            f"{www_root}/divumwx/admin/db/dvmAdmin.db3": {
                "permissions": 0o644,
                "owner": "www-data",
                "group": self.user,
                "fix": "False"
            }
        }
        self.chkDefaultPerms(dirDefaults)
        print(f"{cyan}Scanning for paths that need fixing.\n")
        logging.debug("Scanning for paths that need fixing.")
        for path, settings in dirDefaults.items():
            if settings["fix"] == "True":
                print(f"{yellow}Fixing: {path}{reset}")
                logging.debug(f"Fixing: {path}")
                self.fixPerms(path, settings["permissions"], settings["owner"], settings["group"])
            else:
                print(f"{green}No fixes are needed, ready to proceed{reset}")
                logging.debug(f"No fixes are needed, ready to proceed")
        self.waitFKP()
        print(f"{white}Done! WeeWX must be {yellow}started{white} for changes to become active{reset}")
        print(f"{green}DivumWX is available at {white}http(s)://YourFQDN[or IP]/divumwx")
        logging.debug(f"DivumWX Installation has been completed.")
        self.ctrlWeewx("start")

if __name__ == '__main__':
    DVMInstaller(sys.argv[1] if len(sys.argv) > 1 else None)
