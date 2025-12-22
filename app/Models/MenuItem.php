<?php

namespace App\Models;

class MenuItem extends Model
{
    protected static string $table = 'meals';
    protected string $primaryKey = 'meal_id';
    protected array $fillable = [
        'meal_name',
        'category',
        'description',
        'price',
        'image',
        'availability',
        'quantity'
    ];

    /**
     * Get the category name
     */
    public function getCategoryAttribute(): string
    {
        return $this->category;
    }

    /**
     * Check if item is available
     */
    public function isAvailable(): bool
    {
        return $this->availability === 'Available' && $this->quantity > 0;
    }

    /**
     * Get items by category
     */
    public static function getByCategory(string $category): array
    {
        return self::where('category', $category);
    }

    /**
     * Get available items only
     */
    public static function getAvailable(): array
    {
        return self::where('availability', 'Available');
    }
}

