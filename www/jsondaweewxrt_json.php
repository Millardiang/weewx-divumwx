<?php
#####################################################################################################################                                                                                                        #
#                                                                                                                   #
# weewx-divumwx Skin Template maintained by The DivumWX Team                                                        #
#                                                                                                                   #
# Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved                                 #
#                                                                                                                   #
# Distributed under terms of the GPLv3. See the file LICENSE.txt for your rights.                                   #
#                                                                                                                   #
# Issues for weewx-divumwx skin template should be addressed to https://github.com/Millardiang/weewx-divumwx/issues # 
#                                                                                                                   #
#####################################################################################################################


$jsonS = 'realtimeLabels.json';
$jsonS = file_get_contents($jsonS);
$sdata = json_decode($jsonS, true);

echo $sdata["$weewxrt[1]"]["value"];
for ($k = 0;$k < 70 ;$k++)

{//echo "weewxrt[".$k."]. = ".$ldata["$weewxrt[$k]"]." = ".$weewxrt[$k]."</br>";
echo $sdata["$weewxrt[$k]"];}
?>