<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Auth');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../class/class-auth.php';
require_once __DIR__ . '/../../class/class-user.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JWT from header
$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');

if (empty($jwt)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No token provided']);
    exit();
}

// Authenticate user
$auth = new Auth();
if (!$auth->isUser($jwt)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get user ID from JWT
$user_id = $auth->getUserId($jwt);

if (!$user_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit();
}

// Delete user account
$user = new User();
$result = $user->deleteOwnAccount($user_id);

if ($result['success']) {
    http_response_code(200);
} else {
    http_response_code(400);
}

echo json_encode($result);
