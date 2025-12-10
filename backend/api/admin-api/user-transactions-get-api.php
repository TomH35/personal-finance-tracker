<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Auth');

require_once __DIR__ . '/../../class/class-transactions.php';
require_once __DIR__ . '/../../class/class-auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new Auth();
$transactions = new Transactions();

$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');

if (!$auth->isAdmin($jwt)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A valid user_id is required']);
    exit();
}

$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$category_id = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$type = $_GET['type'] ?? null;

if ($type !== null && !in_array($type, ['income', 'expense'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid transaction type']);
    exit();
}

echo json_encode($transactions->getAllTransactions($user_id, $start_date, $end_date, $category_id, $type));
?>
