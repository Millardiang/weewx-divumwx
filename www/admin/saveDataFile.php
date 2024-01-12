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

$changesData = file_get_contents('php://input');
$changes = json_decode($changesData, true);
$parentDirectory = dirname(getcwd());
$originalFile = $parentDirectory . DIRECTORY_SEPARATOR . 'userSettings.php';
$backupFile = $originalFile . '.' . date('YdmHis');
$new_permissions = 0766;

if (!file_exists($originalFile)) {
    $response = "Unable to find original file.";
    echo json_encode(['status' => $response]);
    exit;
}
if (!rename($originalFile, $backupFile)) {
    $response = "Unable to create backup file.";
    echo json_encode(['status' => $response]);
    exit;
}

$originalContent = file_get_contents($backupFile);
if ($originalContent === false) {
    $response = "Unable to get original File contents for comparison.";
    echo json_encode(['status' => $response]);
    exit;
}

foreach ($changes['updatedSettings'] as $variableName => $variableValue) {
    $search = '/\$' . preg_quote($variableName, '/') . ' = "([^"]*)";/';
    $replace = '$' . $variableName . ' = "' . $variableValue . '";';
    $originalContent = str_replace($search, $replace, $originalContent);
}
if (file_put_contents($originalFile, $originalContent)) {
    $response = "Server changes saved successfully";
} else {
    $response = "Server failed to save changes";
}
if (!chmod($originalFile, $new_permissions)){
    $response = "Unable Unable to change permissions on new settings file";
    echo json_encode(['status' => $response]);
    exit;
}
echo json_encode(['status' => $response]);
?>
