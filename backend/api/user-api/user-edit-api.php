<?php
    session_start();

    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    // Handle preflight request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    require_once __DIR__ . '/../../class/class-auth.php';

    // Create Auth instance
    $auth = new Auth();

    $jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');

    if (!$auth->isAdmin($jwt)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Permission denied: only admins can edit users'
        ]);
        exit();
    }

    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
        exit();
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        exit();
    }

    $user_id = $input['user_id'] ?? null;
    $username = $input['username'] ?? '';
    $email = $input['email'] ?? '';
    $role = $input['role'] ?? '';

    if (!$user_id || !$username || !$email || !$role) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        exit();
    }

    require_once __DIR__ . '/../../class/class-user.php';
    $user = new User();
    $result = $user->updateUser($user_id, $username, $email, $role);

    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
?>
