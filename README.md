# ATTENTION
Please note that any branch, other than main, is assumed to be **broken**, or will be **broken**, or can be **broken**, at any given time. Any branch, other than main, are branches that are being worked on by the Development Team for new enhancements and bug fixes. If you download any branch, other than main, and it is **highly recommended** that you do not, you use that branch at your own risk, and no bug reports or issues raised will be acted upon. Lastly, remember, this software is currently in **ALPHA** and is _not ready_ for prime time in any definition of the word and must not be used in a production environment.

# DivumWX skin for WeeWX - Now Under Construction
Weather Station website skin with Live Data for WeeWX. 

# Setup

To install the template, follow the instructions in the 'installation guide' ([INSTALLATION_GUIDE.md](https://github.com/Millardiang/weewx-divumwx/blob/alpha/INSTALLATION_GUIDE.md)).

# Version 0.9.99.00 Beta 1 has been released

As we are still in beta testing, back up your current installation first. You will need to make a full installation over your existing installation, as the critical file divumwx.py has also been updated.

You will also have to add this stanza to your weewx.conf (manually for the time being, but it will be automated in future). This is a reconfiguration of how the Earth and Moon images are downloaded/cached and are only updated every hour. This will prevent denial of service locks from being activated at the fourmilab.ch servers.
