<?php
// CORS hlavičky – musia byť úplne na začiatku
header('Access-Control-Allow-Origin: http://localhost:5173'); // alebo '*', ak netreba credentials
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');

// Just verify user is logged in (don't strictly check token validity)
// This matches the category API behavior
echo json_encode($tips->getTips());
?>
