<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../class/class-auth.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // for Firebase\JWT

$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data'
    ]);
    exit();
}

$identifier = $input['identifier'] ?? ''; // email or username
$password = $input['password'] ?? '';

$auth = new Auth();
$result = $auth->loginUser($identifier, $password, 'admin'); // hardcoded role admin

if ($result['success']) {
    http_response_code(200);
} else {
    http_response_code(401);
}

echo json_encode($result);
?>
