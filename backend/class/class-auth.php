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
    private $refreshTokenSecret;

    public function __construct() {
        $this->db = new Db();
        
        // Load JWT secret from .env
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
        $this->jwtSecret = $_ENV['JWT_SECRET'];
        $this->refreshTokenSecret = $_ENV['REFRESH_TOKEN_SECRET'];
    }

    /**
     * Check if password meets security requirements
     * 
     * Requirements:
     * - At least 8 characters long
     * - At least one uppercase letter (A-Z)
     * - At least one lowercase letter (a-z)
     * - At least one number (0-9)
     * - At least one special character (!@#$%^&*()_+-=[]{}|;:,.<>?)
     * 
     * @param string $password The password to validate
     * @return array Response array with success status and message
     */
    public function checkPasswordRequirements($password) {
        $errors = [];

        // Check minimum length
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        // Check for at least one number
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        // Check for at least one special character
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $password)) {
            $errors[] = 'Password must contain at least one special character (!@#$%^&*()_+-=[]{}|;:,.<>?)';
        }

        if (count($errors) > 0) {
            return [
                'success' => false,
                'message' => implode('. ', $errors),
                'errors' => $errors
            ];
        }

        return [
            'success' => true,
            'message' => 'Password meets all requirements'
        ];
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

            // Validate password requirements
            $passwordCheck = $this->checkPasswordRequirements($password);
            if (!$passwordCheck['success']) {
                return $passwordCheck;
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
            $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');

            // Generate refresh token
            $refreshTokenResult = $this->generateRefreshToken($user['user_id']);
            
            if (!$refreshTokenResult['success']) {
                error_log("Failed to generate refresh token: " . ($refreshTokenResult['message'] ?? 'Unknown error'));
                // Still return success but without refresh token
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
            }

            return [
                'success' => true,
                'message' => 'Login successful',
                'token' => $jwt,
                'refresh_token' => $refreshTokenResult['token'],
                'refresh_token_expires_at' => $refreshTokenResult['expires_at'],
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
     * Log in a user temporary
     * 
     * @param string $identifier The user's email or username
     * @param string $password The plain text password
     * @param string $expectedRole The expected role ('admin' or 'user')
     * @return array Response array with success status, message, and token if successful
     */
    public function temporaryUserLogin($identifier, $password, $expectedRole = 'user') {
        try {
            $pdo = $this->db->getPdo();

            // Validate input
            if (empty($identifier) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'Identifier and password are required'
                ];
            }

            // Find user
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
            $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');

            return [
                'success' => true,
                'message' => 'Temporary login successful',
                'token'   => $jwt,
                'user'    => [
                    'user_id' => $user['user_id'],
                    'username'=> $user['username'],
                    'email'   => $user['email'],
                    'role'    => $user['role']
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

            if (!isset($decoded->data->role)) {
                return false;
            }

            return $decoded->data->role === 'admin';

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

            if (!isset($decoded->data->role)) {
                return false;
            }

            $role = $decoded->data->role;
            return $role === 'user' || $role === 'admin';

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
        }
    }

    /**
     * Generate a refresh token for a user
     * 
     * @param int $userId The user ID
     * @return array Response array with success status, token, and expiration
     */
    public function generateRefreshToken($userId) {
        try {
            $pdo = $this->db->getPdo();

            // Get user data
            $stmt = $pdo->prepare("SELECT user_id, email, username, role FROM users WHERE user_id = :user_id LIMIT 1");
            $stmt->execute(['user_id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            // Prepare refresh token payload (valid for 7 days)
            $issuedAt = time();
            $expirationTime = $issuedAt + (60 * 60 * 24 * 7); // 7 days
            $expiresAt = date('Y-m-d H:i:s', $expirationTime);

            $payload = [
                'iat' => $issuedAt,
                'exp' => $expirationTime,
                'iss' => 'PFT',
                'type' => 'refresh',
                'data' => [
                    'user_id' => $user['user_id'],
                    'email' => $user['email'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ]
            ];

            // Generate refresh token
            $refreshToken = JWT::encode($payload, $this->refreshTokenSecret, 'HS256');

            // Store in database
            $stmt = $pdo->prepare("
                INSERT INTO refresh_tokens (user_id, token, expires_at) 
                VALUES (:user_id, :token, :expires_at)
            ");
            $stmt->execute([
                'user_id' => $userId,
                'token' => $refreshToken,
                'expires_at' => $expiresAt
            ]);

            return [
                'success' => true,
                'token' => $refreshToken,
                'expires_at' => $expiresAt
            ];

        } catch (PDOException $e) {
            error_log("Refresh token generation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to generate refresh token'
            ];
        } catch (Exception $e) {
            error_log("JWT error in refresh token: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Token generation failed'
            ];
        }
    }

    /**
     * Validate a refresh token and return user data
     * 
     * @param string $refreshToken The refresh token to validate
     * @return array Response array with success status and user data or error message
     */
    public function validateRefreshToken($refreshToken) {
        try {
            // Decode and verify the token
            $decoded = JWT::decode($refreshToken, new Key($this->refreshTokenSecret, 'HS256'));

            // Check if it's a refresh token
            if (!isset($decoded->type) || $decoded->type !== 'refresh') {
                return [
                    'success' => false,
                    'message' => 'Invalid token type'
                ];
            }

            if (!isset($decoded->data->user_id)) {
                return [
                    'success' => false,
                    'message' => 'Invalid token payload'
                ];
            }

            $userId = $decoded->data->user_id;
            $pdo = $this->db->getPdo();

            // Check if token exists in database and is not expired/revoked
            $stmt = $pdo->prepare("
                SELECT token_id, user_id, expires_at, expired 
                FROM refresh_tokens 
                WHERE token = :token AND user_id = :user_id 
                LIMIT 1
            ");
            $stmt->execute([
                'token' => $refreshToken,
                'user_id' => $userId
            ]);
            $tokenRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tokenRecord) {
                return [
                    'success' => false,
                    'message' => 'Token not found'
                ];
            }

            // Check if token is marked as expired (logged out)
            if ($tokenRecord['expired'] == 1) {
                return [
                    'success' => false,
                    'message' => 'Token has been revoked'
                ];
            }

            // Check if token is past expiration date
            if (strtotime($tokenRecord['expires_at']) < time()) {
                // Mark as expired and delete
                $this->deleteRefreshToken($refreshToken);
                return [
                    'success' => false,
                    'message' => 'Token has expired'
                ];
            }

            // Get current user data
            $stmt = $pdo->prepare("SELECT user_id, email, username, role FROM users WHERE user_id = :user_id LIMIT 1");
            $stmt->execute(['user_id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            return [
                'success' => true,
                'user' => $user,
                'token_id' => $tokenRecord['token_id']
            ];

        } catch (ExpiredException $e) {
            error_log("Refresh token expired: " . $e->getMessage());
            // Clean up the expired token from database
            $this->deleteRefreshToken($refreshToken);
            return [
                'success' => false,
                'message' => 'Refresh token has expired'
            ];
        } catch (SignatureInvalidException $e) {
            error_log("Refresh token signature invalid: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Invalid refresh token'
            ];
        } catch (BeforeValidException $e) {
            error_log("Refresh token not yet valid: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Token not yet valid'
            ];
        } catch (Exception $e) {
            error_log("Refresh token validation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Token validation failed'
            ];
        }
    }

    /**
     * Revoke a refresh token (mark as expired)
     * 
     * @param string $refreshToken The refresh token to revoke
     * @return array Response array with success status
     */
    public function revokeRefreshToken($refreshToken) {
        try {
            $pdo = $this->db->getPdo();

            // Mark token as expired
            $stmt = $pdo->prepare("UPDATE refresh_tokens SET expired = 1 WHERE token = :token");
            $stmt->execute(['token' => $refreshToken]);

            // Delete the token (as per user's requirement: delete when expired/logged out)
            $this->deleteRefreshToken($refreshToken);

            return [
                'success' => true,
                'message' => 'Token revoked successfully'
            ];

        } catch (PDOException $e) {
            error_log("Token revocation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to revoke token'
            ];
        }
    }

    /**
     * Delete a refresh token from database
     * 
     * @param string $refreshToken The refresh token to delete
     * @return bool True on success, false on failure
     */
    private function deleteRefreshToken($refreshToken) {
        try {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("DELETE FROM refresh_tokens WHERE token = :token");
            $stmt->execute(['token' => $refreshToken]);
            return true;
        } catch (PDOException $e) {
            error_log("Token deletion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Revoke all refresh tokens for a user (e.g., when changing password)
     * 
     * @param int $userId The user ID
     * @return array Response array with success status
     */
    public function revokeAllUserRefreshTokens($userId) {
        try {
            $pdo = $this->db->getPdo();

            // Delete all tokens for this user
            $stmt = $pdo->prepare("DELETE FROM refresh_tokens WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);

            return [
                'success' => true,
                'message' => 'All tokens revoked successfully'
            ];

        } catch (PDOException $e) {
            error_log("Token revocation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to revoke tokens'
            ];
        }
    }

    /**
     * Refresh access token using a valid refresh token
     * 
     * @param string $refreshToken The refresh token
     * @return array Response array with new access token or error
     */
    public function refreshAccessToken($refreshToken) {
        // Validate the refresh token
        $validation = $this->validateRefreshToken($refreshToken);
        
        if (!$validation['success']) {
            return $validation;
        }

        $user = $validation['user'];

        // Generate new access token
        $issuedAt = time();
        $expirationTime = $issuedAt + (60 * 60); // 1 hour

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

        try {
            $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');

            return [
                'success' => true,
                'message' => 'Token refreshed successfully',
                'token' => $jwt,
                'user' => [
                    'user_id' => $user['user_id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ];
        } catch (Exception $e) {
            error_log("JWT generation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to generate new access token'
            ];
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
                error_log("JWT does not contain user_id");
                return null;
            }

            return (int)$decoded->data->user_id;

        } catch (ExpiredException $e) {
            error_log("JWT expired: " . $e->getMessage());
            return null;
        } catch (SignatureInvalidException $e) {
            error_log("JWT signature invalid: " . $e->getMessage());
            return null;
        } catch (BeforeValidException $e) {
            error_log("JWT not yet valid: " . $e->getMessage());
            return null;
        } catch (Exception $e) {
            error_log("JWT verification error: " . $e->getMessage());
            return null;
        }
    }
}
?>
