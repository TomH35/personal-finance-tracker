<?php
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Auth');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(200);

require_once __DIR__ . '/../../class/class-auth.php';
require_once __DIR__ . '/../../class/class-user.php';

$auth = new Auth();
$user = new User();

$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');
$userId = $auth->getUserId($jwt);

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$result = $user->uploadProfilePicture($userId, $_FILES['profile_picture'] ?? null);

http_response_code($result['success'] ? 200 : 400);
echo json_encode($result);
