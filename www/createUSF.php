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

$data = file_get_contents('php://input');
$configArray = json_decode($data, true);
$phpContent = "<?php\n";

foreach ($configArray as $item) {
    $key = $item['key'];
    $value = $item['value'];
    if (is_string($value)) {
        $value = '"' . addslashes($value) . '"';
    }
    $phpContent .= "\$$key = $value;\n";
}

try {
    if (file_put_contents('userSettings.php', $phpContent) === false) {
        echo json_encode(["status" => "fail", "message" => "Error writing file"]);
        exit;
    }
    $ownerInfo = posix_getpwuid(fileowner('userSettings.php'));
    $groupInfo = posix_getgrgid(filegroup('userSettings.php'));
    $owner = $ownerInfo['name'];
    $group = $groupInfo['name'];
    $permissions = substr(sprintf('%o', fileperms('userSettings.php')), -4);

    echo json_encode([
        "status" => "success",
        "owner" => $owner,
        "group" => $group,
        "permissions" => $permissions
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "fail", "message" => "Exception occurred"]);
}
