<?php
    class Db {
        protected $pdo;

        // Constructor
        public function __construct() {
            // Database connection details
            $dsn = 'mysql:host=localhost;dbname=finance_tracker_db;charset=utf8';
            $username = 'root';
            $password = '';

            try {
                // Create PDO instance for database interaction
                $this->pdo = new PDO($dsn, $username, $password);

                // Set error handling mode to exception
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            } catch (PDOException $e) {
                // Stop execution if connection fails
                die("Database connection failed: " . $e->getMessage());
            }
        }

        // Returns PDO instance for executing queries
        public function getPdo() {
            return $this->pdo;
        }
    }
?>
