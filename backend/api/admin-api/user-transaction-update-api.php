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

$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');

if (!$auth->isAdmin($jwt)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true) ?? [];

$user_id = isset($data['user_id']) ? (int) $data['user_id'] : 0;
$transaction_id = isset($data['id']) ? (int) $data['id'] : (isset($data['transaction_id']) ? (int) $data['transaction_id'] : 0);
$amount = isset($data['amount']) ? (float) $data['amount'] : null;
$category_id = isset($data['category_id']) ? (int) $data['category_id'] : null;
$note = $data['note'] ?? null;
$date = $data['date'] ?? null;
$type = $data['type'] ?? 'expense';
$userCurrency = $data['userCurrency'] ?? 'USD';

if ($user_id <= 0 || $transaction_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Both user_id and transaction id are required']);
    exit();
}

if (!in_array($type, ['income', 'expense'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid transaction type']);
    exit();
}

echo json_encode($transactions->updateTransaction($transaction_id, $user_id, $amount, $category_id, $note, $date, $type, $userCurrency));
?>
