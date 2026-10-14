<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoyaltyInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'invoice_number',
        'period_start',
        'period_end',
        'gross_sales',
        'royalty_percentage',
        'royalty_fee',
        'marketing_fee',
        'technology_fee',
        'total_amount_due',
        'status',
        'due_date',
        'paid_at',
        'payment_reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'gross_sales' => 'decimal:2',
            'royalty_percentage' => 'decimal:2',
            'royalty_fee' => 'decimal:2',
            'marketing_fee' => 'decimal:2',
            'technology_fee' => 'decimal:2',
            'total_amount_due' => 'decimal:2',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'bg-success',
            'issued' => 'bg-info text-dark',
            'draft' => 'bg-secondary',
            'overdue' => 'bg-danger',
            'waived' => 'bg-dark',
            default => 'bg-secondary',
        };
    }
}
