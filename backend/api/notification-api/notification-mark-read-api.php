<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Auth');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../class/class-auth.php';
require_once '../../class/class-notifications.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

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

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['notification_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
    exit;
}

$notifications = new Notifications();
$result = $notifications->markAsRead($data['notification_id'], $user_id);

if ($result['success']) {
    http_response_code(200);
} else {
    http_response_code(404);
}

echo json_encode($result);
