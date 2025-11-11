<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../../class/class-categories.php';
require_once __DIR__ . '/../../class/class-auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new Auth();
$categories = new Categories();

$data = json_decode(file_get_contents("php://input"), true);

// Robustly extract Authorization header
$jwt = '';
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $jwt = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $jwt = $headers['Authorization'];
    } elseif (isset($headers['authorization'])) {
        $jwt = $headers['authorization'];
    }
}
$jwt = str_replace('Bearer ', '', $jwt);


$id = $data['id'] ?? 0;
$name = $data['name'] ?? '';
$type = $data['type'];
$user_id = $auth->getUserId($jwt);

// Check if category belongs to user
$pdo = (new Db())->getPdo();
$stmt = $pdo->prepare("SELECT user_id FROM categories WHERE category_id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$cat = $stmt->fetch(PDO::FETCH_ASSOC);
if ($cat && $cat['user_id'] == $user_id) {
    echo json_encode($categories->updateCategory($id, $name, $type));
} else {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
}
?>
