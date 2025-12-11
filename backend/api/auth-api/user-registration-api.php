<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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
$role     = $input['role'] ?? 'user';

// Get JWT from Authorization header
$jwtHeader = $_SERVER['HTTP_AUTH'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;
$jwt = null;
if ($jwtHeader) {
    if (stripos($jwtHeader, 'Bearer ') === 0) {
        $jwt = trim(substr($jwtHeader, 7));
    } else {
        $jwt = trim($jwtHeader);
    }
}

// Check if admin
$auth = new Auth();
$isAdmin = $jwt ? $auth->isAdmin($jwt) : false;

// captcha fields (only required if not admin)
$captchaA = isset($input['captcha_a']) ? (int)$input['captcha_a'] : null;
$captchaB = isset($input['captcha_b']) ? (int)$input['captcha_b'] : null;
$captchaAnswer = isset($input['captcha_answer']) ? (int)$input['captcha_answer'] : null;

// Check if banned
if ($rateLimiter->isBlocked($ip, $endpoint)) {
    $rateLimiter->registerAttempt($ip, $endpoint, null);
    http_response_code(429);
    echo json_encode(['success'=>false,'message'=>'Too many registration attempts. Try later.']);
    exit();
}

// Require captcha only for normal users
if (!$isAdmin) {
    if ($captchaA === null || $captchaB === null || $captchaAnswer === null) {
        $rateLimiter->registerAttempt($ip, $endpoint, null);
        http_response_code(400);
        echo json_encode(['success'=>false,'message'=>'Captcha required for registration']);
        exit();
    }

    if (($captchaA + $captchaB) !== $captchaAnswer) {
        $rateLimiter->registerAttempt($ip, $endpoint, null);
        http_response_code(403);
        echo json_encode(['success'=>false,'message'=>'Invalid captcha for registration']);
        exit();
    }
}

// Attempt registration
$result = $auth->registerUser($username, $email, $password, $role);

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

// Response
http_response_code($result['success'] ? 201 : 400);
echo json_encode($result);
?>
