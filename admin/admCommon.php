<?php
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