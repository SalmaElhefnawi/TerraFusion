<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Controllers\AuthController;

class OrderController
{
    private $orderModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
    }

    public function index()
    {
        $orders = $this->orderModel->getAllOrdersWithDetails();
        
        $data = [
            'orders' => $orders
        ];

        $content = __DIR__ . '/../Views/orders/index.php';
        include __DIR__ . '/../Views/shared/layout.php';
    }

    public function updateStatus()
    {
        // Allow Assess for specialized roles, e.g. Waiter can update status too? Requirements said "Waiter (lowest access)".
        // Let's assume everyone logged in can update status for now, or maybe restrict to Table Manager+.
        // Prompt says "Every page except login.php must require login". AuthController::requireLogin() is called in index.php.
        // Let's create a check.
        AuthController::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $status = $_POST['status'] ?? null;

            if ($id && $status) {
                $this->orderModel->updateStatus($id, $status);
            }
            
            header("Location: /Admin/public/index.php?page=orders");
            exit();
        }
    }
}
