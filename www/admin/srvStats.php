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

function determineTopCommand($osType) {
    if (strpos($osType, 'LINUX') !== false) {
        return 'top -bn1 | head -n 17';
    } elseif (strpos($osType, 'DARWIN') !== false) {
        return 'top -l 1 | head -n 10';
    } else {
        echo json_encode(['error' => 'Unsupported OS: ' . $osType]);
        exit;
    }
}

function executeTopCommand($topCommand) {
    $output = shell_exec($topCommand);
    if ($output === null || $output === '') {
        echo json_encode(['error' => 'Command execution failed or returned no output']);
        exit;
    }
    return $output;
}

$osType = strtoupper(php_uname('s'));
$topCommand = determineTopCommand($osType);
$output = executeTopCommand($topCommand);
$stats = parseTopOutput($output, $osType);
echo json_encode($stats);

function parseTopOutput($output, $osType) {
    $stats = [];
    if (strpos(strtoupper($osType), 'LINUX') !== false) {
        if (!preg_match('/Tasks:/', $output)) {
            return ['error' => 'Unexpected output format for Linux'];
        }
        preg_match('/Tasks:\s+(\d+) total,\s+(\d+) running,\s+(\d+) sleeping,\s+(\d+) stopped,\s+(\d+) zombie/', $output, $tasksMatches);
        $stats['tasks'] = [
            'total' => $tasksMatches[1],
            'running' => $tasksMatches[2],
            'sleeping' => $tasksMatches[3],
            'stopped' => $tasksMatches[4],
            'zombie' => $tasksMatches[5]
        ];
        preg_match('/%Cpu\(s\):.*(\d+\.\d+) us,.*(\d+\.\d+) sy,.*(\d+\.\d+) ni,.*(\d+\.\d+) id/', $output, $cpuMatches);
        $stats['cpu'] = [
            'us' => $cpuMatches[1],
            'sy' => $cpuMatches[2],
            'id' => $cpuMatches[4]  // Notice the index is 4 here, corresponding to 'id' in the regex capture groups
        ];
        preg_match_all('/^\s*(\d+)\s+(\S+)\s+\d+\s+-?\d+\s+\d+\s+\d+\s+\d+\s+\S+\s+(\d+\.\d+)\s+(\d+\.\d+)\s+\S+\s+(.+)$/m', $output, $processMatches, PREG_SET_ORDER);
        $processes = [];
        foreach ($processMatches as $match) {
            $processes[] = [
                'PID' => $match[1],
                'USER' => $match[2],
                '%CPU' => $match[3],
                '%MEM' => $match[4],
                'COMMAND' => trim($match[5])
            ];
        }
        $stats['processes'] = $processes;
    } elseif (strpos(strtoupper($osType), 'DARWIN') !== false) {
        if (!preg_match('/Processes:/', $output)) {
            return ['error' => 'Unexpected output format for Darwin'];
        }
        if (preg_match('/Processes: (\d+) total, (\d+) running, (\d+) sleeping, (\d+) threads/', $output, $processMatches)) {
            $stats['processes'] = [
                'total' => $processMatches[1],
                'running' => $processMatches[2],
                'sleeping' => $processMatches[3],
                'threads' => $processMatches[4]
            ];
        }
        if (preg_match('/CPU usage: ([\d\.]+)% user, ([\d\.]+)% sys, ([\d\.]+)% idle/', $output, $cpuMatches)) {
            $stats['cpu_usage'] = [
                'user' => $cpuMatches[1],
                'sys' => $cpuMatches[2],
                'idle' => $cpuMatches[3]
            ];
        }
        $processListPattern = '/\n\s*(\d+)\s+(\S+).+?([\d\.]+[KMG]?)\s+\S+\s+\S+\s+\d+\s+\d+\s+\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+\d+\s+\S+\s+\S+\s+\S+\s+\S+\s+(\S+)\s+/';
        if (preg_match_all($processListPattern, $output, $processMatches, PREG_SET_ORDER)) {
            foreach ($processMatches as $match) {
                $stats['processes_list'][] = [
                    'PID' => $match[1],
                    'COMMAND' => $match[2],
                    '%CPU' => $match[3],
                    'MEM' => $match[4],
                    'USER' => $match[5]
               ];
            }
        }
    }
    if (empty($stats)) {
        return ['error' => 'Failed to parse output'];
    }
    return $stats;
}