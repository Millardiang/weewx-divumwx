<?php
session_start();
error_log("🔄 reset_session.php accessed - Session ID: " . session_id());
error_log("🔄 Before Reset - canAccessSetup: " . ($_SESSION['canAccessSetup'] ?? 'NOT SET'));
error_log("🔄 Before Reset - setupAttempted: " . ($_SESSION['setupAttempted'] ?? 'NOT SET'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("❌ Invalid request method!");
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
error_log("🔄 Received input: " . print_r($input, true));

if (!isset($input['csrf_token']) || $input['csrf_token'] !== $_SESSION['csrf_token']) {
    error_log("❌ CSRF token mismatch! Expected: " . $_SESSION['csrf_token'] . " Received: " . $input['csrf_token']);
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'CSRF token mismatch.']);
    exit;
}

// Reset session variables correctly
unset($_SESSION['setupAttempted']);
$_SESSION['canAccessSetup'] = true;  // ✅ Re-establish access instead of unsetting it

error_log("✅ After Reset - canAccessSetup: " . ($_SESSION['canAccessSetup'] ?? 'NOT SET'));
error_log("✅ After Reset - setupAttempted: " . ($_SESSION['setupAttempted'] ?? 'NOT SET'));

echo json_encode(['status' => 'success', 'message' => 'Session reset successfully.']);
exit;
