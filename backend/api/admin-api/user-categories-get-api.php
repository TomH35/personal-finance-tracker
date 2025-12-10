<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Auth');

require_once __DIR__ . '/../../class/class-custom-categories.php';
require_once __DIR__ . '/../../class/class-auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new Auth();
$customCategories = new CustomCategories();

$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTH'] ?? '');

if (!$auth->isAdmin($jwt)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A valid user_id is required']);
    exit();
}

$allCategories = $customCategories->getAllCustomCategories($user_id);
$userOnlyCategories = $customCategories->getUserOwnedCategories($user_id);

if (!$allCategories['success']) {
    http_response_code(500);
    echo json_encode($allCategories);
    exit();
}

if (!$userOnlyCategories['success']) {
    http_response_code(500);
    echo json_encode($userOnlyCategories);
    exit();
}

echo json_encode([
    'success' => true,
    'categories' => $allCategories['categories'], // All categories available to the user (predefined + custom)
    'user_categories' => $userOnlyCategories['categories'] // Only the ones created by the user
]);
?>
