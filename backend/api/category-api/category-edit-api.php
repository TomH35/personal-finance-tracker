<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

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

if ($auth->isAdmin($jwt)) {
    $id = $data['id'] ?? 0;
    $name = $data['name'] ?? '';
    $type = $data['type']; 
    echo json_encode($categories->updateCategory($id, $name, $type));
} else {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
}
?>
