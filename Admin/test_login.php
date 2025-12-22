<?php
require_once 'app/Models/Database.php';
require_once 'app/Helpers/Security.php';
require_once 'app/Helpers/Session.php';

use App\Models\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    // Show existing users with correct column names
    $stmt = $db->query("SELECT user_id, username, role, created_at FROM users");
    $users = $stmt->fetchAll();
    
    echo "Existing users:\n";
    foreach ($users as $user) {
        echo "- ID: {$user['user_id']}, Username: {$user['username']}, Role: {$user['role']}\n";
    }
    
    echo "\nDatabase connection successful!\n";
    echo "You can now login with:\n";
    echo "- Username: manager, Password: password123\n";
    echo "- Username: chef, Password: password123\n";
    echo "- Username: tablemanager, Password: password123\n";
    echo "- Username: waiter, Password: password123\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
