<?php
require_once __DIR__ . '/class-db.php';

class Limits {
    public $db;

    public function __construct() {
        $this->db = new Db();
    }

    // Get all user's limits
    public function getLimit($user_id) {
        try {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM spending_limits WHERE user_id = :user_id ORDER BY limit_id DESC");
            $stmt->execute(['user_id' => $user_id]);
            $limits = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'limit' => $limits];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to load limits'];
        }
    }

    // Set new limit
    public function setLimit($user_id, $warning_limit, $critical_limit, $enabled = 1) {
        try {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("INSERT INTO spending_limits (user_id, warning_limit, critical_limit, enabled) VALUES (:user_id, :warning, :critical, :enabled)");
            $stmt->execute([
                'user_id' => $user_id,
                'warning' => $warning_limit,
                'critical' => $critical_limit,
                'enabled' => $enabled
            ]);
            return ['success' => true, 'message' => 'Limit created'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Edit existing limit
    public function editLimit($user_id, $limit_id, $warning_limit, $critical_limit, $enabled = 1) {
        try {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("UPDATE spending_limits SET warning_limit = :warning, critical_limit = :critical, enabled = :enabled WHERE limit_id = :limit_id AND user_id = :user_id");
            $stmt->execute([
                'warning' => $warning_limit,
                'critical' => $critical_limit,
                'enabled' => $enabled,
                'limit_id' => $limit_id,
                'user_id' => $user_id
            ]);

            return ['success' => true, 'message' => 'Limit updated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Delete a limit
    public function deleteLimit($limit_id) {
        try {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("DELETE FROM spending_limits WHERE limit_id = :limit_id");
            $stmt->execute(['limit_id' => $limit_id]);
            return ['success' => true, 'message' => 'Limit deleted'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to delete limit'];
        }
    }

    // Toggle a limit
    public function toggleLimit($user_id, $limit_id, $enabled) {
        try {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("UPDATE spending_limits SET enabled = :enabled WHERE limit_id = :limit_id AND user_id = :user_id");
            $stmt->execute(['enabled' => $enabled ? 1 : 0, 'limit_id' => $limit_id, 'user_id' => $user_id]);

            return ['success' => true, 'message' => 'Limit toggled'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
?>
