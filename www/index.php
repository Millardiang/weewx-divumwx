<?php
########################################################
#			              						                
# 											                        
# 	                                                                                              
# 					  	                                           
########################################################
include_once('settings1.php');
if ($themelayout=="Layout 1"){include_once('dvmDashboardIndex.php');}
else if ($themelayout=="Layout 2"){include_once('dvmTabletIndex.php');}
else include_once('dvmDashboardIndex.php');
 ?>
