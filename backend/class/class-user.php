<?php
require_once __DIR__ . '/class-db.php';

class User
{
    private $db;
    private $imageDir;

    public function __construct()
    {
        $database = new Db();
        $this->db = $database->getPdo();

        // path to images
        $this->imageDir = __DIR__ . '/../public/user-images/';
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

    public function uploadProfilePicture($user_id, $file)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => 'Upload error'
            ];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allowed)) {
            return [
                'success' => false,
                'message' => 'Invalid file type'
            ];
        }

        if (!is_dir($this->imageDir)) {
            mkdir($this->imageDir, 0755, true);
        }

        // odstránenie starého obrázka
        $this->deleteProfilePicture($user_id);

        $fileName = "user_{$user_id}.{$ext}";
        $fullPath = $this->imageDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            return [
                'success' => true,
                'fileName' => $fileName,
                'url' => '/public/user-images/' . $fileName
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to save image'
        ];
    }

    public function deleteProfilePicture($user_id)
    {
        $extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $deleted = false;

        foreach ($extensions as $ext) {
            $path = $this->imageDir . "user_{$user_id}.{$ext}";
            if (file_exists($path)) {
                unlink($path);
                $deleted = true;
            }
        }

        return [
            'success' => $deleted,
            'message' => $deleted ? 'Picture deleted' : 'No picture found'
        ];
    }

    public function getProfilePicture($user_id)
    {
        $extensions = ['jpg', 'jpeg', 'png', 'gif'];

        foreach ($extensions as $ext) {
            $path = $this->imageDir . "user_{$user_id}.{$ext}";
            if (file_exists($path)) {
                return [
                    'success' => true,
                    'exists' => true,
                    'url' => '/public/user-images/user_' . $user_id . '.' . $ext
                ];
            }
        }

        return [
            'success' => true,
            'exists' => false,
            'url' => null
        ];
    }
}
