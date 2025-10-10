<?php // app/Policies/FileEntryPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\FileEntry;

class FileEntryPolicy
{
    // Boleh submit: user_bidang, admin_arsip, super_admin
    public function submit(User $user): bool
    {
        return $user->hasAnyRole(['user_bidang','admin_arsip','super_admin']);
    }

    // Boleh review: admin_arsip & super_admin
    public function review(User $user, ?FileEntry $file = null): bool
    {
        return $user->hasAnyRole(['admin_arsip','super_admin']);
    }

    // Contoh view (opsional): tamu hanya lihat publik
    public function view(User $user, FileEntry $file): bool
    {
        if ($user->hasAnyRole(['admin_arsip','super_admin'])) return true;
        if ($user->hasRole('user_bidang')) {
            return (int)$user->primary_division_id === (int)$file->division_id;
        }
        // user_tamu: hanya yang public (kalau kamu punya flag/visibility)
        return false;
    }
}
