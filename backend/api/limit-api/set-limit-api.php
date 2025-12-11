<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Auth');

require_once __DIR__ . '/../../class/class-limits.php';
require_once __DIR__ . '/../../class/class-auth.php';
require_once __DIR__ . '/../../class/class-notifications.php';

$auth = new Auth();
$limits = new Limits();
$notifications = new Notifications();

$data = json_decode(file_get_contents("php://input"), true);
$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');

if (!$auth->isUser($jwt)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $auth->getUserId($jwt);
$warning = $data['warning_limit'] ?? 0;
$critical = $data['critical_limit'] ?? 0;
$enabled = isset($data['enabled']) ? (int)$data['enabled'] : 1;
$currency = $data['currency'] ?? 'USD';

$result = $limits->setLimit($user_id, $warning, $critical, $enabled, $currency);

// After setting new limits, recalculate current month's notifications
if ($result['success'] && $enabled && isset($result['limit_id'])) {
    $notifications->recalculateLimitsForCurrentMonth($user_id, $result['limit_id']);
}

echo json_encode($result);
?>
