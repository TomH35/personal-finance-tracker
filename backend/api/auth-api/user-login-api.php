<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();

require_once __DIR__ . '/../../class/class-auth.php';
require_once __DIR__ . '/../../class/class-rate-limiter.php';
require_once __DIR__ . '/../../class/class-db.php';

$db = new Db();
$pdo = $db->getPdo();

$ip = $_SERVER['REMOTE_ADDR'];
$endpoint = "user_login";

$rateLimiter = new RateLimiter($pdo);

$input = json_decode(file_get_contents('php://input'), true);
$identifier = $input['identifier'] ?? '';
$password   = $input['password'] ?? '';

// Check ban first
if ($rateLimiter->isBlocked($ip, $endpoint)) {
    $rateLimiter->registerAttempt($ip, $endpoint, null, null, $identifier);
    http_response_code(429);
    echo json_encode(['success'=>false,'message'=>'Too many login attempts. Try later.']);
    exit();
}

// Attempt login
$auth = new Auth();
$result = $auth->loginUser($identifier, $password);

// Determine user_id if login successful
$userId = $result['success'] ? ($result['user_id'] ?? null) : null;

// Log the attempt (always)
$rateLimiter->registerAttempt($ip, $endpoint, $userId, null, $identifier);

// Check sliding window limit
if (!$rateLimiter->checkLimit($ip, $endpoint, $userId, $identifier)) {
    http_response_code(429);
    echo json_encode(['success'=>false,'message'=>'Too many attempts. Please wait.']);
    exit();
}

http_response_code($result['success'] ? 200 : 401);
echo json_encode($result);
