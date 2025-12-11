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

$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');

if (!$auth->isUser($jwt)) {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$user_id = $auth->getUserId($jwt);
$data = json_decode(file_get_contents("php://input"), true);
$limit_id = $data['limit_id'] ?? null;
$enabled = isset($data['enabled']) ? (int)$data['enabled'] : 1;

if (!$limit_id) {
    echo json_encode(['success' => false, 'message' => 'limit_id is required']);
    exit;
}

$result = $limits->toggleLimit($user_id, $limit_id, $enabled);

// After toggling limits, recalculate notifications
if ($result['success']) {
    if ($enabled) {
        // If enabling, recalculate to create notifications if needed
        $notifications->recalculateLimitsForCurrentMonth($user_id, $limit_id);
    } else {
        // If disabling, remove current month's notifications
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
