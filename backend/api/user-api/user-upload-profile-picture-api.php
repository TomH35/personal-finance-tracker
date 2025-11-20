<?php
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Auth');

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

if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit();
}

$file = $_FILES['profile_picture'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg','jpeg','png','gif'];
if (!in_array($ext, $allowed)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit();
}

// Upload to public/user-images
$uploadDir = __DIR__ . '/../../public/user-images/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$newFileName = 'user_' . $userId . '.' . $ext;
$uploadPath = $uploadDir . $newFileName;

if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    echo json_encode([
        'success' => true,
        'fileName' => $newFileName,
        'url' => '/public/user-images/' . $newFileName  
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
}
