<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/class-db.php';

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;

class Auth {
    private $db;
    private $jwtSecret;

    public function __construct() {
        $this->db = new Db();
        
        // Load JWT secret from .env
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
        $this->jwtSecret = $_ENV['JWT_SECRET'];
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
            // If expectedRole is 'user', allow both 'user' and 'admin' roles
            // If expectedRole is 'admin', only allow 'admin' role
            if ($expectedRole === 'user') {
                if ($user['role'] !== 'user' && $user['role'] !== 'admin') {
                    return [
                        'success' => false,
                        'message' => 'Access denied. Invalid user role.'
                    ];
                }
            } elseif ($expectedRole === 'admin') {
                if ($user['role'] !== 'admin') {
                    return [
                        'success' => false,
                        'message' => 'Access denied. Only Admins are allowed.'
                    ];
                }
            }

            // Prepare JWT payload
            $issuedAt = time();
            $expirationTime = $issuedAt + (60 * 60 * 24); // valid for 24 hours

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
            $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');

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
            $decoded = JWT::decode($jwt, new Key($this->jwtSecret, 'HS256'));

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

        } catch (ExpiredException $e) {
            error_log("JWT expired: " . $e->getMessage());
            return false;
        } catch (SignatureInvalidException $e) {
            error_log("JWT signature invalid: " . $e->getMessage());
            return false;
        } catch (BeforeValidException $e) {
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
     * Check if a user has valid user or admin role based on JWT token
     * 
     * @param string $jwt The JWT token to verify
     * @return bool True if user has 'user' or 'admin' role, false otherwise
     */
    public function isUser($jwt) {
        try {
            $decoded = JWT::decode($jwt, new Key($this->jwtSecret, 'HS256'));

            if (!isset($decoded->data->user_id)) {
                return false;
            }

            $userId = $decoded->data->user_id;

            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = :user_id LIMIT 1");
            $stmt->execute(['user_id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && ($user['role'] === 'user' || $user['role'] === 'admin')) {
                return true;
            }

            return false;

        } catch (ExpiredException $e) {
            error_log("JWT expired: " . $e->getMessage());
            return false;
        } catch (SignatureInvalidException $e) {
            error_log("JWT signature invalid: " . $e->getMessage());
            return false;
        } catch (BeforeValidException $e) {
            error_log("JWT not yet valid: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("JWT verification error: " . $e->getMessage());
            return false;
        } catch (PDOException $e) {
            error_log("Database error in isUser: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user ID from JWT token
     * 
     * @param string $jwt The JWT token to decode
     * @return int|null The user ID from the token, null if token is invalid or user_id is not present
     */
    public function getUserId($jwt) {
        try {
            $decoded = JWT::decode($jwt, new Key($this->jwtSecret, 'HS256'));

            if (!isset($decoded->data->user_id)) {
                file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " | JWT does not contain user_id\n", FILE_APPEND);
                return null;
            }

            return (int)$decoded->data->user_id;

<<<<<<< Updated upstream
        } catch (ExpiredException $e) {
            error_log("JWT expired: " . $e->getMessage());
            return null;
        } catch (SignatureInvalidException $e) {
            error_log("JWT signature invalid: " . $e->getMessage());
            return null;
        } catch (BeforeValidException $e) {
            error_log("JWT not yet valid: " . $e->getMessage());
=======
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = :user_id LIMIT 1");
            $stmt->execute(['user_id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " | User with ID $userId does not exist in database\n", FILE_APPEND);
                return null;
            }

            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " | getUserId returns: $userId\n", FILE_APPEND);
            return (int)$user['user_id'];

        } catch (\Firebase\JWT\ExpiredException $e) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " | JWT expired: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " | JWT signature invalid: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        } catch (\Firebase\JWT\BeforeValidException $e) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " | JWT not yet valid: " . $e->getMessage() . "\n", FILE_APPEND);
>>>>>>> Stashed changes
            return null;
        } catch (Exception $e) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " | JWT verification error: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
<<<<<<< Updated upstream
=======
        } catch (PDOException $e) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " | Database error in getUserId: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
>>>>>>> Stashed changes
        }
    }
}
?>
