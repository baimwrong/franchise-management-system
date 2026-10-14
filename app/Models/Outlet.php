<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outlet extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'region_id',
        'name',
        'outlet_code',
        'franchisee_name',
        'type',
        'status',
        'phone',
        'email',
        'address',
        'city',
        'state_province',
        'postal_code',
        'tax_identification_number',
        'tax_rate_percentage',
        'opening_date',
        'operating_hours',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate_percentage' => 'decimal:2',
            'opening_date' => 'date',
            'operating_hours' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function outletProducts(): HasMany
    {
        return $this->hasMany(OutletProduct::class);
    }

    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function supplyOrders(): HasMany
    {
        return $this->hasMany(SupplyOrder::class);
    }

    public function salesTransactions(): HasMany
    {
        return $this->hasMany(SalesTransaction::class);
    }

    public function royaltyInvoices(): HasMany
    {
        return $this->hasMany(RoyaltyInvoice::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(OutletAudit::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-success',
            'pending_approval' => 'bg-warning text-dark',
            'renovation' => 'bg-info text-dark',
            'suspended' => 'bg-danger',
            'closed' => 'bg-secondary',
            default => 'bg-dark',
        };
    }
}
