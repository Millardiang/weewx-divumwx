# Caution
This extension is still in beta at this time. Exercise caution and ensure you backup all essential files before you commence.
You will experience a lot of logging at this stage which will be removed in due course.

ALWAYS BACKUP YOUR DATABASE FIRST - It is not a nice to have but a necessity whenever your make changes to your WeeWX installation.

# DivumWX Installation Guide - For WeeWX installed by pip method
This installation guide assumes that you already have a stable WeeWX installation running with your specific hardware driver. The installation has been tested with pip installed WeeWX 5.4.0 and Debian 13 (Trixie).
The installation instructions are based on pip. For alternative installations refer to [WeeWX Quickstarts](https://weewx.com/docs/5.4/quickstarts).
     
## Dependencies
Skyfield and Requests Python Modules are required. (Skyfield will also automatically install its own dependencies). : -

     # Activate the WeeWX virtual environment
     source ~/weewx-venv/bin/activate
     # Required install Skyfield and Requests into the virtual environment
     python3 -m pip install skyfield
     python3 -m pip install requests
     sudo systemctl restart weewx
     
## Install weewx-DivumWX
During the install process you there are several prompts which depending on your hardware setup and location will give you the option to install additional dashboard cards up to a total of 25.
Make sure you have to hand: -
* An [OpenWeather API key](https://home.openweathermap.org/api_keys) for global weather warnings and alerts.
* Your nearest airport [METAR code](https://metar-taf.com/)
* If you live in England, your [Location Code](https://geoportal.statistics.gov.uk/datasets/6c968989f5d2405791d17feb27c7629e/explore). Example South East is E12000008

          source ~/weewx-venv/bin/activate
          weectl extension install https://github.com/Millardiang/weewx-divumwx/archive/refs/heads/main.zip
          sudo mkdir -p /var/www/html/divumwx && sudo chown -R $(whoami) /var/www/html/divumwx
          sudo systemctl restart weewx

## Uninstall process
Hopefully you will not feel the need to do so but the process is: -

          cd <to_your_bin/user_folder>
          source ~/weewx-venv/bin/activate
          python3 divumwx_uninstall_helper.py
          weectl extension uninstall divumwx

# DivumWX Installation Guide - For WeeWX installed by Debian package
This installation guide assumes that you already have a stable WeeWX installation running with your specific hardware driver. The installation has been tested with deb installed WeeWX 5.4.0 and Debian 13 (Trixie).
The installation instructions are based on deb. For alternative installations refer to [WeeWX Quickstarts](https://weewx.com/docs/5.4/quickstarts).
     
## Dependencies
Skyfield and Requests Python Modules are required. (Skyfield will also automatically install its own dependencies). : -


     # Required install Skyfield and Requests into the virtual environment
     sudo apt install python3-skyfield
     sudo apt install python3-requests
     sudo systemctl restart weewx
     
## Install weewx-DivumWX
During the install process you there are several prompts which depending on your hardware setup and location will give you the option to install additional dashboard cards up to a total of 25.
Make sure you have to hand: -
* An [OpenWeather API key](https://home.openweathermap.org/api_keys) for global weather warnings and alerts.
* Your nearest airport [METAR code](https://metar-taf.com/)
* If you live in England, your [Location Code](https://geoportal.statistics.gov.uk/datasets/6c968989f5d2405791d17feb27c7629e/explore). Example South East is E12000008

          sudo weectl extension install https://github.com/Millardiang/weewx-divumwx/archive/refs/heads/main.zip
          sudo mkdir -p /var/www/html/divumwx && sudo chown -R weewx:weewx /var/www/html/divumwx
          sudo systemctl restart weewx

## Uninstall process
Hopefully you will not feel the need to do so but the process is: -

          cd <to_your_bin/user_folder>
          python3 divumwx_uninstall_helper.py
          sudo weectl extension uninstall divumwx          

## User feedback
Please feel free to use the [Issues log](https://github.com/Millardiang/weewx-divumwx/issues) for any feedback you may have. I am particularly interested for feedback from users who have installed WeeWX by methods other than pip. This will enable me to provide a more comprehensive set of install instruction in the future.
Thank you, 
ianmillard@icloud.com
