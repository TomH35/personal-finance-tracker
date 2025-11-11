<?php
require_once __DIR__ . '/class-db.php';

class Categories {
    private $db;

    public function __construct() {
        $this->db = new Db();
    }

    // Check if a category name exists, optionally excluding a specific category ID
    public function categoryExists($name, $excludeId = null) {
        try {
            $pdo = $this->db->getPdo();
            if ($excludeId) {
                $stmt = $pdo->prepare("SELECT category_id FROM categories WHERE LOWER(name) = LOWER(?) AND category_id != ?");
                $stmt->execute([$name, $excludeId]);
            } else {
                $stmt = $pdo->prepare("SELECT category_id FROM categories WHERE LOWER(name) = LOWER(?)");
                $stmt->execute([$name]);
            }
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Create a new category
    public function createCategory($name, $user_id = null, $type = 'expense', $is_predefined = false) {
        try {
            $pdo = $this->db->getPdo();


            if (empty($name)) {
                return ['success' => false, 'message' => 'Category name is required'];
            }

            if ($this->categoryExists($name)) {
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
                'is_predefined' => $is_predefined ? 1 : 0
            ]);

            $category_id = $pdo->lastInsertId();

            return [
                'success' => true,
                'message' => 'Category created successfully',
                'category' => [
                    'id' => $category_id,
                    'name' => $name,
                    'user_id' => $user_id,
                    'type' => $type,
                    'is_predefined' => $is_predefined
                ]
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Get all categories
    // If user_id is provided, return users own categories + predefined (global) ones
    public function getAllCategories($user_id = null) {
        try {
            $pdo = $this->db->getPdo();

            if ($user_id) {
                $stmt = $pdo->prepare("
                    SELECT category_id AS id, name, type, user_id, is_predefined
                    FROM categories
                    WHERE user_id = :user_id OR is_predefined = 1
                    ORDER BY name ASC
                ");
                $stmt->execute(['user_id' => $user_id]);
            } else {
                // If called without user_id (admin), return all categories
                $stmt = $pdo->query("
                    SELECT category_id AS id, name, type, user_id, is_predefined
                    FROM categories
                    ORDER BY name ASC
                ");
            }

            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'categories' => $categories];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to load categories'];
        }
    }

    // Update a category
    public function updateCategory($id, $name, $type) {
        try {
            $pdo = $this->db->getPdo();

            if (empty($name)) {
                return ['success' => false, 'message' => 'Category name is required'];
            }

            if ($this->categoryExists($name, $id)) {
                return ['success' => false, 'message' => 'Category name already exists'];
            }

            $stmt = $pdo->prepare("UPDATE categories SET name = :name, type = :type WHERE category_id = :id");
            $stmt->execute(['name' => $name, 'type' => $type, 'id' => $id]);

            return ['success' => true, 'message' => 'Category updated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to update category: ' . $e->getMessage()];
        }
    }

    // Delete a category
    public function deleteCategory($id) {
        try {
            $pdo = $this->db->getPdo();

            $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = :id");
            $stmt->execute(['id' => $id]);

            return ['success' => true, 'message' => 'Category deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to delete category: ' . $e->getMessage()];
        }
    }
}
?>
