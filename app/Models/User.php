<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'phone',
        'company',
        'ruc',
        'is_active',
        'email_verified_at',
        'password',
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
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'user_id');
    }

    public function assignedQuotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'assigned_seller_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function hasRole(string|array $roles): bool
    {
        if (! $this->is_active || ! $this->role) {
            return false;
        }

        $roleList = is_array($roles) ? $roles : [$roles];

        return in_array($this->role->slug, $roleList, true);
    }

    public function hasPermission(string $permission): bool
    {
        if (! $this->is_active || ! $this->role) {
            return false;
        }

        if ($this->isAdmin()) {
            return true;
        }

        return $this->role->hasPermission($permission);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function canAccessAdminPanel(): bool
    {
        if (! $this->is_active || ! $this->role) {
            return false;
        }

        if ($this->isAdmin()) {
            return true;
        }

        return $this->hasPermission('dashboard.view')
            || in_array($this->role->slug, ['admin', 'seller', 'catalog_editor', 'supervisor'], true);
    }
}
