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

function find_variable($strings, $str, $rstr){
    for ($x = 0; $x < count($strings); $x++) {
       $parts = explode("=", $strings[$x]);
       $parts[0] = trim($parts[0]);
       $s_len = strlen($str);
       if (substr($parts[0], 0, $s_len) == $str and $s_len == strlen($parts[0])){
           if (strlen($rstr) == 0)
               return str_replace("'","",str_replace('"','',str_replace(";","",trim($parts[1]))));
           else {
               $strings[$x] = $parts[0].' = '.'"'.$rstr.'";'."\n";
               return $strings;
           }
       }
    }
    return "";
}
$pos = '$'.$_GET['pos'];
$filetext = file('userSettings.php');
$positions = find_variable($filetext, $pos.'s', '');
$positiontitles = find_variable($filetext, $pos.'titles', '');
$currenttitle = find_variable($filetext, $pos.'title', '');
if (strlen($positions) > 0 and strlen($positiontitles) > 0) {
    $urls = explode("!", $positions);
    $titles = explode("!", $positiontitles);
    for ($x = 0; $x < count($titles); $x++) {
        if ($currenttitle == $titles[$x]) {
            $x = ($x + 1) % count($titles); 
            $strings = find_variable(find_variable($filetext, $pos, $urls[$x]), $pos.'title', $titles[$x]);
            if (count($strings) > 0)
                file_put_contents('userSettings.php',$strings,LOCK_EX);
            sleep(3);
            break;
        }
    }
}
header( "refresh:0.1; url=index.php"); 
?>
