<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutletAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'auditor_id',
        'audit_date',
        'food_safety_score',
        'cleanliness_score',
        'brand_standards_score',
        'customer_service_score',
        'overall_score',
        'passed',
        'findings',
        'corrective_actions',
        'follow_up_date',
    ];

    protected function casts(): array
    {
        return [
            'audit_date' => 'date',
            'follow_up_date' => 'date',
            'food_safety_score' => 'decimal:2',
            'cleanliness_score' => 'decimal:2',
            'brand_standards_score' => 'decimal:2',
            'customer_service_score' => 'decimal:2',
            'overall_score' => 'decimal:2',
            'passed' => 'boolean',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function getScoreBadgeClassAttribute(): string
    {
        if ($this->overall_score >= 85) {
            return 'bg-success';
        }
        if ($this->overall_score >= 70) {
            return 'bg-warning text-dark';
        }

        return 'bg-danger';
    }
}
