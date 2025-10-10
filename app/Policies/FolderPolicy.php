<?php // app/Policies/FolderPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Folder;

class FolderPolicy
{
    public function viewAny(User $u){ return $u->hasAnyRole(['super_admin','admin']); }
    public function create(User $u){ return $u->hasRole('super_admin'); }
    public function update(User $u, Folder $f){ return $u->hasRole('super_admin'); }
    public function delete(User $u, Folder $f){ return $u->hasRole('super_admin'); }
}
