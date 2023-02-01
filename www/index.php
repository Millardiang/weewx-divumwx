<?php
########################################################
#			              						                
# 											                        
# 	                                                                                              
# 					  	                                           
########################################################
include_once('userSettings.php');
if ($themelayout=="extra_row"){include_once('dvmIndexDashboard.php');}
else if ($themelayout=="tablet"){include_once('dvmIndexTablet.php');}
else include_once('dvmIndexDashboard.php');
 ?>
