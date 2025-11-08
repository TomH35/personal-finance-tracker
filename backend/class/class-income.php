<?php
require_once __DIR__ . '/class-db.php';

class Income {
    private $db;

    public function __construct() {
        $this->db = new Db();
    }

    // Create a new income
    public function createIncome($user_id, $amount, $category_id, $note = null, $date = null) {
        try {
            $pdo = $this->db->getPdo();

            if (empty($amount) || $amount <= 0) {
                return ['success' => false, 'message' => 'Valid amount is required'];
            }

            if (empty($category_id)) {
                return ['success' => false, 'message' => 'Category is required'];
            }

            // Verify category exists and user has access to it
            $stmt = $pdo->prepare("
                SELECT category_id FROM categories 
                WHERE category_id = :category_id 
                AND type = 'income'
                AND (user_id = :user_id OR is_predefined = 1)
            ");
            $stmt->execute(['category_id' => $category_id, 'user_id' => $user_id]);
            
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Invalid income category'];
            }

            // If no date provided, use current date
            if (empty($date)) {
                $date = date('Y-m-d');
            }

            $stmt = $pdo->prepare("
                INSERT INTO transactions (user_id, amount, category_id, note, date, type)
                VALUES (:user_id, :amount, :category_id, :note, :date, 'income')
            ");
            $stmt->execute([
                'user_id' => $user_id,
                'amount' => $amount,
                'category_id' => $category_id,
                'note' => $note,
                'date' => $date
            ]);

            $transaction_id = $pdo->lastInsertId();

            return [
                'success' => true,
                'message' => 'Income created successfully',
                'income' => [
                    'id' => $transaction_id,
                    'user_id' => $user_id,
                    'amount' => $amount,
                    'category_id' => $category_id,
                    'note' => $note,
                    'date' => $date,
                    'type' => 'income'
                ]
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Get all income for a user
    public function getAllIncome($user_id, $start_date = null, $end_date = null, $category_id = null) {
        try {
            $pdo = $this->db->getPdo();

            $sql = "
                SELECT 
                    t.transaction_id AS id,
                    t.amount,
                    t.note,
                    t.date,
                    t.category_id,
                    c.name AS category_name,
                    c.type AS category_type
                FROM transactions t
                LEFT JOIN categories c ON t.category_id = c.category_id
                WHERE t.user_id = :user_id AND t.type = 'income'
            ";

            $params = ['user_id' => $user_id];

            // Filter by date range
            if ($start_date) {
                $sql .= " AND t.date >= :start_date";
                $params['start_date'] = $start_date;
            }

            if ($end_date) {
                $sql .= " AND t.date <= :end_date";
                $params['end_date'] = $end_date;
            }

            // Filter by category
            if ($category_id) {
                $sql .= " AND t.category_id = :category_id";
                $params['category_id'] = $category_id;
            }

            $sql .= " ORDER BY t.date DESC, t.transaction_id DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $income = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'income' => $income];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to load income: ' . $e->getMessage()];
        }
    }

    // Get a single income by ID
    public function getIncomeById($transaction_id, $user_id) {
        try {
            $pdo = $this->db->getPdo();

            $stmt = $pdo->prepare("
                SELECT 
                    t.transaction_id AS id,
                    t.amount,
                    t.note,
                    t.date,
                    t.category_id,
                    c.name AS category_name,
                    c.type AS category_type
                FROM transactions t
                LEFT JOIN categories c ON t.category_id = c.category_id
                WHERE t.transaction_id = :transaction_id AND t.user_id = :user_id AND t.type = 'income'
            ");
            $stmt->execute(['transaction_id' => $transaction_id, 'user_id' => $user_id]);

            $income = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($income) {
                return ['success' => true, 'income' => $income];
            } else {
                return ['success' => false, 'message' => 'Income not found'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to load income: ' . $e->getMessage()];
        }
    }

    // Update an income
    public function updateIncome($transaction_id, $user_id, $amount, $category_id, $note = null, $date = null) {
        try {
            $pdo = $this->db->getPdo();

            if (empty($amount) || $amount <= 0) {
                return ['success' => false, 'message' => 'Valid amount is required'];
            }

            if (empty($category_id)) {
                return ['success' => false, 'message' => 'Category is required'];
            }

            // Verify income belongs to user
            $stmt = $pdo->prepare("SELECT transaction_id FROM transactions WHERE transaction_id = :transaction_id AND user_id = :user_id AND type = 'income'");
            $stmt->execute(['transaction_id' => $transaction_id, 'user_id' => $user_id]);
            
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Income not found or access denied'];
            }

            // Verify category exists and user has access to it
            $stmt = $pdo->prepare("
                SELECT category_id FROM categories 
                WHERE category_id = :category_id 
                AND type = 'income'
                AND (user_id = :user_id OR is_predefined = 1)
            ");
            $stmt->execute(['category_id' => $category_id, 'user_id' => $user_id]);
            
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Invalid income category'];
            }

            $stmt = $pdo->prepare("
                UPDATE transactions 
                SET amount = :amount, 
                    category_id = :category_id, 
                    note = :note, 
                    date = :date
                WHERE transaction_id = :transaction_id AND user_id = :user_id
            ");
            $stmt->execute([
                'amount' => $amount,
                'category_id' => $category_id,
                'note' => $note,
                'date' => $date,
                'transaction_id' => $transaction_id,
                'user_id' => $user_id
            ]);

            return ['success' => true, 'message' => 'Income updated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to update income: ' . $e->getMessage()];
        }
    }

    // Delete an income
    public function deleteIncome($transaction_id, $user_id) {
        try {
            $pdo = $this->db->getPdo();

            // Verify income belongs to user
            $stmt = $pdo->prepare("SELECT transaction_id FROM transactions WHERE transaction_id = :transaction_id AND user_id = :user_id AND type = 'income'");
            $stmt->execute(['transaction_id' => $transaction_id, 'user_id' => $user_id]);
            
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Income not found or access denied'];
            }

            $stmt = $pdo->prepare("DELETE FROM transactions WHERE transaction_id = :transaction_id AND user_id = :user_id");
            $stmt->execute(['transaction_id' => $transaction_id, 'user_id' => $user_id]);

            return ['success' => true, 'message' => 'Income deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to delete income: ' . $e->getMessage()];
        }
    }

    // Get total income for a user in a period
    public function getTotalIncome($user_id, $start_date = null, $end_date = null, $category_id = null) {
        try {
            $pdo = $this->db->getPdo();

            $sql = "SELECT COALESCE(SUM(amount), 0) AS total FROM transactions WHERE user_id = :user_id AND type = 'income'";
            $params = ['user_id' => $user_id];

            if ($start_date) {
                $sql .= " AND date >= :start_date";
                $params['start_date'] = $start_date;
            }

            if ($end_date) {
                $sql .= " AND date <= :end_date";
                $params['end_date'] = $end_date;
            }

            if ($category_id) {
                $sql .= " AND category_id = :category_id";
                $params['category_id'] = $category_id;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['success' => true, 'total' => $result['total']];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to calculate total income: ' . $e->getMessage()];
        }
    }

    // Get income grouped by category
    public function getIncomeByCategory($user_id, $start_date = null, $end_date = null) {
        try {
            $pdo = $this->db->getPdo();

            $sql = "
                SELECT 
                    c.category_id,
                    c.name AS category_name,
                    c.type AS category_type,
                    COALESCE(SUM(t.amount), 0) AS total_amount,
                    COUNT(t.transaction_id) AS income_count
                FROM categories c
                LEFT JOIN transactions t ON c.category_id = t.category_id 
                    AND t.user_id = :user_id 
                    AND t.type = 'income'
            ";

            $params = ['user_id' => $user_id];

            if ($start_date || $end_date) {
                $conditions = [];
                if ($start_date) {
                    $conditions[] = "t.date >= :start_date";
                    $params['start_date'] = $start_date;
                }
                if ($end_date) {
                    $conditions[] = "t.date <= :end_date";
                    $params['end_date'] = $end_date;
                }
                $sql .= " AND (" . implode(" AND ", $conditions) . ")";
            }

            $sql .= " WHERE (c.user_id = :user_id2 OR c.is_predefined = 1) AND c.type = 'income'";
            $params['user_id2'] = $user_id;

            $sql .= " GROUP BY c.category_id, c.name, c.type ORDER BY total_amount DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $income_by_category = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $income_by_category];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to load income by category: ' . $e->getMessage()];
        }
    }
}
?>
