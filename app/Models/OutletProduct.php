<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutletProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'product_id',
        'selling_price',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'is_available' => 'boolean',
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

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->selling_price ?? $this->product->suggested_retail_price);
    }
}
