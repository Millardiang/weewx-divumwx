# ATTENTION
Please note that any branch, other than main, is assumed to be **broken**, or will be **broken**, or can be **broken**, at any given time. Any branch, other than main, are branches that are being worked on by the Development Team for new enhancements and bug fixes. If you download any branch, other than main, and it is **highly recommended** that you do not, you use that branch at your own risk, and no bug reports or issues raised will be acted upon. Lastly, remember, this software is currently in **ALPHA** and is _not ready_ for prime time in any definition of the word and must not be used in a production environment.

# DivumWX skin for WeeWX - Now Under Construction
Weather Station website skin with Live Data for WeeWX. 

# Setup

Follow the instructions in the 'installation guide' ([INSTALLATION_GUIDE.md](https://github.com/Millardiang/weewx-divumwx/blob/alpha/INSTALLATION_GUIDE.md)) to install the template.

# Beta 0.9.99.00 has been released

As always, as we are still in Alpha testing, back up your current install first. You will need to make a full install over the top of your existing install, as the key file divumwx.py has also been updated.

You will also have to add this stanza to your weewx.conf (manually for the time being, but it will be automated in future). This is a reconfiguration of how the Earth and Moon images are downloaded/cached and are only updated every hour. This will prevent denial of service locks from being activated at the fourmilab.ch servers.

##############################################################################

# Apply your own latitude and longitude (absolute values only to 3 decimal places) and North, South or East, or West to the URLs in this section.

[DivumWXSkyObject]
    enable = True
    so_interval = 3600
    so1_url = https://www.fourmilab.ch/cgi-bin/Earth?img=NASAmMM.evif&imgsize=320&dynimg=y&opt=-l&lat=[YOUR LATITUDE]&ns=[YOUR North or South location]&lon=[YOUR LONGITUDE]&ew=[YOUR East or West location]t&alt=35785&tle=&date=0&utc=&jd=/Earth.jpg			 
    so1_filename = /var/www/html/weewx/divumwx/img/earth-1.jpg
    so2_url = https://www.fourmilab.ch/cgi-bin/Earth?img=LRO_100m.evif&imgsize=320&dynimg=y&gamma=1.32&opt=-l&lat=[YOUR LATITUDE]&ns=[YOUR North or South location]&lon=[YOUR LONGITUDE]&ew=[YOUR East or West location]&alt=8527&tle=&date=0&utc=&jd=/Earth.jpg
    so2_filename = /var/www/html/weewx/divumwx/img/moon-1.jpg
 

############################################################################## 

Also, and this is very, very important, you must delete dvmGetMoonEarth.php from your divumwx folder
Please note the latitude or longitude should be a maximum or 3 decimal places. Also no negative values if you are in a West of South location, just an absolute value for latitude or longitude example -12.345 should be entered as 12.345
