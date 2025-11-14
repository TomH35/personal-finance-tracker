<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Auth');

require_once __DIR__ . '/../../class/class-transaction.php';
require_once __DIR__ . '/../../class/class-auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new Auth();
$transaction = new Transaction();

$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');

if (!$auth->isUser($jwt)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $auth->getUserId($jwt);
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$category_id = $_GET['category_id'] ?? null;
$type = $_GET['type'] ?? null;

echo json_encode($transaction->getAllTransactions($user_id, $start_date, $end_date, $category_id, $type));
?>
