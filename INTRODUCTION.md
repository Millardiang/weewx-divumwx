# Introduction to the WeeWX-DivumWX Skin Template

This is the first release candidate for DivumWX. It is the successor project for weewx-Weather34 whose development is now at end of life.

The grid dashboard format will be familiar to the users of the former but that is where it ends. Virtually every part of the under-lying code has been re-written with extensive use of Java Script libraries to generate much of the modular graphic appearance. 

There are over 20 modules to choose from and you are able to select between 9 and 20 using the new setup features. The page is fully responsive and it smoothly moves from a 1 x 20 grid for cell phones to a 5 x 4 grid for large format monitors without losing shape or resolution. There is even a special 3 x 3 format for those who wish to use a tablet for a wall mount or desk mount display. 

This is a complex and feature rich template which requires significant changes to weewx.conf etc. We recognise that not every user is comfortable with making the required changes so we have developed a bespoke installation process to make installation for all users as painless as possible.  Once installed, there is a suite of administration processes which allow you to make changes to your layout and monitor its performance. For the moment we have concentrated on making the installer process compatible with WeeWX 5.1 installed into a virtual environment using pip as we think this is the future direction. However we will support packaged installations of WeeWX 5.1 later if there is a demand.

It has been quite a journey in reaching RC1 and I could not have done it alone. I have been joined by Sean Balfour and Steven Sheely. 

Sean is responsible for the majority of the graphics used to augment the various modules of the web page along with the development of some highly complex mathematics used in various astronomical calculations.

Steven has masterminded the overhaul of the installation and administration processes.

For my part I have taken care of project lead (design and content strategy), html and css overhaul, APIs and web services. 

There is also a large group of long suffering testers who have been extremely patient in testing a succession of private beta versions. Too many names to mention here but thank you all for your countless contributions.

I would also like to thank Jerry, David, Brian, Ken, Wim and William who have materially contributed to my knowledge over several years.

Finally to those folks from WeeWX Tom, Gary, Matthew, Vince et al. They never cease to amaze me with their genorosity of time, knowledge and patience. Without them there would be no DivumWX.

The DivumWX Team: -
      Ian Millard
      Sean Balfour
      Steven Sheely
