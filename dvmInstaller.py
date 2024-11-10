import os
import sys
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

os.system("clear")
print(f"{white}DIS {cyan}(DivumWX Installation Script) {white}starting.....{reset}")
print(f"{yellow}Standby, importing and verifying required python modules...{reset}")

version = "3.9.72.000"
srvGenURL = 'https://www.divumwx.org/settingsGen/'

def modMissing(module_name):
    print(f"{red}Module '{module_name}' is not installed.{reset}")
    install = input(f"Do you want to install the missing module '{module_name}'? (y/n): ").strip().lower()
    if install == 'y':
        try:
            print(f"Installing '{module_name}'...")
            subprocess.check_call([sys.executable, "-m", "pip", "install", module_name])
            print(f"{green}Module '{module_name}' installed successfully.{reset}")
            print(f"{green}Restarting script...{reset}")
            os.execl(sys.executable, sys.executable, *sys.argv)
        except subprocess.CalledProcessError as e:
            print(f"{red}Failed to install module '{module_name}': {e}{reset}")
            sys.exit(1)
    else:
        print(f"{red}The module '{module_name}' was not installed. The script cannot continue and will exit.{reset}")
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
        banner_text = "Installation Guide (Up/Down arrows to navigate, 'q' to quit)"
        curses.wrapper(self.file_viewer, file_path, banner_text, lines_per_page)

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

    def websrvInfo(self):
        webserver = None
        process_owner = None

        try:
            apache_check = subprocess.run(["pgrep", "-a", "apache2"], capture_output=True, text=True)
            if apache_check.stdout:
                webserver = "Apache"
                process_lines = apache_check.stdout.splitlines()
                for line in process_lines:
                    process_id = line.split()[0]
                    user_info = subprocess.run(["ps", "-o", "user=", "-p", process_id], capture_output=True, text=True)
                    actual_owner = user_info.stdout.strip()
                    if actual_owner != "root":
                        process_owner = actual_owner
                        break
        except Exception as e:
            print(f"Error checking Apache: {e}")

        if not webserver:
            try:
                nginx_check = subprocess.run(["pgrep", "-a", "nginx"], capture_output=True, text=True)
                if nginx_check.stdout:
                    webserver = "Nginx"
                    process_lines = nginx_check.stdout.splitlines()
                    for line in process_lines:
                        process_id = line.split()[0]
                        user_info = subprocess.run(["ps", "-o", "user=", "-p", process_id], capture_output=True, text=True)
                        actual_owner = user_info.stdout.strip()
                        if actual_owner != "root":
                            process_owner = actual_owner
                            break

            except Exception as e:
                print(f"Error checking Nginx: {e}")

        return webserver, process_owner

    def chkWebsrv(self, wsName, wsOwner):
        try:
            result = subprocess.run(
                ["ps", "-u", wsOwner, "-o", "comm="],
                capture_output=True,
                text=True
            )
            if result.returncode == 0:
                processes = result.stdout.splitlines()
                if wsName in processes:
                    print(f"{green}Web server '{wsName}' owned by '{wsOwner}' is running.{reset}")
                    return True
                else:
                    print(f"{red}No process named '{wsName}' found for owner '{wsOwner}'.{reset}")
                    return False
            else:
                print(f"{red}Failed to retrieve process list for owner '{wsOwner}'.{reset}")
                return False
        except Exception as e:
            print(f"{red}An error occurred while validating the web server process: {e}{reset}")
            return False

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

    def chkSvcFile(self):
        services_file = None
        if os.path.exists("../services.json"):
            services_file = "../services.json"
        elif os.path.exists("services.json"):
            services_file = "services.json"
        else:
            print(f"{yellow}You are missing the required services.json file. Please go to {red}[{white}{srvGenURL}{red}]{white}{reset}")
            print(f"{yellow}and follow the instructions there to create the services.json file for your station. Copy that file to the {green}same{reset}")
            print(f"{yellow}directory that {white}dvmInstaller.py{yellow} is in and run {white}dvmInstaller.py again.{reset}")
            sys.exit(1)
        return services_file

    def prnWelMsg(self):
        os.system("clear")
        print(f"{green}Welcome to the DivumWX Skin Installer {white}v{blue}{version}{reset}")
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

    def setfnlOwner(self):
        user = getpass.getuser()
        base_path = f"/home/{user}/weewx-data"
        public_html_path = os.path.join(base_path, "public_html")
        db_path = os.path.join(public_html_path, "admin/db/dvmAdmin.db3")
        
        try:
            print(f"{yellow}Attempting to change ownership of {base_path}, {public_html_path} and {db_path} to {user}:{self.wsOwner}{reset}")
            os.system(f"sudo chown -R {user}:{self.wsOwner} {base_path}")
            os.system(f"sudo chown -R {user}:{self.wsOwner} {public_html_path}")
            os.system(f"sudo chown {user}:{self.wsOwner} {db_path}")
            print(f"{green}Successfully changed ownership of {base_path}, {public_html_path} and {db_path} to {user}:{self.wsOwner}{reset}\n")
            print(f"{yellow}Attempting to change permissions of {base_path} and {public_html_path} to 0775{reset}")
            os.system(f"sudo chmod -R 0775 {base_path}")
            os.system(f"sudo chmod -R 0775 {public_html_path}")
            print(f"{green}Successfully changed permissions of {base_path} and {public_html_path} to 0775{reset}\n")
            print(f"{yellow}Attempting to change permissions of {db_path} to 0666{reset}")
            os.system(f"sudo chmod 0666 {db_path}")
            print(f"{green}Successfully changed permissions of {db_path} to 0666{reset}\n")
        except Exception as e:
            print(f"{red}Error: {str(e)}{reset}")
            print(f"{red}There was an error attempting to change ownership or permissions.{reset}")
            print(f"{red}Your web pages will not be able to be displayed unless these commands are run successfully.{reset}")
            chown_command_base = f"sudo chown -R {user}:{self.ws_owner} {base_path}"
            chown_command_html = f"sudo chown -R {user}:{self.ws_owner} {public_html_path}"
            chown_command_db = f"sudo chown {user}:{self.wsOwner} {db_path}"
            chmod_command_base = f"sudo chmod -R 0775 {base_path}"
            chmod_command_html = f"sudo chmod -R 0775 {public_html_path}"
            chmod_command_db = f"sudo chmod 0666 {db_path}"
            print(f"\n{yellow}To manually attempt the changes, run the following commands:{reset}\n")
            print(f"\nDirectory: {blue}{base_path}{reset}")
            print(f"  - Change ownership to: {green}{user}:{self.ws_owner}{reset}")
            print(f"  - Command: {cyan}{chown_command_base}{reset}")
            print(f"  - Change permissions to: {green}0775{reset}")
            print(f"  - Command: {cyan}{chmod_command_base}{reset}")
            print(f"\nDirectory: {blue}{public_html_path}{reset}")
            print(f"  - Change ownership to: {green}{user}:{self.ws_owner}{reset}")
            print(f"  - Command: {cyan}{chown_command_html}{reset}")
            print(f"  - Change permissions to: {green}0775{reset}")
            print(f"  - Command: {cyan}{chmod_command_html}{reset}")
            print(f"\nFile: {blue}{db_path}{reset}")
            print(f"  - Change ownership to: {green}{user}:{self.ws_owner}{reset}")
            print(f"  - Command: {cyan}{chown_command_db}{reset}")
            print(f"  - Change permissions to: {green}0666{reset}")
            print(f"  - Command: {cyan}{chmod_command_db}{reset}")            
            print(f"\n{red}IMPORTANT:{reset} Your web pages will not be displayed until the above commands are executed successfully.")
                
    def chkPyVer(self):
        current_version = sys.version_info
        required_version = (3, 10)
        if current_version >= required_version:
            print(f"{yellow}Python version {white}{current_version.major}.{current_version.minor}.{current_version.micro} {yellow}meets the minimum required version of {green}3.10.x. {yellow}Proceeding with installation.{reset}")
        else:
            print(f"{red}Python version {white}{current_version.major}.{current_version.minor}.{current_version.micro} {red}does not meet the minimum required version of {white}3.10.x{red}. Installation aborted.{reset}")
            sys.exit(1)

    def chkVenv(self):
        if not hasattr(sys, 'real_prefix') and not (hasattr(sys, 'base_prefix') and sys.base_prefix != sys.prefix):
            print(f"{red}This script is not running inside a Python virtual environment. Please ensure that you have activated the virtual WeeWX environment by running the following command before proceeding.{reset}")
            print(f"{white}source /home/weewx/bin/activate{reset}")
            sys.exit(1)
        else:
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
        print(f"\n{white}For the purposes of file permissions and ownership, we will be using the below listed user and group.{reset}")
        print(f"Detected user: {white}{self.user}{reset}")
        print(f"Detected group: {white}{self.group}{reset}")
        confirm = input("Is this correct? (yes/no): ").strip().lower()
        if confirm in ['yes', 'y']:
            return (self.user, self.group)
        else:
            self.user = input("Please enter the correct user: ").strip()
            self.group = input("Please enter the correct group: ").strip()
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
        for i in range(5, 11):  # config_entries4 to config_entries9
            entry = entries[f'config_entries{i}']
            for section, values in entry.items():
                for key, value in values.items():
                    if isinstance(value, dict):
                        for sub_key, sub_value in value.items():
                            value[sub_key] = sub_value
                    values[key] = value
                config_data[section] = values

    def addBkpStanza(self, config_data, entries):
        entry = entries['backupEntry1']
        for section, values in entry.items():
            for key, value in values.items():
                if isinstance(value, dict):
                    for sub_key, sub_value in value.items():
                        value[sub_key] = sub_value
                values[key] = value
            config_data[section] = values

    def appendStanza(self, config_data, entries, do_overwrite):
        for i in range(5):  # config_entries0 to config_entries4
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
        for key, value in entries.items():
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

    def appendBkpSvcs(self, config_data, entries):
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
                else:
                    if "duplicate column name" in result.stderr:
                        print(f"Column {column} already exists in the database.")
                    else:
                        print(f"Error occurred while adding column {column}: {result.stderr}")

            except Exception as e:
                print(f"An exception occurred: {e}")

    def startWeewx(self):
        restart = input("Are you ready to start the WeeWX service to apply changes? (yes/no): ").strip().lower()
        if restart in ['yes', 'y']:
            try:
                print("Attempting to restart WeeWX service...")
                subprocess.check_call(["sudo", "systemctl", "start", "weewx"])
                print("WeeWX service started successfully.")
            except subprocess.CalledProcessError as e:
                print(f"Failed to start the WeeWX service: {e}")
        else:
            print("WeeWX service start was not requested. Please remember to start WeeWX manually for changes to take effect.")

    def stopWeewx(self):
        try:
            print("Attempting to stop the WeeWX service...")
            subprocess.check_call(["sudo", "systemctl", "stop", "weewx"])
        except subprocess.CalledProcessError as e:
            print(f"Failed to stop the WeeWX service: {e}")
            
    def run_installer(self, conf_file):
        self.user, self.group = self.chkUgrp()
        webserver, owner = self.websrvInfo()
        if webserver:
            print(f"{white}I think that you're running {yellow}{webserver}{white}, and it's run by {yellow}{owner}{white}, is this correct?{reset}")
            wsIsCorrect = input("yes/no: ").strip().lower()
            if wsIsCorrect in ['yes', 'y']:
                self.wsName = webserver
                self.wsOwner = owner
                self.wsGroup = owner
            else:
                while True:
                    self.wsName = input("Please enter the web server name (or 'q' to quit): ").strip()
                    if wsName.lower() == 'q':
                        print("Exiting script.")
                        sys.exit(0)
                    self.wsOwner = input("Please enter the web server owner (or 'q' to quit): ").strip()
                    if wsOwner.lower() == 'q':
                        print("Exiting script.")
                        sys.exit(0)
                    self.wsGroup = input("Please enter the web server group (or 'q' to quit): ").strip()
                    if wsGroup.lower() == 'q':
                        print("Exiting script.")
                        sys.exit(0)
                    if self.chkWebsrv(self.wsName, self.wsOwner):
                        break
                    else:
                        print(f"{red}The web server '{self.wsName}' owned by '{delf.wsOwner}' is not running. Please check the details and try again.{reset}")
        else:
            print(f"{red}I was unable to locate either Apache2 or Nginx running. Please enter the webserver name and the user and group that owns the process.{reset}")
        try:
            with open(conf_file) as infile:
                conf_content = infile.read()
                conf_content = re.sub(r'##.*\n', '', conf_content).replace("\n", "").replace("\t", "")
                d = json.loads(conf_content)
            with open(self.services_file) as infile:
                services_content = infile.read()
                services_content = re.sub(r'##.*\n', '', services_content).replace("\n", "").replace("\t", "")
                d.update(json.loads(services_content))
            user_path = os.path.expanduser(d["user"])
            skins_path = os.path.expanduser(d["skins"])
            weewx_config_file = os.path.expanduser(d["weewx_config_file"])
            config_data = ConfigObj(weewx_config_file, encoding='utf8', list_values=False, write_empty_values=True)
            print(f"{white}Your weewx.conf file [StdReport] section has {yellow}{config_data['StdReport'].get('HTML_ROOT')}{white} as your HTML_ROOT setting.{reset}")
            use_existing = input("Do you want to use the existing HTML_ROOT setting? (yes/no): ").strip().lower()
            if use_existing in ['yes', 'y']:
                existing_html_root = config_data['StdReport'].get('HTML_ROOT')
                home_directory = os.path.expanduser('~')
                html_root = os.path.join(home_directory, 'weewx-data', existing_html_root)
            else:
                html_root = input("Please enter the full path for HTML_ROOT (example: /path/to/weewx-data/public_html): ").strip()
            print(f"\n{green}Explanation of Apache/Nginx Document Root:{reset}")
            print(f"{white}The document root in a web server configuration like Apache or Nginx is the directory where the server{reset}")
            print(f"{white}looks for the files to serve for a particular domain or subdomain.{reset}")
            print(f"{white}For example, if the document root is set to {yellow}/var/www/html{white}, then when a client requests {yellow}http://mywebsite.com{white},")
            print(f"{white}the server will look for the index file (like index.php) in {yellow}/var/www/html.{reset}")
            print(f"{white}If a client requests {yellow}http://mywebsite.com/divumwx{white}, the server will look for the 'divumwx' directory inside")
            print(f"{white}the web server's document root ({yellow}/var/www/html/divumwx{white}).")
            print(f"{white}The document root essentially serves as the base directory for resolving all relative file paths{reset}")
            print(f"{white}that are requested via URL.{reset}\n")
            
            add_divumwx = input('Do you want to add "divumwx" to the HTML_ROOT? (yes/no): ').strip().lower()
            if add_divumwx in ['yes', 'y']:
                html_root = os.path.join(html_root, "divumwx")
                print("If you do not change your Document Root setting, your URL will be http://mywebsite.com/divumwx")
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
                    os.makedirs(html_root)
                    html_root_exists = True
                    html_root_created = True
                except Exception as e:
                    print(f"Error creating directory {html_root}: {e}")
            weewx_config_file_exists = os.path.exists(weewx_config_file)

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
            bkpWX = input('Do you wish to use the Divum Backup Service to backup your weewx database? (yes/no): ').strip().lower()
            if bkpWX in ['yes', 'y']:
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
            print(f"           Status: {green if weewx_config_file_exists else red}{'Yes' if weewx_config_file_exists else 'No'}{reset}")
            print(f"{blue}+--------------------------------------------------------------------------------------+{reset}")
            print(f"            {cyan}Detected user:{reset} {white}{self.user}{reset}")
            print(f"      {cyan}Detected user group:{reset} {white}{self.group}{reset}")
            print(f"          {cyan}Webserver:{reset} {green}{self.wsName}{reset}")
            print(f"    {cyan}Webserver Owner:{reset} {green}{self.wsOwner}{reset}")
            print(f"    {cyan}Webserver Group:{reset} {green}{self.wsGroup}{reset}")
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

            continue_install = input(f"{yellow}Do you want to start the installation process? (yes/no): {reset}").strip().lower()
            if continue_install not in ['yes', 'y']:
                print(f"{red}Installation aborted by user.{reset}")
                sys.exit(1)

            do_overwrite = d["over_write"] == "True"
            self.stopWeewx()
            try:
                if os.path.exists("user"):
                    print(f"{white}Copying {yellow}user{white} directory....")
                    if self.dirNotEmpty('user'):
                        distutils.dir_util.copy_tree("user", locations["user"], update=do_overwrite)
                        print(f"{green}Copied {white}user {green}directory successfully{reset}")
                    else:
                        print(f"{red}Directory 'user' is empty, unable to copy files.{reset}")
                        sys.exit(1)
                else:
                    print(f"{red}Directory 'user' does not exist.{reset}")
                    sys.exit(1)
                if os.path.exists("skins"):
                    print(f"{white}Copying {yellow}skins{white} directory....")
                    if self.dirNotEmpty('skins'):
                        distutils.dir_util.copy_tree("skins", locations["skins"], update=do_overwrite)
                        print(f"{green}Copied {white}skins {green}directory successfully{reset}")
                    else:
                        print(f"{red}Directory 'skins' is empty, unable to copy files.{reset}")
                        sys.exit(1)
                else:
                    print(f"{red}Directory 'skins' does not exist.{reset}")
                    sys.exit(1)
                if os.path.exists("www"):
                    if self.dirNotEmpty(html_root):
                        while True:
                            print(f"{yellow}\n\nThe {white}html_root{yellow} patch contains files from your previous skin. These must be removed.{reset}")
                            user_input = input("Do you want to empty the directory first? (y/n): ").strip().lower()
                            if user_input == 'y':
                                self.deleteContents(html_root)
                                print(f"{green}Directory {html_root} has been emptied.{reset}")
                                print(f"{white}Copying {yellow}www{white} directory....")
                                distutils.dir_util.copy_tree("www", locations["www"], update=do_overwrite)
                                print(f"{green}Copied {white}www {green}directory successfully{reset}")
                                self.chgPermRecur([locations["www"]], (self.user, self.wsOwner))
                                print(f"{green}File permissions and ownership in the {white}www {green}directory successfully set{reset}")
                                break
                            elif user_input == 'n':
                                print(f"{red}Not emptying the HTML_ROOT directory of the previous skin's files {reset}")
                                print(f"{red}can cause {white}SERIOUS{red} issues with DivumWX. You will need to empty the{reset}")
                                print(f"{red}the directory manually or add a sub-directory to install DivumWX to.{reset}")
                                sys.exit(1)
                            else:
                                print(f"{red}Invalid input. Please enter 'y' or 'n'.{reset}")
                else:
                    print(f"{red}Directory 'www' does not exist.{reset}")
                    sys.exit(1)
            except Exception as e:
                print(e)
            timestamp = time.strftime('%Y%m%d%H%M%S')
            backup_file = f"{weewx_config_file}.{timestamp}"
            distutils.file_util.copy_file(weewx_config_file, backup_file)

            print('Updating weewx config')
            timestamp = time.strftime('%Y%m%d%H%M%S')
            backup_file = f"{weewx_config_file}.{timestamp}"
            distutils.file_util.copy_file(weewx_config_file, backup_file)
            print(f"Backup of weewx.conf created: {backup_file}")
            time.sleep(1)
            print(f"{white}Verifying HTML_ROOT for added/updated stanzas{reset}")
            self.chgHroot(d, html_root)
            print(f"{white}Appending to existing stanzas{reset}")
            self.appendStanza(config_data, d, do_overwrite)
            print(f"{white}Adding new stanzas{reset}")
            self.addStanza(config_data, d)
            print(f"{white}Updating Services stanza{reset}")
            self.appendSvcs(config_data, d)
            if bkpWX:
                print(f"{white}Adding DivumWX Backup Service")
                self.addBkpStanza(config_data, d)
                self.appendBkpSvcs(config_data, d)
            else:
                print(f"{white}DivumWX Backup Service not enabled")
            print(f"{white}Writing new weewx.conf file{reset}")
            config_data.write()
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
            print(f"{white}Checking if default skin, {reset}{cyan}Seasons{reset}{white}is still enabled{reset}")
            print(f"{white}and disabling if true.{reset}")
            seasons_found = False
            for i, line in enumerate(lines):
                if "[[SeasonsReport]]" in line:
                    seasons_found = True
                elif seasons_found and "skin = Seasons" in line:
                    for j in range(i + 1, len(lines)):
                        if "enable = true" in lines[j]:
                            print(f"{yellow}Seasons skin is enabled, disabling{reset}")
                            lines[j] = lines[j].replace("enable = true", "enable = false")
                            print(f"{green}Successfully disabled Seasons skin{reset}")
                            seasons_found = False
                            break
                        elif "enable = false" in lines[j]:
                            print(f"{blue}Seasons skin is already disabled{reset}")
                            seasons_found = False
                            break
            time.sleep(2)
            with open(weewx_config_file, 'w') as file:
                file.writelines(lines)
            self.updDatabase()
            self.setfnlOwner()
            print(f"{white}Done! WeeWX must be {yellow}started{white} for changes to become active{reset}")
            print(f"{green}You will need to ensure that your webserver is set to deliver the DivumWX skin{reset}")
            self.startWeewx()
            
        except Exception as e:
            traceback.print_exc()
            print(e)

if __name__ == '__main__':
    DVMInstaller(sys.argv[1] if len(sys.argv) > 1 else None)