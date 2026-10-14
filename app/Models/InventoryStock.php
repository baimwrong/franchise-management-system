<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'product_id',
        'quantity_on_hand',
        'reserved_quantity',
        'reorder_threshold',
        'target_stock_level',
        'last_restocked_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'decimal:2',
            'reserved_quantity' => 'decimal:2',
            'reorder_threshold' => 'decimal:2',
            'target_stock_level' => 'decimal:2',
            'last_restocked_at' => 'datetime',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity_on_hand <= $this->reorder_threshold;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->quantity_on_hand <= 0) {
            return 'Out of Stock';
        }

        if ($this->is_low_stock) {
            return 'Low Stock Alert';
        }

        return 'Adequate';
    }

    public function getStockBadgeClassAttribute(): string
    {
        if ($this->quantity_on_hand <= 0) {
            return 'bg-danger';
        }

        if ($this->is_low_stock) {
            return 'bg-warning text-dark';
        }

        return 'bg-success';
    }
}
