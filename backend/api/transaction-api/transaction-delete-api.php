<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type, Auth');

require_once __DIR__ . '/../../class/class-transactions.php';
require_once __DIR__ . '/../../class/class-auth.php';
require_once __DIR__ . '/../../class/class-notifications.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new Auth();
$transactions = new Transactions();
$notifications = new Notifications();

$data = json_decode(file_get_contents("php://input"), true);
$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');

if (!$auth->isUser($jwt)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $auth->getUserId($jwt);
$transaction_id = $data['id'] ?? null;
$type = $data['type'] ?? 'income';

if (!$transaction_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Transaction ID is required']);
    exit();
}

// Get transaction date before deleting (needed to check the correct month)
$transaction_date = null;
if ($type === 'expense') {
    $transaction_info = $transactions->getIncomeById($transaction_id, $user_id, 'expense');
    if ($transaction_info['success'] && isset($transaction_info['transaction']['date'])) {
        $transaction_date = $transaction_info['transaction']['date'];
    }
}

$result = $transactions->deleteTransaction($transaction_id, $user_id, $type);

// If an expense was successfully deleted, check spending limits for that month
if ($result['success'] && $type === 'expense' && $transaction_date) {
    // Don't show popup when deleting transactions (allow_popup = false)
    $limit_check = $notifications->checkAndNotifySpendingLimits($user_id, $transaction_date, false);
    // Don't add popup info to result since we're not showing popups on delete
}

echo json_encode($result);
?>
