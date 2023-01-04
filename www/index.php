<?php
########################################################
#			              						                
# 											                        
# 	                                                                                              
# 					  	                                           
########################################################
include_once('userfixedSettings.php');
if ($themelayout=="extra_row"){include_once('dvmDashboardIndex.php');}
else if ($themelayout=="tablet"){include_once('dvmTabletIndex.php');}
else include_once('dvmDashboardIndex.php');
 ?>
