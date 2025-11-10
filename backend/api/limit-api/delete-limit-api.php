<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Auth');

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

$limit_id = $_GET['limit_id'] ?? null;
if (!$limit_id) {
    echo json_encode(['success' => false, 'message' => 'Limit ID is required']);
    exit;
}

echo json_encode($limits->deleteLimit($limit_id));
?>
