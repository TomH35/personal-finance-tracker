<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST');
header('Access-Control-Allow-Headers: Content-Type, Auth');

require_once __DIR__ . '/../../class/class-limits.php';
require_once __DIR__ . '/../../class/class-auth.php';

$auth = new Auth();
$limits = new Limits();

$data = json_decode(file_get_contents("php://input"), true);
$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');
$user_id = $auth->getUserId($jwt);

if (!$user_id) {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$limit_id = $data['limit_id'] ?? null;
$month = $data['month'] ?? null;
$year = $data['year'] ?? null;
$warning = $data['warning_limit'] ?? null;
$critical = $data['critical_limit'] ?? null;

if (!$limit_id || !$month || !$year || $warning === null || $critical === null) {
    echo json_encode(['success'=>false,'message'=>'All fields are required']);
    exit;
}

$result = $limits->editLimit($user_id, $limit_id, $month, $year, $warning, $critical);
echo json_encode($result);
?>
