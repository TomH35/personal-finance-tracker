<?php
// CORS hlavičky – musia byť úplne na začiatku
header('Access-Control-Allow-Origin: *'); // alebo '*', ak netreba credentials
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// OPTIONS preflight požiadavka – musí vrátiť hlavičky
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../../class/class-tips.php';
require_once __DIR__ . '/../../class/class-auth.php';

$auth = new Auth();
$tips = new Tips();

$data = json_decode(file_get_contents("php://input"), true);
$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');

// Only admin can edit tips
if (!$auth->isAdmin($jwt)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Admin access required']);
    exit();
}

$id = $data['id'] ?? '';
$title = $data['title'] ?? '';
$content = $data['content'] ?? '';

if (empty($id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tip ID is required']);
    exit();
}

echo json_encode($tips->updateTip($id, $title, $content));
?>
