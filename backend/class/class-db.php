<?php
    require_once __DIR__ . '/../vendor/autoload.php';

    use Dotenv\Dotenv;

    class Db {
        protected $pdo;

        public function __construct() {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();

            $host = $_ENV['DB_HOST'];
            $dbname = $_ENV['DB_NAME'];
            $username = $_ENV['DB_USER'];
            $password = $_ENV['DB_PASSWORD'];
            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8";

            try {
                $this->pdo = new PDO($dsn, $username, $password);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        public function getPdo() {
            return $this->pdo;
        }
        public function closeConnection() {
            $this->pdo = null;
        }
    }
?>
