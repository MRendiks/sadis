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

    public function hasRole(string $name): bool
    {
        return $this->roles()->where('name', $name)->exists();
    }

    public function hasAnyRole(array $names): bool
    {
        return $this->roles()->whereIn('name', $names)->exists();
    }

    /** Cepat buat ambil daftar ID divisi user */
    public function divisionIds()
    {
        return $this->divisions()->pluck('divisions.id');
    }
}
