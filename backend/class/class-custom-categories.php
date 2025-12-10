<?php
require_once __DIR__ . '/class-db.php';

class CustomCategories {
    private $db;

    public function __construct() {
        $this->db = new Db();
    }

    // Check if a category name exists for a specific user, optionally excluding a specific category ID
    private function categoryExists($name, $user_id, $excludeId = null) {
        try {
            $pdo = $this->db->getPdo();
            if ($excludeId) {
                $stmt = $pdo->prepare("SELECT category_id FROM categories WHERE LOWER(name) = LOWER(?) AND user_id = ? AND category_id != ?");
                $stmt->execute([$name, $user_id, $excludeId]);
            } else {
                $stmt = $pdo->prepare("SELECT category_id FROM categories WHERE LOWER(name) = LOWER(?) AND user_id = ?");
                $stmt->execute([$name, $user_id]);
            }
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Create a new custom category
    public function createCustomCategory($name, $user_id, $type = 'expense') {
        try {
            $pdo = $this->db->getPdo();

            if (empty($name)) {
                return ['success' => false, 'message' => 'Category name is required'];
            }

            if (empty($user_id)) {
                return ['success' => false, 'message' => 'User ID is required'];
            }

            if ($this->categoryExists($name, $user_id)) {
                return ['success' => false, 'message' => 'Category name already exists'];
            }

            $stmt = $pdo->prepare("
                INSERT INTO categories (name, user_id, type, is_predefined)
                VALUES (:name, :user_id, :type, :is_predefined)
            ");
            $stmt->execute([
                'name' => $name,
                'user_id' => $user_id,
                'type' => $type,
                'is_predefined' => 0
            ]);

            $category_id = $pdo->lastInsertId();

            return [
                'success' => true,
                'message' => 'Custom category created successfully',
                'category' => [
                    'id' => $category_id,
                    'name' => $name,
                    'user_id' => $user_id,
                    'type' => $type,
                    'is_predefined' => false
                ]
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Get all categories for a user (user's custom categories + predefined categories)
    public function getAllCustomCategories($user_id) {
        try {
            $pdo = $this->db->getPdo();

            if (empty($user_id)) {
                return ['success' => false, 'message' => 'User ID is required', 'categories' => []];
            }

            $stmt = $pdo->prepare("
                SELECT category_id AS id, name, type, user_id, is_predefined
                FROM categories
                WHERE user_id = :user_id OR is_predefined = 1
                ORDER BY name ASC
            ");
            $stmt->execute(['user_id' => $user_id]);

            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'categories' => $categories];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to load categories', 'categories' => []];
        }
    }

    // Get only the categories created by a specific user
    public function getUserOwnedCategories($user_id) {
        try {
            $pdo = $this->db->getPdo();

            if (empty($user_id)) {
                return ['success' => false, 'message' => 'User ID is required', 'categories' => []];
            }

            $stmt = $pdo->prepare("
                SELECT category_id AS id, name, type, user_id, is_predefined
                FROM categories
                WHERE user_id = :user_id
                ORDER BY name ASC
            ");
            $stmt->execute(['user_id' => $user_id]);

            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'categories' => $categories];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to load user categories', 'categories' => []];
        }
    }

    // Update a custom category
    public function updateCustomCategory($id, $name, $type, $user_id) {
        try {
            $pdo = $this->db->getPdo();

            if (empty($name)) {
                return ['success' => false, 'message' => 'Category name is required'];
            }

            if (empty($user_id)) {
                return ['success' => false, 'message' => 'User ID is required'];
            }

            // Check if category belongs to user
            $stmt = $pdo->prepare("SELECT user_id FROM categories WHERE category_id = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$category || $category['user_id'] != $user_id) {
                return ['success' => false, 'message' => 'Permission denied: Category does not belong to user'];
            }

            if ($this->categoryExists($name, $user_id, $id)) {
                return ['success' => false, 'message' => 'Category name already exists'];
            }

            $stmt = $pdo->prepare("UPDATE categories SET name = :name, type = :type WHERE category_id = :id AND user_id = :user_id");
            $stmt->execute(['name' => $name, 'type' => $type, 'id' => $id, 'user_id' => $user_id]);

            return ['success' => true, 'message' => 'Custom category updated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to update category: ' . $e->getMessage()];
        }
    }

    // Delete a custom category
    public function deleteCustomCategory($id, $user_id) {
        try {
            $pdo = $this->db->getPdo();

            if (empty($user_id)) {
                return ['success' => false, 'message' => 'User ID is required'];
            }

            // Check if category belongs to user
            $stmt = $pdo->prepare("SELECT user_id FROM categories WHERE category_id = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$category || $category['user_id'] != $user_id) {
                return ['success' => false, 'message' => 'Permission denied: Category does not belong to user'];
            }

            $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = :id AND user_id = :user_id");
            $stmt->execute(['id' => $id, 'user_id' => $user_id]);

            return ['success' => true, 'message' => 'Custom category deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to delete category: ' . $e->getMessage()];
        }
    }
}
?>
