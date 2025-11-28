<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();

require_once __DIR__ . '/../../class/class-auth.php';

$input = json_decode(file_get_contents('php://input'), true);
$refreshToken = $input['refresh_token'] ?? '';

if (empty($refreshToken)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Refresh token is required'
    ]);
    exit();
}

$auth = new Auth();
$result = $auth->refreshAccessToken($refreshToken);

if ($result['success']) {
    http_response_code(200);
} else {
    http_response_code(401);
}

echo json_encode($result);
