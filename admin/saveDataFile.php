<?php
$changesData = file_get_contents('php://input');
$changes = json_decode($changesData, true);
$parentDirectory = dirname(getcwd());
$originalFile = $parentDirectory . DIRECTORY_SEPARATOR . 'userSettings.php';
$backupFile = $originalFile . '.' . date('YdmHis'); // Create a backup file with a timestamp
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
