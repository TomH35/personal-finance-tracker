<?php
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    $host = 'localhost';
    $db   = 'finance_tracker_db';
    $user = 'root';
    $pass = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        echo json_encode(['status' => 'OK', 'message' => 'Database setup successful']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
    }
?>
