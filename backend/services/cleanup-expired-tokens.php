<?php
/**
 * Cleanup Expired Refresh Tokens Script
 * 
 * This script deletes all expired refresh tokens from the database.
 * It should be run from the command line: php backend/services/cleanup-expired-tokens.php
 * 
 * This script can be run periodically (e.g., via cron job) to keep the database clean.
 * 
 * Example cron job (runs daily at midnight):
 * 0 0 * * * /usr/bin/php /path/to/backend/services/cleanup-expired-tokens.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../class/class-db.php';

// Check if this script is being run from command line
if (php_sapi_name() !== 'cli') {
    die("Error: This script can only be run from the command line.\n");
}

try {
    $db = new Db();
    $pdo = $db->getPdo();
    
    // Get current timestamp
    $now = date('Y-m-d H:i:s');
    
    // Count expired tokens before deletion (for reporting)
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) FROM refresh_tokens 
        WHERE expires_at < :now OR expired = 1
    ");
    $countStmt->execute(['now' => $now]);
    $expiredCount = $countStmt->fetchColumn();
    
    if ($expiredCount === 0) {
        echo "No expired refresh tokens found.\n";
        exit(0);
    }
    
    // Delete expired tokens (either past expiration date or manually marked as expired)
    $deleteStmt = $pdo->prepare("
        DELETE FROM refresh_tokens 
        WHERE expires_at < :now OR expired = 1
    ");
    $deleteStmt->execute(['now' => $now]);
    
    $deletedCount = $deleteStmt->rowCount();
    
    echo "Cleanup completed successfully.\n";
    echo "Deleted $deletedCount expired refresh token(s).\n";
    echo "Timestamp: $now\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
