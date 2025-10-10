<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

// === Import model & policy yang kamu pakai ===
use App\Models\FileEntry;
use App\Models\Folder;
use App\Policies\FileEntryPolicy;
use App\Policies\FolderPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        FileEntry::class => FileEntryPolicy::class,
        Folder::class    => FolderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // contoh Gate tambahan (opsional)
        Gate::define('isSuperAdmin', fn($user) => $user->hasRole('super_admin'));
        Gate::define('isAdminArsip', fn($user) => $user->hasRole('admin_arsip'));
    }
}
