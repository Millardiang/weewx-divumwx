<?php
###############################################################################################
#       _______   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___        #
#      |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |      #
#      (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /       #
#      |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/        #
#      (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \        #
#      |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \       #
#      (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|      #
#                                                                                             #
#      Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
#       Distributed under terms of the GPLv3.  See the file LICENSE.txt for your rights.      #
#     Issues for weewx-divumwx skin template are only addressed via the issues register at    #
#                     https://github.com/Millardiang/weewx-divumwx/issues                     #
###############################################################################################

header('Content-Type: application/json');

$cacheFile = 'wewxRemoteVer.txt';
$url = 'https://weewx.com/downloads/';
if (file_exists($cacheFile) && time() - filemtime($cacheFile) < 86400) {
    $weewx_remote = file_get_contents($cacheFile);
} else {
    $html = file_get_contents($url);
    $matches = [];
    if (preg_match('/weewx-(\d+\.\d+\.\d+)\.tgz/', $html, $matches)) {
        $weewx_remote = $matches[1];
        file_put_contents($cacheFile, $weewx_remote);
    } else {
        $weewx_remote = "Unknown";
    }
}

$localFilePath = 'dvmRemoteVer.txt';
$remoteFileUrl = 'http://www.divumwx.com/latestVer.txt';

if (file_exists($localFilePath) && (time() - filemtime($localFilePath)) < 24 * 3600) {
    $dvmRemoteVer = file_get_contents($localFilePath);
} else {
    $dvmRemoteVer = file_get_contents($remoteFileUrl);
    file_put_contents($localFilePath, $dvmRemoteVer);
}

$data = [];
$days = 0;
$hours = 0;
$minutes = 0;
$uptimeOutput = shell_exec("uptime -p");
$uptime_parts = explode(", ", $reboot_duration);
$days = 0;
$hours = 0;
$minutes = 0;

foreach ($uptime_parts as $part) {
    if (strpos($part, "day") !== false) {
        $days = (int)substr($part, 0, -4);
    } elseif (strpos($part, "hour") !== false) {
        $hours = (int)substr($part, 0, -5);
    } elseif (strpos($part, "minute") !== false) {
        $minutes = (int)substr($part, 0, -7);
    }
}

$total_minutes = ($days * 24 * 60) + ($hours * 60) + $minutes;
$current_timestamp = time();

$uptimeParts = explode(" ", trim(preg_replace('/\s+/', ' ', $uptimeOutput)));
$sysUptime = implode(" ", array_slice($uptimeParts, 1));


$osSystemOutput = shell_exec("cat /etc/*release");
$osSystemLines = explode("\n", str_replace(["\r\n", "\r"], "\n", $osSystemOutput));
$osSystem = "";
foreach ($osSystemLines as $line) {
    if (strpos($line, 'PRETTY_NAME=') === 0) {
        $osSystem = trim(str_replace('PRETTY_NAME=', '', $line), '"');
        break;
    }
}


$php_version = phpversion();
if (preg_match("/(\d+\.\d+\.\d+)/", $php_version, $matches)) {
    $php_version = $matches[1];
}


$python_version = shell_exec('python --version 2>&1');

$webserver_name = "Unknown";
$webserver_version = "Unknown";
$apache_path = shell_exec('which apache2');
$nginx_path = shell_exec('which nginx');

if ($apache_path) {
    $webserver_name = "Apache";
    $webserver_version = shell_exec(trim($apache_path) . ' -v | grep "Server version"');
    $webserver_version = preg_replace('/.*Apache\/([^\s]+)\s.*/', '$1', $webserver_version);
} elseif ($nginx_path) {
    $webserver_name = "Nginx";
    $webserver_version = shell_exec(trim($nginx_path) . ' -v 2>&1');
    $webserver_version = preg_replace('/.*nginx\/([^\s]+).*/', '$1', $webserver_version);
}


$weewx_version = "Unknown";
$weectlPath = trim(shell_exec("find /home -name weectl -type f 2>/dev/null"));
if (!empty($weectlPath)) {
    $output = shell_exec($weectlPath . ' -v');
    $parts = explode(' ', $output);
    if (isset($parts[1])) {
        $weewx_version = trim($parts[1]);
    } else {
        $weewx_version = "Unknown";
    }
} else {
    $weewx_version = "Unknown Error";
}



$data = array(
    'osSystem' => trim($osSystem),
    'sysUptime' => trim($sysUptime),
    'webserver_name' => $webserver_name,
    'webserver_version' => trim($webserver_version),
    'php_version' => trim($php_version),
    'python_version' => trim($python_version),
    'weewx_version' => trim($weewx_version),
    'weewxRemote' => trim($weewx_remote),
    'divumRemote' => trim($dvmRemoteVer)
);

echo json_encode($data);