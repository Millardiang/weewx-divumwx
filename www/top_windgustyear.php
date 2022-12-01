<?php include('dvmCombinedData.php');header('Content-type: text/html; charset=utf-8');date_default_timezone_set($TZ);?>
<div class="topmin">


<?php 
 if ($wind["units"]=='kts'){$wind["units"]="kn";}
 //wind max month
 if ($wind["units"]=='km/h' && $wind["gust_month_max"]>60){echo "<topred1>",$wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='km/h' && $wind["gust_month_max"]>40){echo "<toporange1>",$wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='km/h' && $wind["gust_month_max"]>30){echo "<topyellow1>",$wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='km/h' && $wind["gust_month_max"]>10){ echo "<topgreen1>", $wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='km/h' && $wind["gust_month_max"]>0){ echo "<topblue1>", $wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 //wind  mph
 if ($wind["units"]=='mph' && $wind["gust_month_max"]>40){echo "<topred1>",$wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='mph' && $wind["gust_month_max"]>24){echo "<toporange1>",$wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='mph' && $wind["gust_month_max"]>18){echo "<topyellow1>",$wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='mph' && $wind["gust_month_max"]>6){ echo "<topgreen1>", $wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='mph' && $wind["gust_month_max"]>-50){ echo "<topblue1>", $wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 //wind  ms
 if ($wind["units"]=='m/s' && $wind["gust_month_max"]>16.6){echo "<topred1>",$wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='m/s' && $wind["gust_month_max"]>11){echo "<toporange1>",$wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='m/s' && $wind["gust_month_max"]>8.3){echo "<topyellow1>",$wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='m/s' && $wind["gust_month_max"]>2.7){ echo "<topgreen1>", $wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='m/s' && $wind["gust_month_max"]>-50){ echo "<topblue1>", $wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 //wind  kts
 
 if ($wind["units"]=='kn' && $wind["gust_month_max"]>32.40){echo "<topred1>",$wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='kn' && $wind["gust_month_max"]>21.60){echo "<toporange1>",$wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='kn' && $wind["gust_month_max"]>16.20){echo "<topyellow1>",$wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='kn' && $wind["gust_month_max"]>5.40){ echo "<topgreen1>", $wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='kn' && $wind["gust_month_max"]>-0){ echo "<topblue1>", $wind["gust_month_max"]."<smallwindunit>".$wind["units"] ; }

?>
</div></smalluvunit>

<div class="minword"><?php echo date('M')?></div></div>

<div class="mintimedate"><?php echo $wind["gust_month_maxtime2"]?>
</div>  
<div class="yearwordbig"><?php echo date('Y')?></div>

<div class="topmax">
<?php //wind max year
 if ($wind["units"]=='km/h' && $wind["gust_year_max"]>60){echo "<topred1>",$wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='km/h' && $wind["gust_year_max"]>40){echo "<toporange1>",$wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='km/h' && $wind["gust_year_max"]>30){echo "<topyellow1>",$wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='km/h' && $wind["gust_year_max"]>10){ echo "<topgreen1>", $wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='km/h' && $wind["gust_year_max"]>0){ echo "<topblue1>", $wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 //wind mph
 if ($wind["units"]=='mph' && $wind["gust_year_max"]>40){echo "<topred1>",$wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='mph' && $wind["gust_year_max"]>24){echo "<toporange1>",$wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='mph' && $wind["gust_year_max"]>18){echo "<topyellow1>",$wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='mph' && $wind["gust_year_max"]>6){ echo "<topgreen1>", $wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='mph' && $wind["gust_year_max"]>-50){ echo "<topblue1>", $wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 //wind  ms
 if ($wind["units"]=='m/s' && $wind["gust_year_max"]>16.6){echo "<topred1>",$wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='m/s' && $wind["gust_year_max"]>11){echo "<toporange1>",$wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='m/s' && $wind["gust_year_max"]>8.3){echo "<topyellow1>",$wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='m/s' && $wind["gust_year_max"]>2.7){ echo "<topgreen1>", $wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='m/s' && $wind["gust_year_max"]>-50){ echo "<topblue1>", $wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 //wind kts
 if ($wind["units"]=='kn' && $wind["gust_year_max"]>32.4){echo "<topred1>",$wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='kn' && $wind["gust_year_max"]>21.6){echo "<toporange1>",$wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='kn' && $wind["gust_year_max"]>16.2){echo "<topyellow1>",$wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='kn' && $wind["gust_year_max"]>5.4){ echo "<topgreen1>", $wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
 else if ($wind["units"]=='kn' && $wind["gust_year_max"]>-0){ echo "<topblue1>", $wind["gust_year_max"]."<smallwindunit>".$wind["units"] ; }
?>
</div></smalluvunit>
<div class="maxword"><?php echo date('Y')?></div></div>
<div class="maxtimedate"><?php echo $wind["gust_year_maxtime2"];?>
</div>  

 
