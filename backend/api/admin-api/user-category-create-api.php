<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Auth');

require_once __DIR__ . '/../../class/class-custom-categories.php';
require_once __DIR__ . '/../../class/class-auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new Auth();
$customCategories = new CustomCategories();

$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');

if (!$auth->isAdmin($jwt)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true) ?? [];

$user_id = isset($data['user_id']) ? (int) $data['user_id'] : 0;
$name = trim($data['name'] ?? '');
$type = $data['type'] ?? 'expense';

if ($user_id <= 0 || $name === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'user_id and name are required']);
    exit();
}

if (!in_array($type, ['income', 'expense'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid category type']);
    exit();
}

$result = $customCategories->createCustomCategory($name, $user_id, $type);
http_response_code($result['success'] ? 200 : 400);
echo json_encode($result);
?>
