# DivumWX Installation Guide

The installation process has changed greatly since it's original inception for the Weather34 skin. With the release of DivumWX we have rewritten almost all of the code and moved most of it into functions. We've made it more intuitive so that it can automatically suss out where most things that it needs are located. It now gives you information each step of the way and allows to to make changes at critical junctures, with the major addition of a debugging log

At this time we ONLY support the following

	Linux (Most any Distro)
	Python 3
	weewx 5.x or higher
	weewx installed using pip
	Apache2 or Nginx
	
Now, with that being said, I want to make sure that you understand that we are not trying to cover every conceivable scenario with the installer, especially when it comes to the DOCUMENTROOT of your webserver and how you are delivering your web pages, either locally, or publicly available on the web. Since we are only supporting the pip installation method at this time and we are not going to try and cover all potential web server DOCUMENTROOT scenarios.

What we do, however, is look at your existing weewx.conf file and pull out the existing HTML_ROOT.  This should be the path that points to where the generated reports are placed, relative to WEEWX_ROOT, unless you give it a full path, For example, if your weewx.conf contained "public_html" for the HTML_ROOT, in weewx 5.x and higher, in ~/weewx-data/public_html, and, as we all know, the "~" means the running users home directory.  This then means that if the default skin, "Seasons" is enabled, then the Seasons Report files would be generated in that directory. Same for any other skin that was enabled.

So, since a directory called public_html in a users home directory in not a "normal" location for your web server to serve pages from, we check for the existence of a symlink (symbolic link) between your web servers DOCUMENTROOT and the HTML_ROOT entry in weewx.conf, which would look something like this:

					lrwxrwxrwx 1 rayvenhaus rayvenhaus    39 Jan 10 13:58 public_html -> /home/rayvenhaus/weewx-data/public_html

If we find that symlink, that tells us that you are serving pages. or can serve pages, from that directory.  In the interest or keeping your previous skin operational, we then tack /divumwx onto the constructed HTML_ROOT and now have the path to where we are going to place the DivumWX www files:

					~/weewx-data/public_html/divumwx

Once the installer is finished, how you modify your directory structure to view your pages is entirely up to you. If you make any modifications to weewx.conf after the installer is completed its run, then please remember, any assistance that we can provide will be on a case by case basis, your mileage will vary and please do not fold, spindle or mutilate the packaging when shipping.

Let's give it a go, have fun out there, be safe and may the odds ever be against the opposing thumbs.

Team Divum!