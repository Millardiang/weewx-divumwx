<?php
##############################################################################################
#        ________   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___     #
#       |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |    #
#       (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /     #
#       |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/      #
#       (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \      #
#       |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \     #
#       (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|    #
#                                                                                            #
#     Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
#      Distributed under terms of the GPLv3.  See the file LICENSE.txt for your rights.      #
#    Issues for weewx-divumwx skin template are only addressed via the issues register at    #
#                    https://github.com/Millardiang/weewx-divumwx/issues                     #
##############################################################################################

date_default_timezone_set($TZ);

$meteor_default = "No Meteor";
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 1, 3) ,
    "event_title" => "Quadrantids Meteor",
    "event_end" => mktime(23, 59, 59, 1, 4) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 1, 5) ,
    "event_title" => "Quadrantids Meteor",
    "event_end" => mktime(23, 59, 59, 1, 12) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 28, 2019) ,
    "event_title" => "Quadrantids Meteor",
    "event_end" => mktime(23, 59, 59, 1, 2, 2020) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 28, 2020) ,
    "event_title" => "Quadrantids Meteor",
    "event_end" => mktime(23, 59, 59, 1, 2, 2021) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 28, 2021) ,
    "event_title" => "Quadrantids Meteor",
    "event_end" => mktime(23, 59, 59, 1, 2, 2022) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 28, 2022) ,
    "event_title" => "Quadrantids Meteor",
    "event_end" => mktime(23, 59, 59, 1, 2, 2023) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 28, 2023) ,
    "event_title" => "Quadrantids Meteor",
    "event_end" => mktime(23, 59, 59, 1, 2, 2024) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 28, 2024) ,
    "event_title" => "Quadrantids Meteor",
    "event_end" => mktime(23, 59, 59, 1, 2, 2025) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 4, 9) ,
    "event_title" => "Lyrids Meteor",
    "event_end" => mktime(20, 59, 59, 4, 20) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 4, 21) ,
    "event_title" => "Lyrids Meteor",
    "event_end" => mktime(23, 59, 59, 4, 22) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 5, 5) ,
    "event_title" => "ETA Aquarids",
    "event_end" => mktime(23, 59, 59, 5, 6) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 7, 20) ,
    "event_title" => "Delta Aquarids",
    "event_end" => mktime(23, 59, 59, 7, 28) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 7, 29) ,
    "event_title" => "Delta Aquarids",
    "event_end" => mktime(23, 59, 59, 7, 30) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 8, 1) ,
    "event_title" => "Perseids Meteor",
    "event_end" => mktime(23, 59, 59, 8, 10) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 8, 11) ,
    "event_title" => "Perseids Meteor",
    "event_end" => mktime(23, 59, 59, 8, 13) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 8, 14) ,
    "event_title" => "Perseids Meteor",
    "event_end" => mktime(23, 59, 59, 8, 18) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 10, 6) ,
    "event_title" => "Draconids",
    "event_end" => mktime(23, 59, 59, 10, 7) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 10, 20) ,
    "event_title" => "Orionids Meteor",
    "event_end" => mktime(23, 59, 59, 10, 21) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 11, 4) ,
    "event_title" => "South Taurids",
    "event_end" => mktime(23, 59, 59, 11, 5) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 11, 11) ,
    "event_title" => "North Taurids",
    "event_end" => mktime(23, 59, 59, 11, 11) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 11, 13) ,
    "event_title" => "Leonids Meteor",
    "event_end" => mktime(23, 59, 59, 11, 16) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 11, 17) ,
    "event_title" => "Leonids Meteor",
    "event_end" => mktime(23, 59, 59, 11, 18) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 11, 19) ,
    "event_title" => "Leonids Meteor",
    "event_end" => mktime(23, 59, 59, 11, 29) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 11, 30) ,
    "event_title" => "Geminids Meteor",
    "event_end" => mktime(23, 59, 59, 12, 12) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 13) ,
    "event_title" => "Geminids Meteor",
    "event_end" => mktime(23, 59, 59, 12, 14) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 16) ,
    "event_title" => "Ursids Meteor",
    "event_end" => mktime(23, 59, 59, 12, 20) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 21) ,
    "event_title" => "Ursids Meteor",
    "event_end" => mktime(23, 59, 59, 12, 22) ,
);
$meteor_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 23) ,
    "event_title" => "Ursids Meteor",
    "event_end" => mktime(23, 59, 59, 12, 25) ,
);
$meteorNow = time();
$meteorOP = false;
foreach ($meteor_events as $meteor_check)
{
    if ($meteor_check["event_start"] <= $meteorNow && $meteorNow <= $meteor_check["event_end"])
    {
        $meteorOP = true;
        $meteor_default = $meteor_check["event_title"];
    }
};

//end meteor
//
//lunar and solar eclipse /meteor shpwers advisory 2018-2019-2020
$eclipse_default = " <noalert>No Current Weather <spanyellow><ored>Alerts " . $alert . "</ored></spanyellow></noalert>";
//2 jul solar 2019
$eclipse_events[] = array(
    "event_start" => mktime(0, 0, 0, 7, 2, 2019) ,
    "event_title" => "<div style ='margin-top:5px;'>" . $solareclipsesvg . " <alert>Total Solar <spanyellow>Eclipse</spanyellow></alert>  </div>
",
    "event_end" => mktime(23, 59, 59, 7, 2, 2019) ,
);
//16/17 jul solar 2019
$eclipse_events[] = array(
    "event_start" => mktime(0, 0, 0, 7, 16, 2019) ,
    "event_title" => "<div style ='margin-top:5px;'>" . $solareclipsesvg . "  <alert>Partial Lunar <spanyellow>Eclipse</spanyellow></alert>  </div>
",
    "event_end" => mktime(23, 59, 59, 7, 17, 2019) ,
);
//persieds 2019
$eclipse_events[] = array(
    "event_start" => mktime(0, 0, 0, 8, 12, 2019) ,
    "event_title" => "<div style ='margin-top:5px;'>" . $meteorsvg . " <alert>Perseids <spanyellow>Meteor Shower</spanyellow></alert>  </div>
",
    "event_end" => mktime(23, 59, 59, 8, 13, 2019) ,
);
//leonids 2019
$eclipse_events[] = array(
    "event_start" => mktime(0, 0, 0, 11, 17, 2019) ,
    "event_title" => "<div style ='margin-top:5px;'>" . $meteorsvg . " <alert>Leonids <spanyellow>Meteor Shower</spanyellow></alert>  </div>
",
    "event_end" => mktime(23, 59, 59, 11, 18, 2018) ,
);
//geminids 2019
$eclipse_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 13, 2019) ,
    "event_title" => "<div style ='margin-top:5px;'>" . $meteorsvg . " <alert>Geminids <spanyellow>Meteor Shower</spanyellow></alert>  </div>
",
    "event_end" => mktime(23, 59, 59, 12, 14, 2019) ,
);
//5/6 dec solar 2019
$eclipse_events[] = array(
    "event_start" => mktime(0, 0, 0, 12, 26, 2019) ,
    "event_title" => "<div style ='margin-top:5px;'>" . $solareclipsesvg . "  <alert>Annular Solar <spanyellow>Eclipse</spanyellow></alert>  </div>
",
    "event_end" => mktime(23, 59, 59, 12, 26, 2019) ,
);
//Quadrantids 2020
$eclipse_events[] = array(
    "event_start" => mktime(0, 0, 0, 1, 3, 2020) ,
    "event_title" => "<div style ='margin-top:5px;'>" . $meteorsvg . "  <alert>Quadrantids <spanyellow>Meteor Shower</spanyellow></alert>  </div>
",
    "event_end" => mktime(23, 59, 59, 1, 4, 2020) ,
);
//output eclipse events
$eclipseNow = time();
$eclipseOP = false;
foreach ($eclipse_events as $eclipse_check)
{
    if ($eclipse_check["event_start"] <= $eclipseNow && $eclipseNow <= $eclipse_check["event_end"])
    {
        $eclipseOP = true;
        $eclipse_default = $eclipse_check["event_title"];
    }
};

$str = file_get_contents('jsondata/ki.txt');
$json = array_reverse(json_decode($str, false));
$kp = $json[1][1];
$file = $_SERVER["SCRIPT_NAME"];
$break = Explode('/', $file);
$moddvmfile = $break[count($break) - 1];

?>