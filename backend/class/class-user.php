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
}
