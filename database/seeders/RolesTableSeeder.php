<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'super_admin', 'description' => 'Full control', 'guard_name' => 'web'],
            ['name' => 'admin_arsip', 'description' => 'Verifikasi & arsip', 'guard_name' => 'web'],
            ['name' => 'user_bidang', 'description' => 'User divisi (read/write di divisinya)', 'guard_name' => 'web'],
            ['name' => 'user_tamu', 'description' => 'Hanya lihat (opsional/public/share)', 'guard_name' => 'web'],
        ];

        foreach ($roles as $r) {
            DB::table('roles')->updateOrInsert(
                ['name' => $r['name']],
                ['description' => $r['description']],
                ['guard_name' => $r['guard_name']],
            );
        }
    }
}
