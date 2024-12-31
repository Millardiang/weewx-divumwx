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
$chkReqStatusFile = __DIR__ . '/chkReqstatus.json';
if (file_exists($chkReqStatusFile)) {
    unlink($chkReqStatusFile);
}

function findWeeWXBinary($userHome) {
    $binaryPath = $userHome . '/weewx-venv/bin/weewxd';
    if (is_file($binaryPath) && is_executable($binaryPath)) {
        return $binaryPath;
    }
    return false;
}

function findUserHomeWithWeeWX() {
    $userDirs = glob('/home/*', GLOB_ONLYDIR);
    foreach ($userDirs as $userDir) {
        $binary = findWeeWXBinary($userDir);
        if ($binary) {
            return $userDir;
        }
    }
    return false;
}

function checkWeeWXVersion($userHome) {
    $binary = findWeeWXBinary($userHome);
    if (!$binary) {
        return ['status' => false, 'version' => 'Not found'];
    }
    $output = shell_exec("$binary --version");
    $output = trim($output);
    return ['status' => version_compare($output, '5.0.0', '>='), 'version' => $output];
}

function checkPHPVersion() {
    $version = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;
    return ['status' => version_compare($version, '8.3', '>='), 'version' => $version];
}

function checkPHPModules($modules) {
    $result = [];
    foreach ($modules as $module) {
        $result[$module] = extension_loaded($module) ? 'Loaded' : 'Not loaded';
    }
    return $result;
}

function getOwnerName($ownerId) {
    if (function_exists('posix_getpwuid')) {
        $owner = posix_getpwuid($ownerId);
        return $owner['name'];
    }
    return $ownerId;
}

function getGroupName($groupId) {
    if (function_exists('posix_getgrgid')) {
        $group = posix_getgrgid($groupId);
        return $group['name'];
    }
    return $groupId;
}

function checkDirectory($path, $expectedPerms, $expectedOwner, $expectedGroup, &$debug) {
    if (strpos(basename($path), '.') === 0) {
        return ['status' => true, 'perms' => null, 'owner' => null, 'group' => null];
    }

    if (!is_dir($path)) {
        $debug[$path] = "Directory does not exist.";
        return ['status' => false, 'perms' => null, 'owner' => null, 'group' => null];
    }
    $perms = substr(sprintf('%o', fileperms($path)), -4);
    $owner = getOwnerName(fileowner($path));
    $group = getGroupName(filegroup($path));
    return [
        'status' => $perms == $expectedPerms && $owner == $expectedOwner && $group == $expectedGroup,
        'perms' => $perms,
        'owner' => $owner,
        'group' => $group
    ];
}

function checkFile($path, $expectedPerms, $expectedOwner, $expectedGroup, &$debug) {
    if (strpos(basename($path), '.') === 0) {
        return ['status' => true, 'perms' => null, 'owner' => null, 'group' => null];
    }

    if (!is_file($path)) {
        $debug[$path] = "File does not exist.";
        return ['status' => false, 'perms' => null, 'owner' => null, 'group' => null];
    }
    $perms = substr(sprintf('%o', fileperms($path)), -4);
    $owner = getOwnerName(fileowner($path));
    $group = getGroupName(filegroup($path));
    return [
        'status' => $perms == $expectedPerms && $owner == $expectedOwner && $group == $expectedGroup,
        'perms' => $perms,
        'owner' => $owner,
        'group' => $group
    ];
}

function getScriptOwnerAndGroup() {
    $scriptPath = __DIR__;
    $owner = getOwnerName(fileowner($scriptPath));
    $group = getGroupName(filegroup($scriptPath));
    return [$owner, $group];
}

function getCurrentUser() {
    if (function_exists('posix_getpwuid')) {
        $userInfo = posix_getpwuid(posix_geteuid());
        return $userInfo['name'];
    }
    return get_current_user();
}

list($scriptOwner, $scriptGroup) = getScriptOwnerAndGroup();
$currentUser = getCurrentUser();

if ($scriptOwner === 'www-data' && $scriptGroup === 'www-data') {
    $userHome = findUserHomeWithWeeWX();
    if (!$userHome) {
        die(json_encode(['error' => 'WeeWX binary not found under any user home directory']));
    }
} else {
    $ownerInfo = posix_getpwnam($scriptOwner);
    $userHome = $ownerInfo['dir'];
}

$directories = [
    ['path' => './admin', 'perms' => '0775', 'owner' => $scriptOwner, 'group' => $scriptGroup],
    ['path' => './admin/archives', 'perms' => '0775', 'owner' => $scriptOwner, 'group' => $scriptGroup],
    ['path' => './admin/assets', 'perms' => '0775', 'owner' => $scriptOwner, 'group' => $scriptGroup],
    ['path' => './admin/db', 'perms' => '0775', 'owner' => $scriptOwner, 'group' => $scriptGroup],
];

$results = [
    'weewx' => checkWeeWXVersion($userHome),
    'php_version' => checkPHPVersion(),
    'php_modules' => checkPHPModules(['intl', 'json', 'mysqli', 'pdo_sqlite', 'random', 'session', 'sqlite3']),
    'directories' => [],
    'running_user' => $currentUser,
];

$debug = [];
$errorDetected = false;

foreach ($directories as $dir) {
    $result = checkDirectory($dir['path'], $dir['perms'], $dir['owner'], $dir['group'], $debug);
    $results['directories'][$dir['path']] = $result;
    if (!$result['status']) {
        $errorDetected = true;
    }
}

$dbFileCheck = checkFile('./admin/db/dvmAdmin.db3', '0666', $scriptOwner, $scriptGroup, $debug);
$results['db_file'] = $dbFileCheck;
if (!$dbFileCheck['status']) {
    $errorDetected = true;
} else {
    $dbConnectionCheck = checkDatabaseConnection('./admin/db/dvmAdmin.db3');
    $results['db_connection'] = $dbConnectionCheck;
    if (!$dbConnectionCheck['status']) {
        $errorDetected = true;
    }
}

header('Content-Type: application/json');
echo json_encode($results);

function checkDatabaseConnection($dbFile) {
    $result = ['status' => false, 'message' => ''];

    try {
        $db = new SQLite3($dbFile);
        $result['status'] = true;
        $result['message'] = 'Database is accessible and permissions are correct.';
    } catch (Exception $e) {
        $result['message'] = 'Failed to connect to the database: ' . $e->getMessage();
    }

    return $result;
}

file_put_contents(__DIR__ . '/chkReqstatus.json', json_encode($results, JSON_PRETTY_PRINT));

if ($errorDetected) {
    file_put_contents(__DIR__ . '/chkReqdebug.json', json_encode($debug, JSON_PRETTY_PRINT));
}
