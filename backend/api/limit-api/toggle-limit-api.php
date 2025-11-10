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
$user_id = $auth->getUserId($jwt);

if (!$user_id) {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$enabled = isset($data['enabled']) ? (int)$data['enabled'] : 1;

try {
    $pdo = $limits->db->getPdo();
    $stmt = $pdo->prepare("UPDATE spending_limits SET enabled = :enabled WHERE user_id = :user_id");
    $stmt->execute(['enabled'=>$enabled,'user_id'=>$user_id]);
    echo json_encode(['success'=>true,'message'=>'Limits toggled']);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'message'=>'Database error: '.$e->getMessage()]);
}
?>
