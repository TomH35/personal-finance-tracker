<?php
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type');

    // Handle preflight request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    require_once __DIR__ . '/../../class/class-auth.php';

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate JSON input
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        exit();
    }

    // Extract parameters
    $username = $input['username'] ?? '';
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    // Create Auth instance
    $auth = new Auth();

    $jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');

    if (!$auth->isAdmin($jwt)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Permission denied: only admins can create admin users'
        ]);
        exit();
    }

    // Register as admin (role = 'admin')
    $result = $auth->registerUser($username, $email, $password, 'admin');

    // Set response code
    if ($result['success']) {
        http_response_code(201);
    } else {
        http_response_code(400);
    }

    echo json_encode($result);
