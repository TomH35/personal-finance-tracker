<?php
/**
 * Admin Creation Script
 *
 * This script waits for MySQL, then creates the first admin user
 * from credentials stored in `.env`.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../class/class-auth.php';
require_once __DIR__ . '/../class/class-db.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Check if this script is being run from command line
if (php_sapi_name() !== 'cli') {
    die("Error: This script can only be run from the command line.\n");
}

// Check required environment variables
$requiredEnvVars = ['ADMIN_USERNAME', 'ADMIN_EMAIL', 'ADMIN_PASSWORD'];
foreach ($requiredEnvVars as $var) {
    if (empty($_ENV[$var])) {
        die("Error: Missing required environment variable: $var\n");
    }
}

$username = $_ENV['ADMIN_USERNAME'];
$email = $_ENV['ADMIN_EMAIL'];
$password = $_ENV['ADMIN_PASSWORD'];

// Wait for MySQL to be ready
$maxAttempts = 60; // ~60 seconds
$attempt = 1;

echo "Waiting for MySQL to be ready...\n";

while ($attempt <= $maxAttempts) {
    try {
        $db = new Db();
        $pdo = $db->getPdo();
        echo "MySQL is ready!\n";
        break;
    } catch (Exception $e) {
        echo "Attempt $attempt/$maxAttempts failed: " . $e->getMessage() . "\n";
        sleep(1);
        $attempt++;
    }
}

if ($attempt > $maxAttempts) {
    die("Error: MySQL did not become ready in time.\n");
}

try {
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    $adminCount = $stmt->fetchColumn();

    if ($adminCount > 0) {
        die("Admin user already exists. Skipping creation.\n");
    }

    // Create the admin user
    $auth = new Auth();
    $result = $auth->registerUser($username, $email, $password, 'admin');

    if ($result['success']) {
        echo "Admin user created successfully.\n";
        echo "Username: $username\n";
        echo "Email: $email\n";
    } else {
        echo "Error: " . $result['message'] . "\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>