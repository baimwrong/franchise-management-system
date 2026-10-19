<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'outlet_id',
        'phone',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function supplyOrdersRequested(): HasMany
    {
        return $this->hasMany(SupplyOrder::class, 'requested_by_user_id');
    }

    public function bulkImports(): HasMany
    {
        return $this->hasMany(BulkImport::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isFranchiseOwner(): bool
    {
        return $this->role === 'franchise_owner';
    }

    public function isOutletManager(): bool
    {
        return $this->role === 'outlet_manager';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function accessibleOutletsQuery()
    {
        if ($this->isSuperAdmin()) {
            return Outlet::query();
        }

        if ($this->isFranchiseOwner()) {
            return Outlet::where(function ($q) {
                if ($this->outlet_id) {
                    $q->where('id', $this->outlet_id);
                }
                $q->orWhere('franchisee_name', 'like', "%{$this->name}%");
            });
        }

        return Outlet::where('id', $this->outlet_id ?? 0);
    }

    public function canManageOutlet(int $outletId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isFranchiseOwner()) {
            if ($this->outlet_id === $outletId) {
                return true;
            }

            return $this->accessibleOutletsQuery()->where('id', $outletId)->exists();
        }

        return $this->outlet_id === $outletId;
    }

    public function getRoleBadgeClassAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'bg-danger',
            'franchise_owner' => 'bg-primary',
            'outlet_manager' => 'bg-info text-dark',
            'staff' => 'bg-secondary',
            default => 'bg-dark',
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'HQ Super Admin',
            'franchise_owner' => 'Franchise Owner',
            'outlet_manager' => 'Outlet Manager',
            'staff' => 'Outlet Staff',
            default => ucfirst(str_replace('_', ' ', (string) $this->role)),
        };
    }
}
