<?php
    require_once 'vendor/autoload.php';
    use Dotenv\Dotenv;
    
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $host = $_ENV['DB_HOST'];
    $db   = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASSWORD'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        echo json_encode(['status' => 'OK', 'message' => 'Database setup successful']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
    }
?>
