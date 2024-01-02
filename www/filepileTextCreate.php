<?php
/*
Include a stanza like this in your weewx.conf file: -

###############################################################################

# Options for extension 'filepile'
[FilePile]
    # Where to find the incoming new data:
    filename = /var/www/html/divumwx/serverdata/filepileTextData.txt
    # What unit system they will be in.
    # Choices are 'US', 'METRIC', or 'METRICWX'
    unit_system = US
    # Map from incoming names, to WeeWX names.
    [[label_map]]
        cloudCover = cloudcover
################################################################################

You must also create a dummy file in your serverdata folder: -

filepileTextData.txt with permissions set to 0777 to make it writeable and executable

*/

include_once ('dvmUpdater.php');
$json_cloud = file_get_contents("/var/www/html/divumwx/jsondata/awc.txt");
$cloud = json_decode($json_cloud, true);
$cloudcover = $cloud['response'][0]['periods'][0]['sky'];

$myfile = fopen("/var/www/html/divumwx/serverdata/filepileTextData.txt", "w") or die("Unable to open file!");

$txt = "cloudCover = $cloudcover";
fwrite($myfile, $txt);
fclose($myfile);

?>
