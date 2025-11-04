<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Auth');

require_once __DIR__ . '/../../class/class-categories.php';
require_once __DIR__ . '/../../class/class-auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new Auth();
$categories = new Categories();

$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');
$user_id = $auth->getUserId($jwt);

echo json_encode($categories->getAllCategories($user_id));
?>
