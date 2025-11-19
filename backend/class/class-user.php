<?php
require_once __DIR__ . '/class-db.php';

class User
{
    private $db;

    public function __construct()
    {
        $database = new Db();
        $this->db = $database->getPdo();
    }

    public function getAllUsers()
    {
        try {
            $stmt = $this->db->prepare("SELECT user_id, username, email, role, created_at FROM users");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'users' => $users
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve users: ' . $e->getMessage()
            ];
        }
    }

    public function deleteUser($user_id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);

            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'User not found or already deleted'
                ];
            }

            return [
                'success' => true,
                'message' => 'User deleted successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ];
        }
    }

    public function updateUser($user_id, $username, $email, $role)
    {
        try {
            // Basic input validation
            if (empty($username) || empty($email) || !in_array($role, ['user', 'admin'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid input'
                ];
            }

            $stmt = $this->db->prepare("
                UPDATE users
                SET username = :username, email = :email, role = :role
                WHERE user_id = :user_id
            ");
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'role' => $role,
                'user_id' => $user_id
            ]);

            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'User not found or no changes made'
                ];
            }

            return [
                'success' => true,
                'message' => 'User updated successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ];
        }
    }

    public function getUserProfile($user_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT user_id, username, email, role, currency FROM users WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            return [
                'success' => true,
                'user' => $user
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve user profile: ' . $e->getMessage()
            ];
        }
    }

    public function updateUserProfile($user_id, $username, $email, $currency)
    {
        try {
            // Basic input validation
            if (empty($username) || empty($email)) {
                return [
                    'success' => false,
                    'message' => 'Username and email are required'
                ];
            }

            // Validate currency code
            $validCurrencies = ['USD', 'EUR', 'PLN', 'CZK'];
            if (!in_array($currency, $validCurrencies)) {
                return [
                    'success' => false,
                    'message' => 'Invalid currency code'
                ];
            }

            $stmt = $this->db->prepare("
                UPDATE users
                SET username = :username, email = :email, currency = :currency
                WHERE user_id = :user_id
            ");
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'currency' => $currency,
                'user_id' => $user_id
            ]);

            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'User not found or no changes made'
                ];
            }

            return [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ];
        }
    }

    public function deleteOwnAccount($user_id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);

            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            return [
                'success' => true,
                'message' => 'Account deleted successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete account: ' . $e->getMessage()
            ];
        }
    }
}
