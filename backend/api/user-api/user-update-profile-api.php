<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Auth');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../class/class-auth.php';
require_once '../../class/class-user.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

if (!isset($data['username']) || !isset($data['email']) || !isset($data['currency'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username, email, and currency are required']);
    exit;
}

$username = $data['username'];
$email = $data['email'];
$currency = $data['currency'];

$user = new User();
$result = $user->updateUserProfile($user_id, $username, $email, $currency);

if ($result['success']) {
    http_response_code(200);
    echo json_encode($result);
} else {
    http_response_code(400);
    echo json_encode($result);
}
