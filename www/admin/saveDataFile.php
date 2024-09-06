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

if (PHP_OS === 'Linux') {
    $timezone = shell_exec('cat /etc/timezone');
    date_default_timezone_set($timezone);
} elseif (PHP_OS === 'Darwin') {
    $timezone = shell_exec('systemsetup -gettimezone');
    $timezone = str_replace('Time Zone: ', '', $timezone);
    date_default_timezone_set($timezone);
}

$changedData = file_get_contents('php://input');
$changes = json_decode($changedData, true);
$parentDirectory = dirname(getcwd());
$originalFile = './userSettings.php';
$backupFile =  './archives/userSettings.php' . '.' . date('Y-d-m-His');
$n$new_permissions = 0766;
$timezone = '';

if (!file_exists($originalFile)) {
    $status = 'fail';
    $message = "Unable to find original userSettings.php file, aborting.";
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}
if (!rename($originalFile, $backupFile)) {
    $status = 'fail';
    $message = "Unable to create backup userSettings.php file, aborting.";
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

$originalContent = file_get_contents($backupFile);
if ($originalContent === false) {
    $status = 'fail';
    $message = "Unable to get original userSettings.php ile contents for comparison, aborting.";
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

foreach ($changes['updatedSettings'] as $variableName => $variableValue) {
    $searchPattern = '/\$' . preg_quote($variableName, '/') . '\s*=\s*(?:"([^"]*)"|([^;]*));/';
    preg_match($searchPattern, $originalContent, $matches);
    $isStringValue = isset($matches[1]);
    $formattedValue = $isStringValue ? '"' . $variableValue . '"' : $variableValue;
    $replace = '$' . $variableName . ' = ' . $formattedValue . ';';
    $originalContent = preg_replace($searchPattern, $replace, $originalContent);
}
if (file_put_contents($originalFile, $originalContent)) {
    $status = 'pass';
    $message = "userSettings.php changes have been saved successfully and archive file has been created.";
} else {
    $status = 'fail';
    $response = "Unable to write new userSettings.php, update aborted.";
}
if (!chmod($originalFile, $new_permissions)){
    $status = 'fail';
    $message = "Unable to change permissions on new settings file, aborting.";
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}
echo json_encode(['status' => $status, 'message' => $message]);