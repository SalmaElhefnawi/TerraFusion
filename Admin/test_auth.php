<?php
require_once 'app/Controllers/AuthController.php';
require_once 'app/Models/Database.php';
require_once 'app/Helpers/Security.php';
require_once 'app/Helpers/Session.php';

use App\Controllers\AuthController;

// Test login with manager credentials
echo "Testing login functionality...\n\n";

// Test 1: Valid login
echo "Test 1: Valid login (manager/password123)\n";
$result = AuthController::login('manager', 'password123');
echo "Result: " . ($result['success'] ? "SUCCESS" : "FAILED") . "\n";
if (!$result['success']) {
    echo "Error: " . $result['message'] . "\n";
} else {
    echo "User logged in: {$result['user']['username']} (Role: {$result['user']['role']})\n";
}

// Test 2: Invalid login
echo "\nTest 2: Invalid login (wrong/password)\n";
$result = AuthController::login('wrong', 'password');
echo "Result: " . ($result['success'] ? "SUCCESS" : "FAILED") . "\n";
if (!$result['success']) {
    echo "Error: " . $result['message'] . "\n";
}

// Test 3: Check if logged in
echo "\nTest 3: Check login status\n";
$loggedIn = AuthController::isLoggedIn();
echo "Is logged in: " . ($loggedIn ? "YES" : "NO") . "\n";

echo "\nAuthentication tests completed!\n";
?>
