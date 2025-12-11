<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
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
$isAdminCreation = $input['is_admin_creation'] ?? false;

// captcha fields (only used for normal user registration)
$captchaA = isset($input['captcha_a']) ? (int)$input['captcha_a'] : null;
$captchaB = isset($input['captcha_b']) ? (int)$input['captcha_b'] : null;
$captchaAnswer = isset($input['captcha_answer']) ? (int)$input['captcha_answer'] : null;

// If not admin creation, apply rate limiter & captcha
if (!$isAdminCreation) {
    // Check if banned
    if ($rateLimiter->isBlocked($ip, $endpoint)) {
        $rateLimiter->registerAttempt($ip, $endpoint, null);
        http_response_code(429);
        echo json_encode(['success'=>false,'message'=>'Too many registration attempts. Try later.']);
        exit();
    }

    // Require captcha for registration (simple stateless math captcha)
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
$auth = new Auth();
$result = $auth->registerUser($username, $email, $password);

// Determine user_id if registration successful
$userId = $result['success'] ? ($result['user_id'] ?? null) : null;

// Log attempt only if not admin
if (!$isAdminCreation) {
    $rateLimiter->registerAttempt($ip, $endpoint, $userId);

    // Check sliding window & ban if needed
    if (!$rateLimiter->checkLimit($ip, $endpoint, $userId)) {
        http_response_code(429);
        echo json_encode(['success'=>false,'message'=>'Too many attempts. Please wait.']);
        exit();
    }
}

http_response_code($result['success'] ? 201 : 400);
echo json_encode($result);
