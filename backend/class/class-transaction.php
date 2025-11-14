<?php
require_once __DIR__ . '/class-db.php';

class Transaction {
    private $db;

    public function __construct() {
        $this->db = new Db();
    }

    // Create a new income or expense
    public function createTransaction($user_id, $amount, $category_id, $note = null, $date = null, $type = 'income') {
        try {
            $pdo = $this->db->getPdo();

            if (empty($amount) || $amount <= 0) {
                return ['success' => false, 'message' => 'Valid amount is required'];
            }

            if (empty($category_id)) {
                return ['success' => false, 'message' => 'Category is required'];
            }

            // Validate type
            if (!in_array($type, ['income', 'expense'])) {
                return ['success' => false, 'message' => 'Invalid type. Must be income or expense'];
            }

            // Verify category exists and user has access to it
            $stmt = $pdo->prepare("
                SELECT category_id FROM categories 
                WHERE category_id = :category_id 
                AND type = :type
                AND (user_id = :user_id OR is_predefined = 1)
            ");
            $stmt->execute(['category_id' => $category_id, 'user_id' => $user_id, 'type' => $type]);
            
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Invalid ' . $type . ' category'];
            }

            // If no date provided, use current date
            if (empty($date)) {
                $date = date('Y-m-d');
            }

            $stmt = $pdo->prepare("
                INSERT INTO transactions (user_id, amount, category_id, note, date, type)
                VALUES (:user_id, :amount, :category_id, :note, :date, :type)
            ");
            $stmt->execute([
                'user_id' => $user_id,
                'amount' => $amount,
                'category_id' => $category_id,
                'note' => $note,
                'date' => $date,
                'type' => $type
            ]);

            $transaction_id = $pdo->lastInsertId();

            return [
                'success' => true,
                'message' => ucfirst($type) . ' created successfully',
                'transaction' => [
                    'id' => $transaction_id,
                    'user_id' => $user_id,
                    'amount' => $amount,
                    'category_id' => $category_id,
                    'note' => $note,
                    'date' => $date,
                    'type' => $type
                ]
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Get all income or expenses for a user
    public function getAllTransactions($user_id, $start_date = null, $end_date = null, $category_id = null, $type = null) {
        try {
            $pdo = $this->db->getPdo();

            // Validate type if provided
            if ($type !== null && !in_array($type, ['income', 'expense'])) {
                return ['success' => false, 'message' => 'Invalid type. Must be income or expense'];
            }

            $sql = "
                SELECT 
                    t.transaction_id AS id,
                    t.amount,
                    t.note,
                    t.date,
                    t.category_id,
                    t.type,
                    c.name AS category_name,
                    c.type AS category_type
                FROM transactions t
                LEFT JOIN categories c ON t.category_id = c.category_id
                WHERE t.user_id = :user_id
            ";

            $params = ['user_id' => $user_id];

            // Filter by type if specified
            if ($type !== null) {
                $sql .= " AND t.type = :type";
                $params['type'] = $type;
            }

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

            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'transactions' => $transactions];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to load transactions: ' . $e->getMessage()];
        }
    }

    // Get a single income or expense by ID
    public function getIncomeById($transaction_id, $user_id, $type = 'income') {
        try {
            $pdo = $this->db->getPdo();

            // Validate type
            if (!in_array($type, ['income', 'expense'])) {
                return ['success' => false, 'message' => 'Invalid type. Must be income or expense'];
            }

            $stmt = $pdo->prepare("
                SELECT 
                    t.transaction_id AS id,
                    t.amount,
                    t.note,
                    t.date,
                    t.category_id,
                    t.type,
                    c.name AS category_name,
                    c.type AS category_type
                FROM transactions t
                LEFT JOIN categories c ON t.category_id = c.category_id
                WHERE t.transaction_id = :transaction_id AND t.user_id = :user_id AND t.type = :type
            ");
            $stmt->execute(['transaction_id' => $transaction_id, 'user_id' => $user_id, 'type' => $type]);

            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($transaction) {
                return ['success' => true, 'transaction' => $transaction];
            } else {
                return ['success' => false, 'message' => ucfirst($type) . ' not found'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to load transaction: ' . $e->getMessage()];
        }
    }

    // Update an income or expense
    public function updateTransaction($transaction_id, $user_id, $amount, $category_id, $note = null, $date = null, $type = 'income') {
        try {
            $pdo = $this->db->getPdo();

            if (empty($amount) || $amount <= 0) {
                return ['success' => false, 'message' => 'Valid amount is required'];
            }

            if (empty($category_id)) {
                return ['success' => false, 'message' => 'Category is required'];
            }

            // Validate type
            if (!in_array($type, ['income', 'expense'])) {
                return ['success' => false, 'message' => 'Invalid type. Must be income or expense'];
            }

            // Verify transaction belongs to user and matches type
            $stmt = $pdo->prepare("SELECT transaction_id FROM transactions WHERE transaction_id = :transaction_id AND user_id = :user_id AND type = :type");
            $stmt->execute(['transaction_id' => $transaction_id, 'user_id' => $user_id, 'type' => $type]);
            
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => ucfirst($type) . ' not found or access denied'];
            }

            // Verify category exists and user has access to it
            $stmt = $pdo->prepare("
                SELECT category_id FROM categories 
                WHERE category_id = :category_id 
                AND type = :type
                AND (user_id = :user_id OR is_predefined = 1)
            ");
            $stmt->execute(['category_id' => $category_id, 'user_id' => $user_id, 'type' => $type]);
            
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Invalid ' . $type . ' category'];
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

            return ['success' => true, 'message' => ucfirst($type) . ' updated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to update ' . $type . ': ' . $e->getMessage()];
        }
    }

    // Delete an income or expense
    public function deleteTransaction($transaction_id, $user_id, $type) {
        try {
            $pdo = $this->db->getPdo();

            // Validate type
            if (!in_array($type, ['income', 'expense'])) {
                return ['success' => false, 'message' => 'Invalid type. Must be income or expense'];
            }

            $stmt = $pdo->prepare("DELETE FROM transactions WHERE transaction_id = :transaction_id AND user_id = :user_id");
            $stmt->execute(['transaction_id' => $transaction_id, 'user_id' => $user_id]);

            return ['success' => true, 'message' => ucfirst($type) . ' deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to delete ' . $type . ': ' . $e->getMessage()];
        }
    }

    // Get total income or expenses for a user in a period
    public function getTotalIncome($user_id, $start_date = null, $end_date = null, $category_id = null, $type = 'income') {
        try {
            $pdo = $this->db->getPdo();

            // Validate type
            if (!in_array($type, ['income', 'expense'])) {
                return ['success' => false, 'message' => 'Invalid type. Must be income or expense'];
            }

            $sql = "SELECT COALESCE(SUM(amount), 0) AS total FROM transactions WHERE user_id = :user_id AND type = :type";
            $params = ['user_id' => $user_id, 'type' => $type];

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
            return ['success' => false, 'message' => 'Failed to calculate total: ' . $e->getMessage()];
        }
    }

    // Get income or expenses grouped by category
    public function getIncomeByCategory($user_id, $start_date = null, $end_date = null, $type = 'income') {
        try {
            $pdo = $this->db->getPdo();

            // Validate type
            if (!in_array($type, ['income', 'expense'])) {
                return ['success' => false, 'message' => 'Invalid type. Must be income or expense'];
            }

            $sql = "
                SELECT 
                    c.category_id,
                    c.name AS category_name,
                    c.type AS category_type,
                    COALESCE(SUM(t.amount), 0) AS total_amount,
                    COUNT(t.transaction_id) AS transaction_count
                FROM categories c
                LEFT JOIN transactions t ON c.category_id = t.category_id 
                    AND t.user_id = :user_id 
                    AND t.type = :type
            ";

            $params = ['user_id' => $user_id, 'type' => $type];

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

            $sql .= " WHERE (c.user_id = :user_id2 OR c.is_predefined = 1) AND c.type = :type2";
            $params['user_id2'] = $user_id;
            $params['type2'] = $type;

            $sql .= " GROUP BY c.category_id, c.name, c.type ORDER BY total_amount DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $transactions_by_category = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $transactions_by_category];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to load transactions by category: ' . $e->getMessage()];
        }
    }
}
?>
