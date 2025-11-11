<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Auth');

require_once __DIR__ . '/../../class/class-categories.php';
require_once __DIR__ . '/../../class/class-auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new Auth();
$categories = new Categories();

$data = json_decode(file_get_contents("php://input"), true);
$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');

$name = $data['name'] ?? '';
$type = $data['type'] ?? 'expense';

$user_id = $auth->getUserId($jwt);

if ($auth->isAdmin($jwt)) {
    echo json_encode($categories->createCategory($name, $user_id, $type, true));
} else {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
}
?>
