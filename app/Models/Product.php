<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'brand_id',
        'category_id',
        'sku',
        'barcode',
        'name',
        'description',
        'unit_of_measure',
        'cost_price',
        'suggested_retail_price',
        'minimum_selling_price',
        'is_track_inventory',
        'is_active',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'suggested_retail_price' => 'decimal:2',
            'minimum_selling_price' => 'decimal:2',
            'is_track_inventory' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function outletProducts(): HasMany
    {
        return $this->hasMany(OutletProduct::class);
    }

    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function supplyOrderItems(): HasMany
    {
        return $this->hasMany(SupplyOrderItem::class);
    }

    public function salesTransactionItems(): HasMany
    {
        return $this->hasMany(SalesTransactionItem::class);
    }
}
