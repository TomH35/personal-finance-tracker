<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST');
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
$limit_id = $data['limit_id'] ?? null;
$warning = $data['warning_limit'] ?? null;
$critical = $data['critical_limit'] ?? null;
$enabled = isset($data['enabled']) ? (int)$data['enabled'] : 1;
$currency = $data['currency'] ?? 'USD';

if (!$limit_id || $warning === null || $critical === null) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$result = $limits->editLimit($user_id, $limit_id, $warning, $critical, $enabled, $currency);

// After updating limits, recalculate current month's notifications
// This removes old notifications and creates new ones based on new limits
if ($result['success']) {
    if ($enabled) {
        $notifications->recalculateLimitsForCurrentMonth($user_id, $limit_id);
    } else {
        // If limits are disabled, just delete current month's notifications
        $current_month = date('Y-m');
        $stmt = $notifications->db->prepare("
            DELETE FROM notifications 
            WHERE user_id = :user_id 
            AND limit_id = :limit_id
            AND DATE_FORMAT(created_at, '%Y-%m') = :current_month
        ");
        $stmt->execute([
            'user_id' => $user_id,
            'limit_id' => $limit_id,
            'current_month' => $current_month
        ]);
    }
}

echo json_encode($result);
?>
