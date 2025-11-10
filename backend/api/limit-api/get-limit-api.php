<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Auth');

require_once __DIR__ . '/../../class/class-limits.php';
require_once __DIR__ . '/../../class/class-auth.php';

$auth = new Auth();
$limits = new Limits();

$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');
$user_id = $auth->getUserId($jwt);

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

echo json_encode($limits->getLimit($user_id));
?>
