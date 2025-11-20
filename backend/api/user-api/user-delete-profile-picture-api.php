<?php
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Auth');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../class/class-auth.php';
$auth = new Auth();

$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');
$userId = $auth->getUserId($jwt);
if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$extensions = ['jpg','jpeg','png','gif'];
$deleted = false;
foreach ($extensions as $ext) {
    $filePath = __DIR__ . '/../../public/user-images/user_' . $userId . '.' . $ext;
    if (file_exists($filePath)) {
        unlink($filePath);
        $deleted = true;
        break;
    }
}

if ($deleted) {
    echo json_encode(['success' => true, 'message' => 'Profile picture deleted']);
} else {
    echo json_encode(['success' => false, 'message' => 'No profile picture found']);
}
