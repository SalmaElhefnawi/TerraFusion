<?php
// This connects to your database
require_once 'config.php'; 

// The temporary password everyone will use
$default_password = 'password123';
$hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

try {
    // By removing the WHERE clause, this updates the password for EVERY user
    $sql = "UPDATE users SET password_hash = :hash";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['hash' => $hashed_password]);

    echo "<h1>Success!</h1>";
    echo "The passwords for ALL users (including customers) have been reset to: <b>" . $default_password . "</b>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>