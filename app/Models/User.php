<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ✅ RBAC Relasi
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission');
    }

    // ✅ Helper RBAC
    public function hasRole(string $roleName): bool
    {
        return $this->role?->name === $roleName;
    }

    public function hasPermission(string $permissionName): bool
    {
        // 🚀 Super Admin Bypass
        $superRoles = ['Super Admin', 'superadmin', 'admin_sistem'];
        if (in_array($this->role?->name, $superRoles, true)) {
            return true;
        }

        $roleHas = $this->role
            ? $this->role->permissions()->where('name', $permissionName)->exists()
            : false;

        if ($roleHas) return true;

        return $this->permissions()->where('name', $permissionName)->exists();
    }
}
