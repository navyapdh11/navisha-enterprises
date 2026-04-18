<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'compare_at_price',
        'weight',
        'weight_unit',
        'attributes',
        'is_active',
        'zone',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'attributes' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the product that owns this variant.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the inventory levels for this variant.
     */
    public function inventoryLevels()
    {
        return $this->hasMany(InventoryLevel::class);
    }

    /**
     * Get total stock across all locations.
     */
    public function getTotalStockAttribute(): int
    {
        return $this->inventoryLevels()->sum('quantity');
    }

    /**
     * Check if the variant is in stock for a given zone.
     */
    public function isInStock(?string $zone = null): bool
    {
        $query = $this->inventoryLevels()->where('quantity', '>', 0);
        if ($zone) {
            $query->where('zone', $zone);
        }
        return $query->exists();
    }
}
