<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Auth');

require_once __DIR__ . '/../../class/class-limits.php';
require_once __DIR__ . '/../../class/class-auth.php';

$auth = new Auth();
$limits = new Limits();

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

echo json_encode($limits->setLimit($user_id, $warning, $critical, $enabled));
?>
