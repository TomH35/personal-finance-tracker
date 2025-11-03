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

    /**
     * Log in a user
     * 
     * @param string $identifier The user's email or username
     * @param string $password The plain text password
     * @param string $expectedRole The expected role ('admin' or 'user')
     * @return array Response array with success status, message, and token if successful
     */
    public function loginUser($identifier, $password, $expectedRole = 'user') {
        try {
            $pdo = $this->db->getPdo();

            // Validate input
            if (empty($identifier) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'Identifier and password are required'
                ];
            }

            // Find user by email or username
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :identifier OR username = :identifier LIMIT 1");
            $stmt->execute(['identifier' => $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid identifier or password'
                ];
            }

            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid identifier or password'
                ];
            }

            // Check user role matches expected role
            if ($user['role'] !== $expectedRole) {
                return [
                    'success' => false,
                    'message' => 'Access denied. Only ' . ucfirst($expectedRole) . 's are allowed.'
                ];
            }

            // Load JWT secret from .env
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
            $jwtSecret = $_ENV['JWT_SECRET'];

            // Prepare JWT payload
            $issuedAt = time();
            $expirationTime = $issuedAt + (60 * 60); // valid for 1 hour

            $payload = [
                'iat' => $issuedAt,
                'exp' => $expirationTime,
                'iss' => 'PFT',
                'data' => [
                    'user_id' => $user['user_id'],
                    'email' => $user['email'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ]
            ];

            // Generate JWT token
            $jwt = \Firebase\JWT\JWT::encode($payload, $jwtSecret, 'HS256');

            return [
                'success' => true,
                'message' => 'Login successful',
                'token' => $jwt,
                'user' => [
                    'user_id' => $user['user_id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ];

        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during login'
            ];
        } catch (Exception $e) {
            error_log("JWT error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Token generation failed'
            ];
        }
    }

    /**
     * Check if a user is an admin based on JWT token
     * 
     * @param string $jwt The JWT token to verify
     * @return bool True if user is admin, false otherwise
     */
    public function isAdmin($jwt) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
            $jwtSecret = $_ENV['JWT_SECRET'];

            $decoded = \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key($jwtSecret, 'HS256'));

            if (!isset($decoded->data->user_id)) {
                return false;
            }

            $userId = $decoded->data->user_id;

            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = :user_id LIMIT 1");
            $stmt->execute(['user_id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['role'] === 'admin') {
                return true;
            }

            return false;

        } catch (\Firebase\JWT\ExpiredException $e) {
            error_log("JWT expired: " . $e->getMessage());
            return false;
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            error_log("JWT signature invalid: " . $e->getMessage());
            return false;
        } catch (\Firebase\JWT\BeforeValidException $e) {
            error_log("JWT not yet valid: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("JWT verification error: " . $e->getMessage());
            return false;
        } catch (PDOException $e) {
            error_log("Database error in isAdmin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user ID from JWT token
     * Validates and decodes the JWT, verifies the user exists in the database,
     * and returns the user ID
     * 
     * @param string $jwt The JWT token to verify
     * @return int|null The user ID if valid and user exists, null otherwise
     */
    public function getUserId($jwt) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
            $jwtSecret = $_ENV['JWT_SECRET'];

            $decoded = \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key($jwtSecret, 'HS256'));

            if (!isset($decoded->data->user_id)) {
                error_log("JWT does not contain user_id");
                return null;
            }

            $userId = $decoded->data->user_id;

            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = :user_id LIMIT 1");
            $stmt->execute(['user_id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                error_log("User with ID $userId does not exist in database");
                return null;
            }

            return (int)$user['user_id'];

        } catch (\Firebase\JWT\ExpiredException $e) {
            error_log("JWT expired: " . $e->getMessage());
            return null;
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            error_log("JWT signature invalid: " . $e->getMessage());
            return null;
        } catch (\Firebase\JWT\BeforeValidException $e) {
            error_log("JWT not yet valid: " . $e->getMessage());
            return null;
        } catch (Exception $e) {
            error_log("JWT verification error: " . $e->getMessage());
            return null;
        } catch (PDOException $e) {
            error_log("Database error in getUserId: " . $e->getMessage());
            return null;
        }
    }
}
?>
