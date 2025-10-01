<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $pwd = env('SEED_USER_PASSWORD', '123');
        $roleIds = DB::table('roles')->pluck('id','name');   // super_admin, admin_arsip, user_bidang
        $divIds  = DB::table('divisions')->pluck('id','code'); // APD, IPP, UMUM, ...

        $upsert = function(array $user, array $roles, array $divCodes = []) use ($pwd, $roleIds, $divIds) {
            // cari existing
            $existing = DB::table('users')->where('email', $user['email'])->first();

            if ($existing) {
                DB::table('users')->where('id', $existing->id)->update([
                    'name' => $user['name'],
                    'password' => isset($user['password']) ? Hash::make($user['password']) : $existing->password,
                    'primary_division_id' => isset($user['primary_division_code']) ? ($divIds[$user['primary_division_code']] ?? null) : $existing->primary_division_id,
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
                $uid = $existing->id;
            } else {
                $uid = DB::table('users')->insertGetId([
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => Hash::make($user['password'] ?? $pwd),
                    'primary_division_id' => isset($user['primary_division_code']) ? ($divIds[$user['primary_division_code']] ?? null) : null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // sync roles
            $roleIdList = collect($roles)->map(fn($r) => $roleIds[$r] ?? null)->filter()->values()->all();
            DB::table('user_roles')->where('user_id', $uid)->whereNotIn('role_id', $roleIdList)->delete();
            foreach ($roleIdList as $rid) {
                DB::table('user_roles')->updateOrInsert(['user_id'=>$uid,'role_id'=>$rid], []);
            }

            // sync divisions
            $divIdList = collect($divCodes)->map(fn($c) => $divIds[$c] ?? null)->filter()->values()->all();
            DB::table('user_divisions')->where('user_id',$uid)->whereNotIn('division_id',$divIdList)->delete();
            foreach ($divIdList as $did) {
                DB::table('user_divisions')->updateOrInsert(['user_id'=>$uid,'division_id'=>$did], []);
            }

            // set primary_division jika kosong
            $primary = DB::table('users')->where('id',$uid)->value('primary_division_id');
            if (!$primary && !empty($divIdList)) {
                DB::table('users')->where('id',$uid)->update(['primary_division_id'=>$divIdList[0]]);
            }
        };

        // Super Admin
        $upsert([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'primary_division_code' => 'UMUM',
        ], ['super_admin'], ['APD','IPP','UMUM']);

        // Admin Arsip
        $upsert([
            'name' => 'Admin Arsip',
            'email' => 'admin.arsip@example.com',
            'primary_division_code' => 'UMUM',
        ], ['admin_arsip'], ['APD','IPP','UMUM']);

        // Users Divisi (contoh)
        foreach (['APD','IPP','UMUM'] as $code) {
            $upsert([
                'name' => "User {$code}",
                'email' => "user.{$code}@example.com",
                'primary_division_code' => $code,
            ], ['user_bidang'], [$code]);
        }
    }
}
