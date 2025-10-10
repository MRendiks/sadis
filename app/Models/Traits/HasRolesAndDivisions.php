<?php

namespace App\Models\Traits;

use App\Models\Role;
use App\Models\Division;

trait HasRolesAndDivisions
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function divisions()
    {
        return $this->belongsToMany(Division::class, 'user_divisions');
    }

    public function primaryDivision()
    {
        return $this->belongsTo(Division::class, 'primary_division_id');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles->contains(fn ($r) => $r->name === $role);
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->pluck('name')->intersect($roles)->isNotEmpty();
    }

    // 🔹 Tambahan biar kompatibel sama Blade lama (Spatie-like)
    public function getRoleNames(): \Illuminate\Support\Collection
    {
        return $this->roles->pluck('name');
    }
}

