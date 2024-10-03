#!/usr/bin/env python
# -*- coding: utf-8 -*-

from datetime import date  
from math import radians as rad, degrees as deg
from ephem import Equatorial, Ecliptic   
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
g.date = date.today() 
g.date -= ephem.hour

tilt = Equatorial('90', '0', epoch = g.date)
print(deg(Ecliptic(tilt).lat))  
