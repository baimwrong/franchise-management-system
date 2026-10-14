<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'tagline',
        'corporate_tax_id',
        'hq_address',
        'contact_email',
        'contact_phone',
        'royalty_rate_percentage',
        'marketing_fund_percentage',
        'currency',
        'currency_symbol',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'royalty_rate_percentage' => 'decimal:2',
            'marketing_fund_percentage' => 'decimal:2',
            'settings' => 'array',
        ];
    }

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
