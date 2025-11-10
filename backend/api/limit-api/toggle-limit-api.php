<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Auth');

require_once __DIR__ . '/../../class/class-limits.php';
require_once __DIR__ . '/../../class/class-auth.php';

$auth = new Auth();
$limits = new Limits();

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
echo json_encode($result);
?>
