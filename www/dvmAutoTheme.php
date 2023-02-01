<?php //original divumwx script original css/svg/php by divumwx 2015-2019 // 
 include_once('dvmCombinedData.php');include_once('userSettings.php');include_once('common.php');include_once('webserver_ip_address.php');date_default_timezone_set($TZ);
?>
<?php header('Content-type: text/html; charset=utf-8');error_reporting(0);

if ($autotheme == "auto_1") {$theme1 = $theme_switch; $theme = $theme1;}
else if ($autotheme == "auto_2" && $theme_switch = "dark") {$theme = "dark"; $theme1 = "light";}
else if ($autotheme == "auto_2" && $theme_switch = "light") {$theme = "light"; $theme1 = "dark";}
else if ($autotheme == "light_2") {$theme = "light"; $theme1 = "dark";}
else if ($autotheme == "dark_2") {$theme = "dark"; $theme1 = "light";}
else if ($autotheme == "dark_1") {$theme = "dark"; $theme1 = "dark";}
else if ($autotheme == "light_1") {$theme = "light"; $theme1 = "light";}
?>
