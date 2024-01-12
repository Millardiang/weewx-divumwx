<?php
#####################################################################################################################                                                                                                        #
#                                                                                                                   #
# weewx-divumwx Skin Template maintained by The DivumWX Team                                                        #
#                                                                                                                   #
# Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved                                 #
#                                                                                                                   #
# Distributed under terms of the GPLv3. See the file LICENSE.txt for your rights.                                   #
#                                                                                                                   #
# Issues for weewx-divumwx skin template should be addressed to https://github.com/Millardiang/weewx-divumwx/issues #
#                                                                                                                   #
#####################################################################################################################

function displaySidebar() {
    ?>
		<!-- BEGIN #sidebar -->
		<div id="sidebar" class="app-sidebar">
			<!-- BEGIN scrollbar -->
			<div class="app-sidebar-content" data-scrollbar="true" data-height="100%">
				<!-- BEGIN menu -->
				<div class="menu">
					<div class="menu-header">Navigation</div>
					<div class="menu-item">
						<a href="../index.php" class="menu-link">
							<span class="menu-icon"><i class="bi bi-rocket-takeoff"></i></span>
							<span class="menu-text">Return to Website</span>
						</a>
					</div>
					<div class="menu-item">
						<a href="./dvmDashboard.php" class="menu-link">
							<span class="menu-icon"><i class="bi bi-house-door"></i></span>
							<span class="menu-text">Home</span>
						</a>
					</div>
					<div class="menu-item">
						<a href="./dvmDBManager.php" class="menu-link">
							<span class="menu-icon"><i class="bi bi-database"></i></span>
							<span class="menu-text">Database Manager</span>
						</a>
					</div>
					<div class="menu-item">
						<a href="./dvmFilemanager.php" class="menu-link">
							<span class="menu-icon"><i class="bi bi-folder"></i></span>
							<span class="menu-text">File Manager</span>
						</a>
					</div>
					<div class="menu-item">
						<a href="./dvmSettings.php" class="menu-link">
							<span class="menu-icon"><i class="bi bi-gear"></i></span>
							<span class="menu-text">Settings</span>
						</a>
					</div>
					<div class="menu-item">
						<a href="./dvmSysInfo.php" class="menu-link">
							<span class="menu-icon"><i class="bi bi-info-circle"></i></span>
							<span class="menu-text">System Info</span>
						</a>
					</div>

					<div class="menu-item">
						<a href="./dvmModFiles.php" class="menu-link">
							<span class="menu-icon"><i class="bi bi-list"></i></span>
							<span class="menu-text">Module Info</span>
						</a>
					</div>
					<div class="menu-item">
						<a href="./dvmDashboard.php?logout=true" class="menu-link">
							<span class="menu-icon"><i class="bi bi-box-arrow-right"></i></span>
							<span class="menu-text">Logout</span>
						</a>
					</div>
				</div>
				<!-- END menu -->
			</div>
			<!-- END scrollbar -->
            <?php
}

function ldusrSettings($usrSettingsFile) {
    include $usrSettingsFile;
    $allVars = get_defined_vars();
    unset($allVars['usrSettingsFile']);
    return $allVars;
}
function wrusrSettings($usrSettings, $usrSettingsFile) {
    if (file_exists($usrSettingsFile)) {
        $newFilename = $usrSettingsFile . '.' . date('YmdHis');
        rename($usrSettingsFile, $newFilename);
    }
    $contents = "<?php\n";
    foreach ($usrSettings as $name => $value) {
        if (is_array($value)) {
            foreach ($value as $subName => $subValue) {
                $contents .= "\${$name}['$subName'] = '$subValue';\n";
            }
        } else {
            $contents .= "\$$name = '$value';\n";
        }
    }
    $contents .= "?>";
    file_put_contents($usrSettingsFile, $contents);
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
?>