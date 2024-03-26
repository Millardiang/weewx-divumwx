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

function determineMemoryCommand($osType) {
    if (strpos($osType, 'LINUX') !== false) {
        return 'free -m';
    } elseif (strpos($osType, 'DARWIN') !== false) {
        return 'vm_stat';
    }
}

function executeCommand($command) {
    $output = shell_exec($command);
    if ($output === null || $output === '') {
        echo json_encode(['error' => 'Command execution failed or returned no output']);
        exit;
    }
    return $output;
}

function parseMemoryOutput($output, $osType) {
    $memoryStats = [];
    if (strpos($osType, 'LINUX') !== false) {
        preg_match('/Mem:\s+(\d+)\s+(\d+)\s+(\d+)/', $output, $matches);
        $memoryStats = [
            'total' => $matches[1],
            'used' => $matches[2],
            'free' => $matches[3]
        ];
    } elseif (strpos($osType, 'DARWIN') !== false) {
        preg_match('/Pages free:\s+(\d+)/', $output, $freeMatches);
        preg_match('/Pages active:\s+(\d+)/', $output, $activeMatches);
        preg_match('/Pages inactive:\s+(\d+)/', $output, $inactiveMatches);
        preg_match('/Pages speculative:\s+(\d+)/', $output, $speculativeMatches);

        $totalMemory = intval(shell_exec("sysctl -n hw.memsize")) / (1024 * 1024); // In MB
        $pageSize = intval(shell_exec("vm_stat | awk '/page size of/{print $8}'")); // In bytes

        $freeMemory = $freeMatches[1] * $pageSize / (1024 * 1024); // Convert to MB
        $usedMemory = ($activeMatches[1] + $inactiveMatches[1] + $speculativeMatches[1]) * $pageSize / (1024 * 1024); // Convert to MB

        $memoryStats = [
            'total' => round($totalMemory),
            'used' => round($usedMemory),
            'free' => round($freeMemory)
        ];
    }
    return $memoryStats;
}

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
        preg_match('/%Cpu\(s\):\s+(\d+\.\d+) us,\s+(\d+\.\d+) sy,.*?(\d+\.\d+) id,/', $output, $cpuMatches);
        $stats['cpu'] = [
            'us' => $cpuMatches[1],
            'sy' => $cpuMatches[2],
            'id' => $cpuMatches[3]
        ];
        preg_match_all('/^\s*(\d+)\s+(\S+)\s+\d+\s+-?\d+\s+\d+\s+\d+\s+\d+\s+\S+\s+(\d+\.\d+)\s+(\d+\.\d+)\s+\S+\s+(.+)$/m', $output, $processMatches, PREG_SET_ORDER);
        $processes = [];
        $index = 0; // Initialize process index
        foreach ($processMatches as $match) {
            $processes[] = [
                'index' => $index++, // Assign and increment the process index
                'PID' => $match[1],
                'USER' => $match[2],
                'CPU' => $match[3],
                'MEM' => $match[4],
                'COMMAND' => trim($match[5])
            ];
        }
        $stats['processes'] = $processes;
    } elseif (strpos(strtoupper($osType), 'DARWIN') !== false) {
        if (!preg_match('/Processes:/', $output)) {
            return ['error' => 'Unexpected output format for Darwin'];
        }
        preg_match('/Processes: (\d+) total, (\d+) running, (\d+) sleeping, (\d+) threads/', $output, $processMatches);
        $stats['processes'] = [
            'total' => $processMatches[1],
            'running' => $processMatches[2],
            'sleeping' => $processMatches[3],
            'threads' => $processMatches[4]
        ];
        preg_match('/CPU usage: ([\d\.]+)% user, ([\d\.]+)% sys, ([\d\.]+)% idle/', $output, $cpuMatches);
        $stats['cpu_usage'] = [
            'user' => $cpuMatches[1],
            'sys' => $cpuMatches[2],
            'idle' => $cpuMatches[3]
        ];
    }
    return $stats;
}

$osType = strtoupper(php_uname('s'));
$topCommand = determineTopCommand($osType);
$memoryCommand = determineMemoryCommand($osType);
$topOutput = executeCommand($topCommand);
$memoryOutput = executeCommand($memoryCommand);
$topStats = parseTopOutput($topOutput, $osType);
$memoryStats = parseMemoryOutput($memoryOutput, $osType);
$stats = array_merge($topStats, ['memory' => $memoryStats]);
echo json_encode($stats);