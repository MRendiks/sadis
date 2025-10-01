<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            ['code' => 'APD',  'name' => 'APD'],
            ['code' => 'IPP',  'name' => 'IPP'],
            ['code' => 'UMUM', 'name' => 'Umum'],
        ];

        foreach ($divisions as $d) {
            DB::table('divisions')->updateOrInsert(
                ['code' => $d['code']],
                ['name' => $d['name'], 'is_active' => true]
            );
        }
    }
}
