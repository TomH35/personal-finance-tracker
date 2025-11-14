<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Content-Type, Auth');

require_once __DIR__ . '/../../class/class-transactions.php';
require_once __DIR__ . '/../../class/class-auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new Auth();
$transactions = new Transactions();

$data = json_decode(file_get_contents("php://input"), true);
$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');

if (!$auth->isUser($jwt)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $auth->getUserId($jwt);
$transaction_id = $data['id'] ?? null;
$amount = $data['amount'] ?? null;
$category_id = $data['category_id'] ?? null;
$note = $data['note'] ?? null;
$date = $data['date'] ?? null;
$type = $data['type'] ?? 'income';

if (!$transaction_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Transaction ID is required']);
    exit();
}

echo json_encode($transactions->updateTransaction($transaction_id, $user_id, $amount, $category_id, $note, $date, $type));
?>
