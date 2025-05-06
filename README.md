# DivumWX Skin Template for WeeWX

Some time ago now we stated that weewx-Weather34 skin template development had reached reached maturity, would not be developed further and at some stage it would be replaced.

Its replacement is weewx-DivumWX, the dashboard style format will be familiar but that is where the similarity ends. Every single module (card) has been completely re-written together with new graphic designs and a CSS re-write with a better performing responsive layout across all devices from cell phones to large wide screen monitors and TVs. The new default format is now a 5w x 4h grid with 20 full size card modules from a choice of 25 (compared with the previous 3w x 3h format). An alert banner which appears at the top of the page to flag up alerts, watches, warnings and advisories has also been introduced. A new installer process is being developed together with a suite of admin functions. Forecasts and alerts are provided by XWeather API.
<img width="1792" alt="DivumWX Full Version" src="https://github.com/user-attachments/assets/3bb57078-243f-4d16-aec2-d238f7ee3cdf" />
Live example of DivumWX https://claydonsweather.org.uk/

In the meantime, as development and testing is still ongoing, we have decided to release a "lite Version", DivumWX-Lite. This is fully functioning but only in a 3 x 3 grid format. We have pre-selected a range of sensors that we would expect to find as a minimum on any home weather station. The layout is responsive but it is particularly suitable to use on tablet size screens for desktop or wall displays.
<img width="1067" alt="DivumWX_Lite" src="https://github.com/user-attachments/assets/121bad7f-8bb4-482a-8ee2-34d7ceefee0c" />
Live example of DivumWX-Lite https://claydonsweather.org.uk/divumLite/

# DivumWX-Lite Skin Template for WeeWX

Weather Station website skin with Live Data for WeeWX. This version is compatible with WeeWX 5.1.0 and later builds / Python 3.7 and later. **At this point in time, the unique installer is only designed to work with WeeWX 5.1.0 or later installed by the pip install method. This version of the template requires the extended database schema introduced with WeeWX 4.0.0.** It is strongly recommended that you start with an entirely new clean Python3 pip install of WeeWX 5.1.0 or later.

Although this is free software it contains elements from third parties that cannot be used on commercial websites or websites that contain advertising. **It is therefore FORBIDDEN TO INSTALL ON COMERCIAL WEBSITES OR THOSE THAT ARE SPONSORED/SUPPORTED BY ADVERTISING.**

# Installation Guide for DivumWX-Lite

**The installer defaults to overwrite mode, userSettings.php will not be overwritten as it does not exist in the package. However, it is essential that any customisations you may have been made are backed up before running the install.**

IMPORTANT. If you are making a completley clean install of WeeWX and DivumLite Template it is strongly recommended that you allow the WeeWX database to establish itself for 24hours before attempting installing the template.

This installation guide assumes that you are already reasonably familiar with WeeWX and that it is already installed on your computer along with a webserver, php and curl.

Currently, Divumwx-Lite only supports WeeWX 5.1 or later, installed by Pip into a Python v3.7 or later virtual environment. Instructions can be found at https://weewx.com/docs/5.1/quickstarts/pip/

* Please familiarise yourself with the location of your WeeWX system files including your bin/user folder, skins folder and weewx.conf file. If you are unsure where to find these, please refer to the installation process here: - https://weewx.com/docs/5.1/quickstarts/pip/.

<h2 class="wp-block-heading">Install packaging.py</h2>
        	
    # Activate the WeeWX virtual environment
    source ~/weewx-venv/bin/activate
    # Verify that your version of pip is current
    pip3 install –upgrade pip
    # Install packaging.py
    pip3 install packaging


IMPORTANT DivumWX-Lite requires PHP8.2 or later. Please make sure you install all the PHP modules appropriate for your version of PHP. Failure to due so may mean that forecasts and current conditions fail to update. There are numerous instructions to be found on The Internet for various flavours of Linux. This is an example for installing PHP8.4 modules on a Debian based distribution: -

<h2 class="wp-block-heading">Install PHP</h2>

<p>Connect to Raspberry Pi via SSH and execute command to download GPG key:</p>

<pre class="highlighter"><code class="language-plaintext no-line-numbers">sudo wget -qO /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg</code></pre>

<p>Add PHP repository:</p>

<pre class="highlighter"><code class="language-plaintext no-line-numbers">echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php.list</code></pre>

<p>Update the package lists:</p>

<pre class="highlighter"><code class="language-plaintext no-line-numbers">sudo apt update</code></pre>

<p>Next, install PHP 8.4 with command line interface (CLI):</p>

<pre class="highlighter"><code class="language-plaintext no-line-numbers">sudo apt install -y php8.4-common php8.4-cli</code></pre>

<p>Check PHP version when installation was finished:</p>

<pre class="highlighter"><code class="language-plaintext no-line-numbers">php --version</code></pre>

<p>There are various PHP extensions that provide additional functionality. PHP extensions can be installed using the following syntax:</p>

<pre class="highlighter"><code class="language-plaintext no-line-numbers">sudo apt install -y php8.4-extension_name</code></pre>

<p> Execute the following command to install commonly used PHP extensions:</p>

<pre class="highlighter"><code class="language-plaintext no-line-numbers">sudo apt install -y php8.4-common php8.4-curl php8.4-gd php8.4-mbstring php8.4-xml php8.4-zip</code></pre>

<p>We can use <code>-m</code> option to check what extensions are installed.</p>

<pre class="highlighter"><code class="language-plaintext no-line-numbers">php -m</code></pre>

<h2 class="wp-block-heading">PHP integration with MySQL or MariaDB</h2>

<p>To use PHP with MySQL or MariaDB database, we need to install the following extension:</p>

<pre class="highlighter"><code class="language-plaintext no-line-numbers">sudo apt install -y php8.4-mysql</code></pre>

<h2 class="wp-block-heading">PHP integration with Apache</h2>

<p>If we want to integrate PHP with Apache HTTP server, then install the following extension:</p>

<pre class="highlighter"><code class="language-plaintext no-line-numbers">sudo apt install -y libapache2-mod-php8.4</code></pre>

<p>Once installation is complete, restart Apache:</p>

<pre class="highlighter"><code class="language-plaintext no-line-numbers">sudo service apache2 restart</code></pre>

<h2 class="wp-block-heading">Testing PHP</h2>

<p>Create a new <code>main.php</code> file:</p>

<pre class="highlighter"><code class="language-plaintext no-line-numbers">nano main.php</code></pre>

<p>Add the following code:</p>

<pre class="highlighter"><code class="language-php">&lt;?php

echo 'Hello world';</code></pre>

<p>Run the following command to test a script:</p>

<pre class="highlighter"><code class="language-plaintext no-line-numbers">php main.php</code></pre>

<h2 class="wp-block-heading">Install older versions</h2>

<p>PHP 8.3 is an older version that is still supported. It can be installed by changing <code>php8.4</code> to <code>php8.3</code> in this post presented commands.</p></div>

    
* This install process assumes that your are using one of the officially documented WeeWX installs and a typical Apache2 or Nginx web server configuration with a document root of /var/www/html. In this instance, at the end of the installation process your path to the DivumWX-Lite skin will be /var/www/html/divumLite. If your installation deviates from this, you will need to adjust the paths in your weewx.conf file after the installation process has taken place.

* I am very gratefully to Jerry Dietrich for writing a new installer which has been adapted for use with DivumWX-Lite. This installer copies everything to the correct places and automatically configures the correct web server ownerships, permissions and groups etc. The whole process is very fast and your skin will be up and running in no time.

* Go to https://Millardiang.github.io/weewx-DivumWX-Lite to complete the pre-install web services settings which which generates 'services.txt' in your default Download folder. 

* From the command line: - 
                
		Download weewx-DivumWX-Lite-master.zip from https://github.com/Millardiang/weewx-DivumWX-Lite/archive/refs/heads/main.zip into your Download folder alongside the services.txt file
		cd [path_to_your_Download_folder]
		unzip weewx-DivumWX-Lite-master.zip
		cd weewx-DivumWX-Lite-master

Find the file named "pip.conf". Open up this file to edit. On lines 2 (in 2 places), 3 and 8 you will see <USER>. You must change this to the login/user ID that you use for your WeeWX installation. For example, if your login/user ID is "fred", line 3 would become "weewx_config_file":"/home/fred/weewx-data/weewx.conf",. 

The default path follows the default document root for Apache2 and Nginx server software, i.e. /var/www/html. If your path differs from this, you must change /var/www/html/divumwx/ on lines 2,4,12,28,44 accordingly.
 
Save the file. Then start the installer.

      sudo python3 dvmInstaller.py

* Follow the prompts
		


* When the installation process completes, restart WeeWX and from command line run: -
            	
      # Activate the WeeWX virtual environment
      source ~/weewx-venv/bin/activate
      # Run all enabled reports:    
      weectl report run

This will allow some of the required variable data to be generated immediately without having to wait for the next report generation interval.

* You can now test that the template is working by opening it up in your browser.

* Any problems, please raise an Issue in this repository attaching a debug report, your skin.conf files and a journal report covering at least two archive cycles from startup.
  
      # Debug Report
      source ~/weewx-venv/bin/activate   
      weectl debug

      # Journal Report
      sudo journalctl -u weewx -f
