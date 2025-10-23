<?php
    require_once __DIR__ . '/class-db.php';

    class Auth {
        private $db;

        public function __construct() {
            $this->db = new Db();
        }

        /**
         * Register a new user
         * 
         * @param string $username The username
         * @param string $email The email address
         * @param string $password The plain text password
         * @param string $role The user role (default: 'user')
         * @return array Response array with success status and message
         */
        public function registerUser($username, $email, $password, $role = 'user') {
            try {
                $pdo = $this->db->getPdo();

                // Validate input
                if (empty($username) || empty($email) || empty($password)) {
                    return [
                        'success' => false,
                        'message' => 'All fields are required'
                    ];
                }

                // Validate email format
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return [
                        'success' => false,
                        'message' => 'Invalid email format'
                    ];
                }

                // Validate password length (minimum 6 characters)
                if (strlen($password) < 6) {
                    return [
                        'success' => false,
                        'message' => 'Password must be at least 6 characters long'
                    ];
                }

                // Check if username already exists
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = :username");
                $stmt->execute(['username' => $username]);
                if ($stmt->fetch()) {
                    return [
                        'success' => false,
                        'message' => 'Username already exists'
                    ];
                }

                // Check if email already exists
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
                $stmt->execute(['email' => $email]);
                if ($stmt->fetch()) {
                    return [
                        'success' => false,
                        'message' => 'Email already registered'
                    ];
                }

                // Hash the password
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                // Insert new user
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password_hash, role) 
                    VALUES (:username, :email, :password_hash, :role)
                ");
                
                $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'password_hash' => $passwordHash,
                    'role' => $role
                ]);

                return [
                    'success' => true,
                    'message' => 'User registered successfully',
                    'user_id' => $pdo->lastInsertId()
                ];

            } catch (PDOException $e) {
                error_log("Registration error: " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'An error occurred during registration'
                ];
            }
        }
    }
?>
