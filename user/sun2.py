#!/usr/bin/env python
# -*- coding: utf-8 -*-

from datetime import date  
from math import radians as rad,degrees as deg   
import ephem
import json  
  
g = ephem.Observer()
g.lat = rad(51.06728920273504)   
g.long = rad(13.767345965293318)
g.elevation = 120.81746673583984
g.pressure = 0 
g.horizon = '-0:34' # 0:00 normal -0:34 Naval
 
s = ephem.Sun()  
  
g.date = date.today() 
g.date -= ephem.hour  
  
for i in range(24 * 60): # calculate positions for 1 day !  
    s.compute(g)     
  
    # print("[",deg(s.az),",",deg(s.alt),"],")
    print('[',deg(s.az),',',deg(s.alt),'],')
    g.date += ephem.minute * 1