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

$mpstatOutput = shell_exec("mpstat -P ALL");
$lines = preg_split("/\r\n|\r|\n/", $mpstatOutput);
$data = [];
$headersToKeep = ['usr', 'nice', 'sys', 'iowait', 'irq', 'soft', 'steal', 'guest', 'gnice', 'idle'];

$timestampLine = $lines[2];
$timestampParts = preg_split('/\s+/', $timestampLine, -1, PREG_SPLIT_NO_EMPTY);
$timestamp = $timestampParts[0]; // Assuming the timestamp is in the second item

for ($i = 4; $i <= 7; $i++) {
    $stats = preg_split('/\s+/', $lines[$i], -1, PREG_SPLIT_NO_EMPTY);
    $cpu = intval($stats[1]);
    $dataStats = array_slice($stats, 2, 10);
    if (count($headersToKeep) === count($dataStats)) {
        $data[$cpu] = array_combine($headersToKeep, $dataStats);
    } else {
        echo "Error: Mismatch in header count and data count for CPU $cpu\n";
        echo "Headers: " . implode(', ', $headersToKeep) . "\n";
        echo "Data: " . implode(', ', $dataStats) . "\n";
    }
}

$meminfoOutput = shell_exec("cat /proc/meminfo");
$memLines = preg_split("/\r\n|\r|\n/", $meminfoOutput);

$memoryData = [];
$memoryKeys = ['MemTotal', 'MemFree', 'Buffers', 'Cached', 'SReclaimable', 'Shmem', 'SwapTotal', 'SwapFree'];

foreach ($memLines as $line) {
    foreach ($memoryKeys as $key) {
        if (strpos($line, $key) === 0) {
            $parts = preg_split('/\s+/', $line, -1, PREG_SPLIT_NO_EMPTY);
            $memoryData[$key] = $parts[1];
        }
    }
}

$osSystemOutput = shell_exec("cat /etc/*release");
$osSystemLines = preg_split("/\r\n|\r|\n/", $osSystemOutput);

$osSystem = "";
foreach ($osSystemLines as $line) {
    if (strpos($line, 'PRETTY_NAME=') === 0) {
        $osSystem = trim(str_replace('PRETTY_NAME=', '', $line), '"');
        break;
    }
}

$system_locale = Locale::getDefault();
$uptimeOutput = shell_exec("uptime -p");
$reboot_duration = substr($uptimeOutput, 3);

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
$reboot_timestamp = $current_timestamp - ($total_minutes * 60);
$reboot_date_time = date("Y-m-d H:i:s", $reboot_timestamp);

$formatter = new IntlDateFormatter($system_locale, IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
$localized_reboot_date_time = $formatter->format($reboot_timestamp);



$uptimeParts = explode(" ", trim($uptimeOutput));
$sysUptime = implode(" ", array_slice($uptimeParts, 1));

$data['timestamp'] = $timestamp;
$data['memory'] = $memoryData;
$data['sysUptime'] = $sysUptime;
$data['osSystem'] = $osSystem;
$data['rebootTime'] = $reboot_date_time;

header('Content-Type: application/json');
echo json_encode($data);
?>
