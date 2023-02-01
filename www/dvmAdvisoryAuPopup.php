<?php
//###################################################################################################################
//	weewx-divumwx Template maintained by Ian Millard (Steepleian)                                 				#
//	                                                                                                				#
//  Contains original code by Ian Millard and collaborators															#
//  © claydonsweather.org.uk original CSS/SVG/PHP 2020-2021                                                         #
// 	                                                                                                				#
// 	Issues for weewx-divumwx template should be addressed to https://github.com/steepleian/weewx-divumwx/issues #
// 	                                                                                                				#
//###################################################################################################################

include "dvmCombinedData.php";
error_reporting(0);

?>

<body>
  <?php
  if ($theme === "dark") {
      $text1 = "silver";
      $url = "cyan";
  } elseif ($theme === "light") {
      $text1 = "black";
      $url = "green";
  }
  $forecastime = filemtime("jsondata/au.txt");
  ?> 
<?php
$xml = simplexml_load_file("jsondata/au.txt") or die("Error: Cannot create object");
$jsonData = json_encode($xml, JSON_PRETTY_PRINT);
$parsed_json = json_decode($jsonData, true);
$title1 = $parsed_json["channel"]["title"];
?>
<div class="divumwxdarkbrowser" style="color:<?php echo $text1; ?>;" url="<?php echo $title1; ?>"></div>
<main class="grid3"  style="font-size:13px";>  
<?php for ($i = 0; $i < count($parsed_json["channel"]["item"]); $i++) {

    $title2[$i] = $parsed_json["channel"]["item"][$i]["title"];
    $link[$i] = $parsed_json["channel"]["item"][$i]["link"];
    $pubDate[$i] = $parsed_json["channel"]["item"][$i]["pubDate"];
    $guid[$i] = $parsed_json["channel"]["item"][$i]["guid"];
    ?>

<articlegraph3>  
  <div class="lotemp" style="color:<?php echo $text1; ?>;";>
                     <?php echo $title2[$i]; ?> 
<a href="<?php echo $link[$i]; ?>" title="<?php echo $link[
    $i
]; ?>" target="_blank" style="color:<?php echo $url; ?>;"> <?php echo $link[
    $i
]; ?> </a>
  </div>
  
   
  </articlegraph3>   
 <?php
} ?>   
        
    </div>
 
<div class="divumwxbrowser-footer">
</div>
  </main>
