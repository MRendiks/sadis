<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'super_admin', 'description' => 'Full control'],
            ['name' => 'admin_arsip', 'description' => 'Verifikasi & arsip'],
            ['name' => 'user_bidang', 'description' => 'User divisi (read/write di divisinya)'],
            ['name' => 'user_tamu', 'description' => 'Hanya lihat (opsional/public/share)'],
        ];

        foreach ($roles as $r) {
            DB::table('roles')->updateOrInsert(
                ['name' => $r['name']],
                ['description' => $r['description']]
            );
        }
    }
}
