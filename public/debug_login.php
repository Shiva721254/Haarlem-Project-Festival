<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Repositories\UserRepository;

echo "<h2>Debug Login Test</h2>";
echo "<pre>";

// Test Database Configuration
echo "=== Database Configuration ===\n";
echo "HOST: " . Database::HOST . "\n";
echo "PORT: " . Database::PORT . "\n";
echo "NAME: " . Database::NAME . "\n";
echo "USER: " . Database::USER . "\n";
echo "DSN: mysql:host=" . Database::HOST . ";port=" . Database::PORT . ";dbname=" . Database::NAME . "\n\n";

try {
    $repo = new UserRepository();
    echo "✅ Repository created successfully\n\n";
    
    $user = $repo->findByEmail('admin@haarlemfestival.nl');
    
    if ($user) {
        echo "✅ User Found!\n";
        echo "Email: " . htmlspecialchars($user['email']) . "\n";
        echo "Password Hash: " . htmlspecialchars($user['password_hash']) . "\n";
        echo "Role: " . htmlspecialchars($user['role']) . "\n";
        
        echo "\n--- Testing password_verify ---\n";
        $testPassword = 'Admin123!';
        $verified = password_verify($testPassword, $user['password_hash']);
        echo "Testing password: '$testPassword'\n";
        echo "Result: " . ($verified ? '✅ VERIFIED' : '❌ FAILED') . "\n";
    } else {
        echo "❌ User NOT FOUND!\n";
        echo "Checking manual query...\n\n";
        
        // Try manual PDO connection
        $dsn = 'mysql:host=' . Database::HOST . ';port=' . Database::PORT . ';dbname=' . Database::NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, Database::USER, Database::PASSWORD);
        $stmt = $pdo->prepare('SELECT id, email, password_hash, role FROM users WHERE email = ?');
        $stmt->execute(['admin@haarlemfestival.nl']);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo "✅ Manual query FOUND user!\n";
            var_dump($result);
        } else {
            echo "❌ Manual query also NOT FOUND!\n";
            echo "Checking if users table exists...\n";
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            echo "Tables: " . implode(', ', $tables) . "\n\n";
            
            echo "=== ALL USERS IN DATABASE ===\n";
            $allUsers = $pdo->query("SELECT id, email, password_hash, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
            if (empty($allUsers)) {
                echo "❌ NO USERS FOUND IN DATABASE!\n";
            } else {
                echo "Found " . count($allUsers) . " user(s):\n";
                foreach ($allUsers as $u) {
                    echo "  - ID: " . $u['id'] . ", Email: " . $u['email'] . ", Role: " . $u['role'] . "\n";
                }
            }
        }
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
}

echo "</pre>";

