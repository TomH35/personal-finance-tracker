<?php
require_once __DIR__ . '/class-db.php';

try {
    $db = new Db();
    $pdo = $db->getPdo();

    $stmt = $pdo->prepare("DELETE FROM rate_limits");
    $stmt->execute();

    echo "[" . date('Y-m-d H:i:s') . "] Rate limits table cleared.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
