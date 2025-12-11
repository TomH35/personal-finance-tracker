<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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
$amount = $data['amount'] ?? null;
$category_id = $data['category_id'] ?? null;
$note = $data['note'] ?? null;
$date = $data['date'] ?? null;
$type = $data['type'] ?? 'income';
$userCurrency = $data['userCurrency'] ?? 'USD';

$result = $transactions->createTransaction($user_id, $amount, $category_id, $note, $date, $type, $userCurrency);

// If an expense was successfully created, check spending limits for that month
if ($result['success'] && $type === 'expense') {
    $transaction_date = $date ? $date : date('Y-m-d');
    $limit_check = $notifications->checkAndNotifySpendingLimits($user_id, $transaction_date);
    // Add popup info to result
    if (isset($limit_check['show_popup'])) {
        $result['show_popup'] = $limit_check['show_popup'];
        $result['notification_type'] = $limit_check['notification_type'];
    }
}

echo json_encode($result);
?>
