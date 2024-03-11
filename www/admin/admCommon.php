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

$loggingEnabled = true;
$logDebugEnabled = false;

function executeSqlFile($pdo, $filePath) {
    if (!file_exists($filePath)) {
        echo "Error: File '$filePath' does not exist.\n";
        return;
    }
    $sqlCommands = file_get_contents($filePath);
    if ($sqlCommands === false) {
        echo "Error: Unable to read file '$filePath'.\n";
        return;
    }
    $commands = explode(';', $sqlCommands);
    foreach ($commands as $command) {
        $trimmedCommand = trim($command);
        if ($trimmedCommand) {
            try {
                $pdo->exec($trimmedCommand);
            } catch (PDOException $e) {
                echo "Error executing SQL command: " . $e->getMessage() . "\n";
                return;
            }
        }
    }
}
function displaySidebar($activePage) {
    ?>
    </div>
        <div id="sidebar" class="app-sidebar">
            <div class="app-sidebar-content" data-scrollbar="true" data-height="100%">
                <div class="menu" id="home">
                    <div class="menu-header">Navigation</div>
                    <div class="menu-item <?= ($activePage == 'home') ? 'active' : '' ?>" id="home">
                        <a href="./dvmDashboard.php" class="menu-link">
                            <span class="menu-icon"><i class="bi bi-house-door"></i></span>
                            <span class="menu-text">Home</span>
                        </a>
                    </div>
                    <div class="menu-item <?= ($activePage == 'settings') ? 'active' : '' ?>" id="settings">
                        <a href="./dvmSettings.php" class="menu-link">
                            <span class="menu-icon"><i class="bi bi-gear"></i></span>
                            <span class="menu-text">Settings</span>
                        </a>
                    </div>
                    <div class="menu-item <?= ($activePage == 'modinfo') ? 'active' : '' ?>" id="modinfo">
                        <a href="./dvmModFiles.php" class="menu-link">
                            <span class="menu-icon"><i class="bi bi-list"></i></span>
                            <span class="menu-text">Module Info</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a href="../index.php" class="menu-link">
                            <span class="menu-icon"><i class="bi bi-rocket-takeoff"></i></span>
                            <span class="menu-text">Return to Website</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a href="./dvmDashboard.php?logout=true" class="menu-link">
                            <span class="menu-icon"><i class="bi bi-box-arrow-right"></i></span>
                            <span class="menu-text">Logout of the Dashboard</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php
}

function ldusrSettings($usrSettingsFile) {
    include $usrSettingsFile;
    $allVars = get_defined_vars();
    unset($allVars['usrSettingsFile']);
    return $allVars;
}
function createDropdown($name, $options, $selectedOption = null, $currentPosition = null) {
    $dropdown = '<select name="new' . $name . '" class="newElement form-select form-select-sm" mb-3 id="new' . $name . '">';
    foreach ($options as $value => $text) {
        $selected = ($selectedOption !== null && $selectedOption == $value) ? 'selected' : '';
        if ($currentPosition !== null && $currentPosition == $value) {
            $selected = 'selected';
        }
        $dropdown .= '<option value="' . $value . '" ' . $selected . '>' . $text . '</option>';
    }
    $dropdown .= '</select>';
    return $dropdown;
}
function setLocalMachineTimezone() {
    $cacheFile = './tzInfo.cache';
    if (file_exists($cacheFile) && ($timezone = file_get_contents($cacheFile))) {
        date_default_timezone_set($timezone);
        return;
    }

    $timezone = '';
    if (PHP_OS === 'Linux') {
        $timezone = system('cat /etc/timezone');
    } elseif (PHP_OS === 'Darwin') {
        $timezone = system('systemsetup -gettimezone');
        $timezone = str_replace('Time Zone: ', '', $timezone);
    }

    if ($timezone) {
        file_put_contents($cacheFile, $timezone);
        date_default_timezone_set($timezone);
    } else {
        $timezone = 'UTC';
        file_put_contents($cacheFile, $timezone);
        date_default_timezone_set($timezone);
    }
}
function console_log($output, $with_script_tags = true) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) .');';
        if ($with_script_tags) {
            $js_code = '<script>' . $js_code . '</script>';
        }
    echo $js_code;
}
class Logger {
    const TYPE_INFO = 'INFO';
    const TYPE_WARNING = 'WARNING';
    const TYPE_ERROR = 'ERROR';

    private $pdo;
    private $loggerName;

    public function __construct($databasePath, $loggerName = 'defaultLogger') {
        $this->pdo = new PDO('sqlite:' . $databasePath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->loggerName = $loggerName;
    }

    private function log($type, $message, $file = 'N/A', $line = 'N/A') {
        global $loggingEnabled, $logDebugEnabled;

        if (!$loggingEnabled) {
            return;
        }

        if ($type === 'DEBUG' && !$logDebugEnabled) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("INSERT INTO dvmAdminlog (timestamp, logger, level, message, file, line) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$timestamp, $this->loggerName, $type, $message, $file, $line]);
    }

    public function info($message, $file = 'N/A', $line = 'N/A') {
        $this->log(self::TYPE_INFO, $message, $file, $line);
    }

    public function warning($message, $file = 'N/A', $line = 'N/A') {
        $this->log(self::TYPE_WARNING, $message, $file, $line);
    }

    public function error($message, $file = 'N/A', $line = 'N/A') {
        $this->log(self::TYPE_ERROR, $message, $file, $line);
    }

    public function debug($message, $file = 'N/A', $line = 'N/A') {
        $this->log('DEBUG', $message, $file, $line);
    }
}

setLocalMachineTimezone();
?>