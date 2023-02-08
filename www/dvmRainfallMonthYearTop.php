<?php include('dvmCombinedData.php');header('Content-type: text/html; charset=utf-8');date_default_timezone_set($TZ);?>
<div class="topmin">

<?php //rain month 
if($rain["month_total"]>=1000){ echo "<topblue1>".round($rain["month_total"],0)  ;}
else  echo "<topblue1>".$rain["month_total"]  ;echo "<smallwindunit>".$rain["units"];
?>
</div></smallwindunit>
<div class="minword"><?php echo date('M')?></div></div>
<div class="mintimedate">Total 
</div>  

<div class="yearwordbig"><?php echo date('Y')?></div>
<div class="topmax">
<?php //rain year 
if($rain["year_total"]>=1000){ echo "<topblue1>".round($rain["year_total"],0)  ;}
else  echo "<topblue1>".$rain["year_total"]  ;echo "<smallwindunit>".$rain["units"];
?>
</div></smallwindunit>
<div class="maxword"><?php echo date('Y')?></div></div>
<div class="maxtimedate">Total</div> 

