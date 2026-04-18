<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'subcategory',
        'base_price',
        'images',
        'is_active',
        'is_featured',
        'tags',
        'zone',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'base_price' => 'decimal:2',
        'images' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'tags' => 'array',
    ];

    /**
     * Get the variants for this product.
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get all inventory levels across all variants.
     */
    public function inventoryLevels()
    {
        return $this->hasManyThrough(InventoryLevel::class, ProductVariant::class);
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to filter by zone.
     */
    public function scopeZone($query, string $zone)
    {
        return $query->where('zone', $zone);
    }

    /**
     * Get the minimum price across all variants.
     */
    public function getMinPriceAttribute(): ?string
    {
        $minPrice = $this->variants()->min('price');
        return $minPrice !== null ? number_format($minPrice, 2, '.', '') : null;
    }

    /**
     * Check if the product has any variant in stock.
     */
    public function isInStock(): bool
    {
        return $this->variants()->whereHas('inventoryLevels', function ($query) {
            $query->where('quantity', '>', 0);
        })->exists();
    }
}
