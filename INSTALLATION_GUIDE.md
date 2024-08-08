# DivumWX Installation Guide

The installation process has changed greatly since it's original inception for the Weather34 skin. With the release of DivumWX we have rewritten almost all of the code and moved most of it into functions. We've made it more intuitive so that it can automatically suss out where most things that it needs are located. It now gives you information each step of the way and allows to to make changes at critical junctures.

At this time we ONLY support the following

	Linux (Most any Distro)
	Python 3
	weewx 5.x or higher
	weewx installed using pip
	Apache2 or Nginx
	
This installation guide assumes that you are already reasonably familiar with WeeWX and that it is already installed on your computer and displaying a webpage of at least the default Seasons skin.

At this point in time, we do not yet support upgrading an existing DivumWX from this package, we only support a fresh, new installation of DivumWX.

* Please familiarise yourself with the location of your WeeWX system files including your bin/user folder, skins folder and weewx.conf file. If you are unsure where to find these, please refer to the installation processes here: - https://weewx.com/docs/5.0/usersguide/where/ on the pip tab.


*NOTE
* Prior to starting the installation process you MUST go to this page: https://www.divumwx.org/settingsGen/ and fill out the DivumWX services.txt Generator form which generates 'services.json' file which will download to your default Download folder. This file must be uploaded to your server and placed in the same folder the the dvmInstaller.py file 

Installation

    * From the command line: - 
                
		Download the current DivumWX archive from https://www.divumwx.org/files/latest.tar.gz into your local Download folder alongside the services.json file.
		Upload both files to your weewx server (if needed)
		Log into your weewx server (if needed)
		* This activaes the python virtual environment
		source ~/weewx-venv/bin/activate
		cd to the directory where you uploaded the new files
		Unpack the archive: tar –xvzf latest.tar.gz
		* This will have created a directory called "divumwx-x.x.xx.xxx" where x.x.xx.xxxx" is the version number of the latest release.
		mv ./services.json ./divumwx.x.x.xx.xxx/services.json
		cd divumwz.x.x.xx.xxx

	* Install DivumWX: -

		python3 dvmInstaller.py
			
			From this point the installer will ask you a few questions, all you to set the directory for your HTML_ROOT, if needed and will display the final variables it is going to use and allow you a final Go/NoGo question, and, should you allow it to proceed, it will install DivumWX and inform you along the way what it is doing. Upon completion, it will tell you to restart weewx to allow the changes to take place. It will also have made a backup of your existing weewx.conf file, in the format of weewx.conf.yyyymmddhhmmss, prior to making any changes to it
		
	* Restart or Start weewx
		
			sudo systemctl start weewx
			
			or
			
			sudo systemctl restart weewx
