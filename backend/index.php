<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// XAMPP database connection (root, no password)
$host = 'localhost';
$db   = 'finance_tracker_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo json_encode(['status' => 'OK', 'message' => 'Database connection successful']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
}
?>
