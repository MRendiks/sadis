<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\Role;
use App\Models\Division;

class UsersController extends Controller
{
    // Halaman form tambah user
    public function create()
    {
        $roles = Role::pluck('name','id');
        $divisions = Division::where('is_active',1)->get();

        return view('users.create', compact('roles','divisions'));
    }

    // Simpan user baru
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        // Insert ke tabel users
        $userId = DB::table('users')->insertGetId([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'primary_division_id' => $data['primary_division_id'] ?? null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Roles → simpan di pivot user_roles
        $roleIds = Role::whereIn('name', $data['roles'])->pluck('id')->all();
        foreach ($roleIds as $rid) {
            DB::table('user_roles')->insert(['user_id'=>$userId,'role_id'=>$rid]);
        }

        // Divisions → simpan di pivot user_divisions
        if (!empty($data['divisions'])) {
            $rows = collect($data['divisions'])
                ->map(fn($did)=>['user_id'=>$userId,'division_id'=>$did])
                ->all();
            DB::table('user_divisions')->insert($rows);
        }

        // Tambah log (Auth::id() ✅)
        DB::table('activity_logs')->insert([
            'subject_type' => 'User',
            'subject_id'   => $userId,
            'action'       => 'user.created',
            'properties'   => json_encode(['by'=>Auth::id()]),
            'causer_id'    => Auth::id(), // ✅
            'ip_address'   => request()->ip(),
            'user_agent'   => substr((string)request()->userAgent(),0,255),
            'created_at'   => now(),
        ]);

        return redirect()->route('users.create')->with('success','User berhasil dibuat!');
    }
}
