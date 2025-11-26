<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();

require_once __DIR__ . '/../../class/class-auth.php';
require_once __DIR__ . '/../../class/class-rate-limiter.php';

$ip = $_SERVER['REMOTE_ADDR'];
$endpoint = "user_login";

$rateLimiter = new RateLimiter();

$input = json_decode(file_get_contents('php://input'), true);
$identifier = $input['identifier'] ?? '';
$password   = $input['password'] ?? '';

// captcha fields (stateless simple math captcha)
$captchaA = isset($input['captcha_a']) ? (int)$input['captcha_a'] : null;
$captchaB = isset($input['captcha_b']) ? (int)$input['captcha_b'] : null;
$captchaAnswer = isset($input['captcha_answer']) ? (int)$input['captcha_answer'] : null;

// If there is an active block + require_captcha, tell frontend to show captcha (include blocked_until)
$latest = $rateLimiter->getLatestRecord($ip, $endpoint, null);
if ($latest && isset($latest['require_captcha']) && intval($latest['require_captcha']) === 1) {
    if (isset($latest['blocked_until']) && $latest['blocked_until'] !== null && strtotime($latest['blocked_until']) > time()) {
        // require captcha case: respond with flag and blocked_until so frontend can show modal & timer
        http_response_code(200);
        echo json_encode([
            'success' => false,
            'message' => 'Too many attempts. Captcha required to proceed.',
            'captcha_required' => true,
            'blocked_until' => $latest['blocked_until']
        ]);
        exit();
    }
}

// Check ban normally (if blocked but no require_captcha, return 429)
if ($rateLimiter->isBlocked($ip, $endpoint)) {
    $rateLimiter->registerAttempt($ip, $endpoint, null, null, $identifier);
    http_response_code(429);
    echo json_encode(['success'=>false,'message'=>'Too many login attempts. Try later.']);
    exit();
}

// Check if captcha is required by policy (should be false in normal cases because we now rely on DB flag)
// We'll still call requiresCaptcha() for compatibility; it will return true only if DB flag + active block exist.
$captchaRequired = $rateLimiter->requiresCaptcha($ip, $endpoint, null, $identifier);

// If captcha required, validate presence and correctness
if ($captchaRequired) {
    if ($captchaA === null || $captchaB === null || $captchaAnswer === null) {
        // register attempt and inform frontend to show captcha
        $rateLimiter->registerAttempt($ip, $endpoint, null, null, $identifier);
        http_response_code(400);
        echo json_encode(['success'=>false,'message'=>'Captcha required','captcha_required'=>true]);
        exit();
    }

    // validate captcha
    if (($captchaA + $captchaB) !== $captchaAnswer) {
        $rateLimiter->registerAttempt($ip, $endpoint, null, null, $identifier);
        http_response_code(403);
        echo json_encode(['success'=>false,'message'=>'Invalid captcha','captcha_required'=>true]);
        exit();
    }
}

// Attempt login
$auth = new Auth();
$result = $auth->loginUser($identifier, $password);

// Determine user_id if login successful
$userId = $result['success'] ? ($result['user']['user_id'] ?? null) : null;

// Log the attempt (always)
$rateLimiter->registerAttempt($ip, $endpoint, $userId, null, $identifier);

// Check sliding window limit (this will apply ban if needed)
if (!$rateLimiter->checkLimit($ip, $endpoint, $userId, $identifier)) {
    http_response_code(429);
    echo json_encode(['success'=>false,'message'=>'Too many attempts. Please wait.']);
    exit();
}

// If login failed → tell frontend if captcha is now required (based on latest DB record)
if (!$result['success']) {
    $nowRequires = $rateLimiter->requiresCaptcha($ip, $endpoint, $userId, $identifier);
    $result['captcha_required'] = $nowRequires;
}

http_response_code($result['success'] ? 200 : 401);
echo json_encode($result);
