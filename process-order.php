<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
require_once 'cart_functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_id = getOrCreateCart($user_id);
$cartItems = getCartItems($cart_id);
$total_amount = getCartTotal($cart_id) * 1.1 + 5.0; // Subtotal + 10% tax + 5 EGP fee

if (empty($cartItems)) {
    header('Location: menu.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // 0. served_by_fk logic
    $served_by_fk = $_SESSION['user_id']; // Since it's self-ordering or logged-in user

    // 1. Validate Stock & Calculate Total (Live Price)
    $stmtStock = $pdo->prepare("SELECT quantity, availability, price, meal_name FROM meals WHERE meal_id = ? FOR UPDATE");
    $stmtUpdateStock = $pdo->prepare("UPDATE meals SET quantity = quantity - ? WHERE meal_id = ?");
    
    $calculated_total = 0;
    $finalItems = [];

    foreach ($cartItems as $item) {
        $stmtStock->execute([$item['meal_id']]);
        $meal = $stmtStock->fetch(PDO::FETCH_ASSOC);

        if (!$meal) {
            throw new Exception("Item '{$item['meal_name']}' not found.");
        }
        
        // Availability Check
        if ($meal['availability'] === 'Out of Stock') {
            throw new Exception("Item '{$meal['meal_name']}' is Out of Stock.");
        }

        // Quantity Check
        if ($meal['quantity'] < $item['quantity']) {
            throw new Exception("Not enough stock for '{$meal['meal_name']}'. Only {$meal['quantity']} left.");
        }

        $price_at_sale = $meal['price'];
        $calculated_total += $price_at_sale * $item['quantity'];
        
        $finalItems[] = [
            'id' => $item['meal_id'],
            'qty' => $item['quantity'],
            'price' => $price_at_sale
        ];
    }
    
    // Add Tax/Fee logic matching existing logic
    $total_amount_final = $calculated_total * 1.1 + 5.0;

    // 2. Insert into orders table
    $stmt = $pdo->prepare("
        INSERT INTO orders (customer_name, table_number, total_amount, status, order_date, served_by_fk) 
        VALUES (?, ?, ?, 'Pending', NOW(), ?)
    ");
    
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $customerName = trim($firstName . ' ' . $lastName) ?: ($_SESSION['full_name'] ?? $_SESSION['user_name'] ?? 'Guest');
    $tableNumber = isset($_POST['table_number']) ? (int)$_POST['table_number'] : NULL;

    $stmt->execute([$customerName, $tableNumber, $total_amount_final, $served_by_fk]);
    $order_id = $pdo->lastInsertId();

    // 3. Insert items & Update Stock
    $stmtItem = $pdo->prepare("
        INSERT INTO order_details (order_fk, item_fk, quantity, price_at_sale) 
        VALUES (?, ?, ?, ?)
    ");

    foreach ($finalItems as $fItem) {
        // Insert Detail
        $stmtItem->execute([
            $order_id,
            $fItem['id'],
            $fItem['qty'],
            $fItem['price']
        ]);
        
        // Reduce Stock
        $stmtUpdateStock->execute([$fItem['qty'], $fItem['id']]);
        
        // Auto-update status if 0 (Optional, but good practice)
        // $pdo->exec("UPDATE meals SET availability='Out of Stock' WHERE quantity=0 AND meal_id=" . $fItem['id']);
    }

    // 4. Clear the cart
    clearCart($cart_id);

    $pdo->commit();

    // Store for confirmation
    $_SESSION['last_order'] = [
        'order_id' => $order_id,
        'customer_name' => $customerName,
        'total' => $total_amount_final,
        'timestamp' => time()
    ];

    header('Location: order-confirmation.php?order_id=' . $order_id);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Order processing failed: " . $e->getMessage());
    die("An error occurred while placing your order: " . $e->getMessage());
}

