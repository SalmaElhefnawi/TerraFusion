<?php

namespace App\Models;

use PDO;

class MenuItemModel extends BaseModel
{
    protected $table = 'meals';
    protected $primaryKey = 'meal_id';

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (meal_name, description, price, category, image, availability, quantity) VALUES (:meal_name, :description, :price, :category, :image, :availability, :quantity)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'meal_name' => $data['meal_name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'category' => $data['category'] ?? 'Main Courses',
            'image' => $data['image'] ?? null,
            'availability' => $data['availability'] ?? 'Available',
            'quantity' => $data['quantity'] ?? 0
        ]);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET meal_name = :meal_name, description = :description, price = :price, category = :category, image = :image, availability = :availability, quantity = :quantity WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'meal_name' => $data['meal_name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'category' => $data['category'],
            'image' => $data['image'] ?? null,
            'availability' => $data['availability'] ?? 'Available',
            'quantity' => $data['quantity'] ?? 0
        ]);
    }

    public function getUniqueMealTypes()
    {
        $sql = "SELECT DISTINCT category FROM {$this->table} WHERE category IS NOT NULL AND category != ''";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getGroupedByMealType()
    {
        $items = $this->getAll();
        $grouped = [];
        foreach ($items as $item) {
            $grouped[$item['category']][] = $item;
        }
        return $grouped;
    }

    public function getMealTypeCounts()
    {
        $sql = "SELECT category as label, COUNT(*) as value FROM {$this->table} GROUP BY category";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
