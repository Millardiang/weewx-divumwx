<?php include('dvmCombinedData.php');header('Content-type: text/html; charset=utf-8');date_default_timezone_set($TZ);?>
<div class="topmin">
<?php //temperature min year
 if ($wind["units"]=='C' && $temp["outside_year_min"]>30){echo "<topred1>",$temp["outside_year_min"]  ;echo "&deg;<smalluvunit>".$wind["units"] ; }
 else if ($temp["units"]=='C' && $temp["outside_year_min"]>=24){echo "<toporange1>",$temp["outside_year_min"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='C' && $temp["outside_year_min"]>18){echo "<topyellow1>",$temp["outside_year_min"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='C' && $temp["outside_year_min"]>12){echo "<topyellow2>",$temp["outside_year_min"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='C' && $temp["outside_year_min"]>=10){ echo "<topgreen1>", $temp["outside_year_min"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='C' && $temp["outside_year_min"]>-50){ echo "<topblue1>", $temp["outside_year_min"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
 //non metric
 if ($temp["units"]=='F' && $temp["outside_year_min"]>86){echo "<topred1>",$temp["outside_year_min"];echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='F' && $temp["outside_year_min"]>=75){echo "<toporange1>",$temp["outside_year_min"];echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='F' && $temp["outside_year_min"]>=64){echo "<topyellow1>",$temp["outside_year_min"];echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='F' && $temp["outside_year_min"]>53.6){echo "<topyellow2>",$temp["outside_year_min"];echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='F' && $temp["outside_year_min"]>=42.8){ echo "<topgreen1>", $temp["outside_year_min"];echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='F' && $temp["outside_year_min"]>-50){ echo "<topblue1>", $temp["outside_year_min"];echo "&deg;<smalluvunit>".$temp["units"] ; }
 ?>
</div></smalluvunit>

<div class="yearwordbig"><?php echo date('Y')?></div>


<div class="minword">Min</div></div>
<div class="mintimedate"><?php echo $temp["outside_year_mintime2"];?>
</div>  
<div class="topmax">
<?php //temperture max year celsius
 if ($temp["units"]=='C' && $temp["outside_year_max"]>30){echo "<topred1>",$temp["outside_year_max"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='C' && $temp["outside_year_max"]>=24){echo "<toporange1>",$temp["outside_year_max"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='C' && $temp["outside_year_max"]>18){echo "<topyellow1>",$temp["outside_year_max"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='C' && $temp["outside_year_max"]>12){echo "<topyellow2>",$temp["outside_year_max"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='C' && $temp["outside_year_max"]>=10){ echo "<topgreen1>", $temp["outside_year_max"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='C' && $temp["outside_year_max"]>-50){ echo "<topblue1>", $temp["outside_year_max"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
  //non metric
 if ($temp["units"]=='F' && $temp["outside_year_max"]>86){echo "<topred1>",$temp["outside_year_max"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='F' && $temp["outside_year_max"]>=75){echo "<toporange1>",$temp["outside_year_max"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='F' && $temp["outside_year_max"]>=64){echo "<topyellow1>",$temp["outside_year_max"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='F' && $temp["outside_year_max"]>53.6){echo "<topyellow2>",$temp["outside_year_max"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='F' && $temp["outside_year_max"]>=42.8){ echo "<topgreen1>", $temp["outside_year_max"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
 else if ($temp["units"]=='F' && $temp["outside_year_max"]>-50){ echo "<topblue1>", $temp["outside_year_max"]  ;echo "&deg;<smalluvunit>".$temp["units"] ; }
 ?>
</div></smalluvunit>
<div class="maxword">Max</div></div>
<div class="maxtimedate"><?php echo $temp["outside_year_maxtime2"];?></oorange></div> 
