<?php
############################################################################
# A Project of TNET Services, Inc. and Saratoga-Weather.org (Canada/World-ML template set)
############################################################################
#
#   Project:    Sample Included Website Design
#   Module:     sample.php
#   Purpose:    Sample Page
#   Authors:    Kevin W. Reed <kreed@tnet.com>
#               TNET Services, Inc.
#
# 	Copyright:	(c) 1992-2007 Copyright TNET Services, Inc.
############################################################################
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA
# Version 1.01 - 12-May-2022 - support for new get-meteoalarm-warning-inc.php V3.00
# Version 1.02 - 18-May-2022 - updated access for get-meteoalarm-warning-inc.php from file
############################################################################
#	This document uses Tab 4 Settings
############################################################################
require_once("Settings.php");
require_once("common.php");
############################################################################
$TITLE= $SITE['organ'] . " - " . langtransstr("Watches/Warnings/Advisories");
$useUTF8 = true; // UTF-8 required for meteoalarm.org alert information
$showGizmo = true;  // set to false to exclude the gizmo
include("top.php");
############################################################################
?>
</head>
<body>
<?php
############################################################################
include("header.php");
############################################################################
include("menubar.php");
############################################################################
?>

<div id="main-copy">
  
 	  <h3><?php langtrans('Watches/Warnings/Advisories'); ?></h3> 
        
  
  <?php if(isset($SITE['useMeteoalarm']) and $SITE['useMeteoalarm'] 
	          and file_exists('get-meteoalarm-warning-inc.php')) {
    include_once("get-meteoalarm-warning-inc.php"); // Use the EU Meteoalarm warning
		if(file_exists($warn_details)) {
			readfile($warn_details);
		}
	} else { 
// -------------------------------------------------------
// put your code for your regional weather warnings below this line ?>

   <div class="advisoryBox" style="text-align: left; background-color:#FFFF99">
	Note: you'll need to find a link to your national watch/warn/advisory source.
	</div>
	

  <?php // end of put your code above this line
// -------------------------------------------------------
    } // end roll-your-own warnings ?>
  
  
</div><!-- end main-copy -->

<?php
############################################################################
include("footer.php");
############################################################################
# End of Page
############################################################################
?>