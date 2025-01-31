<?php
$json_string = file_get_contents('solar.json');
$parsed_json = json_decode($json_string,true);
$test = $parsed_json[0]["solarGenerationplot"]["series"]["wattsDay"][1];
echo $test2 = json_encode($test);
?>