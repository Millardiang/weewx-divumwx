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
//include('dvmShared.php');
$templateversion = "DivumWX-(RESPONSIVE)";
$os = shell_exec('lsb_release -d');

if (str_contains($os, "Manjaro")) {$osLogo = $archLogo;}
else if (str_contains($os, "Debian")) {$osLogo = $DebianLogo;}
else if (str_contains($os, "Ubuntu")) {$osLogo = $ubuntuLogo;}
else if (str_contains($os, "Mint")) {$osLogo = $mintLogo;}
$version = str_replace('Description:',' ',$os);
$os_version = $version;
?>
