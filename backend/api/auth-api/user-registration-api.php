<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();

require_once __DIR__ . '/../../class/class-auth.php';
require_once __DIR__ . '/../../class/class-rate-limiter.php';

$ip = $_SERVER['REMOTE_ADDR'];
$endpoint = "user_registration";

$rateLimiter = new RateLimiter();

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$email    = $input['email'] ?? '';
$password = $input['password'] ?? '';

// Check if banned
if ($rateLimiter->isBlocked($ip, $endpoint)) {
    $rateLimiter->registerAttempt($ip, $endpoint, null);
    http_response_code(429);
    echo json_encode(['success'=>false,'message'=>'Too many registration attempts. Try later.']);
    exit();
}


// Attempt registration
$auth = new Auth();
$result = $auth->registerUser($username, $email, $password);

// Determine user_id if registration successful
$userId = $result['success'] ? ($result['user_id'] ?? null) : null;

// Log attempt (always)
$rateLimiter->registerAttempt($ip, $endpoint, $userId);

// Check sliding window & ban if needed
if (!$rateLimiter->checkLimit($ip, $endpoint, $userId)) {
    http_response_code(429);
    echo json_encode(['success'=>false,'message'=>'Too many attempts. Please wait.']);
    exit();
}

http_response_code($result['success'] ? 201 : 400);
echo json_encode($result);
