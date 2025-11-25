<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Auth');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../class/class-auth.php';
require_once __DIR__ . '/../../class/class-user.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Read JWT from header
$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');

if (empty($jwt)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authorization header missing or invalid']);
    exit;
}

$auth = new Auth();
$user_id = $auth->getUserId($jwt);

if (!$user_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
    exit;
}

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Accept fields: currentPassword (optional), newPassword (required), confirmPassword (required)
$currentPassword = isset($input['currentPassword']) ? trim($input['currentPassword']) : null;
$newPassword = isset($input['newPassword']) ? trim($input['newPassword']) : '';
$confirmPassword = isset($input['confirmPassword']) ? trim($input['confirmPassword']) : '';

if (empty($newPassword) || empty($confirmPassword)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'New password and confirmation are required']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'New password and confirmation do not match']);
    exit;
}

// Optional: enforce minimum password length (same as in class)
if (strlen($newPassword) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters long']);
    exit;
}

$user = new User();
$result = $user->changePassword($user_id, $currentPassword, $newPassword);

http_response_code($result['success'] ? 200 : 400);
echo json_encode($result);
