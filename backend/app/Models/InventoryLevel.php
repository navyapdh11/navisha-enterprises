<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLevel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_variant_id',
        'location',
        'zone',
        'quantity',
        'reserved_quantity',
        'reorder_point',
        'restock_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'restock_date' => 'datetime',
    ];

    /**
     * Get the product variant for this inventory level.
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get the available (non-reserved) quantity.
     */
    public function getAvailableQuantity(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    /**
     * Check if this inventory level needs restocking.
     */
    public function needsRestock(): bool
    {
        return $this->getAvailableQuantity() <= $this->reorder_point;
    }

    /**
     * Scope a query to only include low stock items.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_point');
    }

    /**
     * Scope a query to only include items for a specific zone.
     */
    public function scopeZone($query, string $zone)
    {
        return $query->where('zone', $zone);
    }

    /**
     * Reserve a quantity from available stock.
     */
    public function reserve(int $quantity): bool
    {
        if ($this->getAvailableQuantity() < $quantity) {
            return false;
        }

        return $this->increment('reserved_quantity', $quantity);
    }

    /**
     * Release a reserved quantity.
     */
    public function release(int $quantity): void
    {
        $releaseAmount = min($quantity, $this->reserved_quantity);
        $this->decrement('reserved_quantity', $releaseAmount);
    }

    /**
     * Deduct stock permanently (e.g., after order fulfillment).
     */
    public function deduct(int $quantity): void
    {
        $this->decrement('quantity', $quantity);
        $this->decrement('reserved_quantity', $quantity);
    }
}
