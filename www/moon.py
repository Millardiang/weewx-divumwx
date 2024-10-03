#! python3
# -*- coding: utf-8 -*-

###############################################################################################
##        ________   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___     #
##       |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |    #
##       (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /     #
##       |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/      #
##       (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \      #
##       |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \     #
##       (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|    #
##                                                                                            #
##     Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
##      Distributed under terms of the GPLv3.  See the file LICENSE.txt for your rights.      #
##    Issues for weewx-divumwx skin template are only addressed via the issues register at    #
##                   https://github.com/Millardiang/weewx-divumwx/issues                     #
###############################################################################################

from datetime import date  
from math import radians as rad,degrees as deg  
  
import ephem
import sys
lat = float(sys.argv[1])
lon = float(sys.argv[2])
elev = float(sys.argv[3])
g = ephem.Observer()
g.lat = rad(lat)   
g.long = rad(lon)
g.elevation = elev
g.pressure = 0
g.horizon = '-0:34' # 0:00 normal -0:34 Naval  
m = ephem.Moon()  
g.date = date.today()  
g.date -= ephem.hour
   
for i in range(24 * 60): # calculate positions for 1 day !  
    m.compute(g)   
  
    # print("[",deg(m.az),",",deg(m.alt),"],")
    print("[",deg(m.az),",",deg(m.alt),"],")  
    g.date += ephem.minute * 1  
