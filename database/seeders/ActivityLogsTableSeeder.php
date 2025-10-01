<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActivityLogsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actions = ['upload_file', 'review_file', 'login', 'logout', 'update_profile'];
        $subjects = ['files', 'users', 'folders'];

        for ($i = 1; $i <= 20; $i++) {
            DB::table('activity_logs')->insert([
                'subject_type' => $subjects[array_rand($subjects)],
                'subject_id'   => rand(1, 10),
                'action'       => $actions[array_rand($actions)],
                'causer_id'    => rand(1, 5), // user_id acak
                'properties'   => json_encode([
                    'ip'   => '192.168.1.' . rand(2, 254),
                    'info' => Str::random(10),
                ]),
                'ip_address'   => '192.168.1.' . rand(2, 254),
                'user_agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at'   => now()->subMinutes(rand(0, 500)),
            ]);
        }
    }
}
