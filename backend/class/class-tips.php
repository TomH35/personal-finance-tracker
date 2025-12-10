<?php
require_once __DIR__ . '/class-db.php';

class Tips {
    private $db;

    public function __construct() {
        $this->db = new Db();
    }

    // Create a new tip
    public function createTip($title, $content) {
        try {
            $pdo = $this->db->getPdo();

            if (empty($title)) {
                return ['success' => false, 'message' => 'Tip title is required'];
            }

            if (empty($content)) {
                return ['success' => false, 'message' => 'Tip content is required'];
            }

            $stmt = $pdo->prepare("
                INSERT INTO tips (title, content)
                VALUES (:title, :content)
            ");
            $stmt->execute([
                'title' => $title,
                'content' => $content
            ]);

            $tip_id = $pdo->lastInsertId();

            return [
                'success' => true,
                'message' => 'Tip created successfully',
                'tip' => [
                    'id' => $tip_id,
                    'title' => $title,
                    'content' => $content
                ]
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Get all tips
    public function getTips() {
        try {
            $pdo = $this->db->getPdo();

            $stmt = $pdo->query("
                SELECT tip_id AS id, title, content, created_at, updated_at
                FROM tips
                ORDER BY created_at DESC
            ");

            $tips = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'tips' => $tips];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to load tips: ' . $e->getMessage()];
        }
    }

    // Get a single tip by ID
    public function getTipById($id) {
        try {
            $pdo = $this->db->getPdo();

            $stmt = $pdo->prepare("
                SELECT tip_id AS id, title, content, created_at, updated_at
                FROM tips
                WHERE tip_id = :id
            ");
            $stmt->execute(['id' => $id]);

            $tip = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($tip) {
                return ['success' => true, 'tip' => $tip];
            } else {
                return ['success' => false, 'message' => 'Tip not found'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to load tip: ' . $e->getMessage()];
        }
    }

    // Update a tip
    public function updateTip($id, $title, $content) {
        try {
            $pdo = $this->db->getPdo();

            if (empty($title)) {
                return ['success' => false, 'message' => 'Tip title is required'];
            }

            if (empty($content)) {
                return ['success' => false, 'message' => 'Tip content is required'];
            }

            $stmt = $pdo->prepare("
                UPDATE tips 
                SET title = :title, content = :content 
                WHERE tip_id = :id
            ");
            $stmt->execute([
                'title' => $title,
                'content' => $content,
                'id' => $id
            ]);

            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Tip updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Tip not found or no changes made'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to update tip: ' . $e->getMessage()];
        }
    }

    // Delete a tip
    public function deleteTip($id) {
        try {
            $pdo = $this->db->getPdo();

            $stmt = $pdo->prepare("DELETE FROM tips WHERE tip_id = :id");
            $stmt->execute(['id' => $id]);

            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Tip deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Tip not found'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to delete tip: ' . $e->getMessage()];
        }
    }
}
?>
