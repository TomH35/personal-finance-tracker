<?php
require_once __DIR__ . '/class-db.php';

class Limits {
    public $db;

    public function __construct() {
        $this->db = new Db();
    }

    // Get all users limits or specific month/year
    public function getLimit($user_id, $month = null, $year = null) {
        try {
            $pdo = $this->db->getPdo();
            $sql = "SELECT * FROM spending_limits WHERE user_id = :user_id";
            $params = ['user_id' => $user_id];

            if ($month) { $sql .= " AND month = :month"; $params['month'] = $month; }
            if ($year)  { $sql .= " AND year = :year";   $params['year'] = $year; }

            $sql .= " ORDER BY year DESC, month DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $limits = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'limit' => $limits];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to load limits'];
        }
    }

    // Set or update users limit for a month/year
    public function setLimit($user_id, $month, $year, $warning_limit, $critical_limit) {
        try {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT limit_id FROM spending_limits WHERE user_id = :user_id AND month = :month AND year = :year");
            $stmt->execute(['user_id'=>$user_id,'month'=>$month,'year'=>$year]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $stmt = $pdo->prepare("UPDATE spending_limits SET warning_limit = :warning, critical_limit = :critical WHERE limit_id = :limit_id");
                $stmt->execute(['warning'=>$warning_limit,'critical'=>$critical_limit,'limit_id'=>$existing['limit_id']]);
                return ['success'=>true,'message'=>'Limit updated'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO spending_limits (user_id, month, year, warning_limit, critical_limit) VALUES (:user_id, :month, :year, :warning, :critical)");
                $stmt->execute(['user_id'=>$user_id,'month'=>$month,'year'=>$year,'warning'=>$warning_limit,'critical'=>$critical_limit]);
                return ['success'=>true,'message'=>'Limit created'];
            }
        } catch (PDOException $e) {
            return ['success'=>false,'message'=>'Database error: '.$e->getMessage()];
        }
    }

    // Edit existing limit by ID (month, year, warning, critical)
    public function editLimit($user_id, $limit_id, $month, $year, $warning_limit, $critical_limit) {
        try {
            $pdo = $this->db->getPdo();
            // Ensure the limit belongs to the user
            $stmtCheck = $pdo->prepare("SELECT * FROM spending_limits WHERE limit_id = :limit_id AND user_id = :user_id");
            $stmtCheck->execute(['limit_id'=>$limit_id,'user_id'=>$user_id]);
            $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if (!$existing) return ['success'=>false,'message'=>'Limit not found'];

            $stmt = $pdo->prepare("UPDATE spending_limits SET month = :month, year = :year, warning_limit = :warning, critical_limit = :critical WHERE limit_id = :limit_id");
            $stmt->execute([
                'month'=>$month,
                'year'=>$year,
                'warning'=>$warning_limit,
                'critical'=>$critical_limit,
                'limit_id'=>$limit_id
            ]);

            return ['success'=>true,'message'=>'Limit updated successfully'];
        } catch (PDOException $e) {
            return ['success'=>false,'message'=>'Database error: '.$e->getMessage()];
        }
    }

    // Delete a limit by ID
    public function deleteLimit($limit_id) {
        try {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("DELETE FROM spending_limits WHERE limit_id = :limit_id");
            $stmt->execute(['limit_id'=>$limit_id]);
            return ['success'=>true,'message'=>'Limit deleted'];
        } catch (PDOException $e) {
            return ['success'=>false,'message'=>'Failed to delete limit'];
        }
    }
}
?>
