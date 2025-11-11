<?php
// CORS hlavičky – musia byť úplne na začiatku
header('Access-Control-Allow-Origin: http://localhost:5173'); // alebo '*', ak netreba credentials
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// OPTIONS preflight požiadavka – musí vrátiť hlavičky
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


header('Content-Type: application/json');

require_once __DIR__ . '/../../class/class-categories.php';
require_once __DIR__ . '/../../class/class-auth.php';


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


$name = $data['name'] ?? '';
$type = $data['type'] ?? 'expense';

$user_id = $auth->getUserId($jwt);

if ($auth->isAdmin($jwt)) {
    echo json_encode($categories->createCategory($name, $user_id, $type, true));
} else {
    echo json_encode($categories->createCategory($name, $user_id, $type, false));
}
?>
