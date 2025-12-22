<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/BaseModel.php';
require_once __DIR__ . '/../app/Models/MenuItemModel.php';

use App\Models\MenuItemModel;

try {
    $model = new MenuItemModel();
    $data = [
        'meal_name' => 'Test PHP Meal ' . time(),
        'description' => 'Direct test from PHP script',
        'price' => 9.99,
        'meal_type' => 'Lunch',
        'image' => 'images/meals-imgs/default.jpg',
        'availability' => 'Available',
        'quantity' => 10
    ];
    
    echo "Attempting to create meal...\n";
    $result = $model->create($data);
    
    if ($result) {
        echo "SUCCESS: Meal created!\n";
    } else {
        echo "FAILURE: Model returned false.\n";
    }
    
    // Check if it's there
    $items = $model->getAll();
    echo "Total items in DB: " . count($items) . "\n";
    
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
