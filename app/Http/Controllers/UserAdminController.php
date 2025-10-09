<?php


namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $size = (int)$request->input('size', 10);
        $q    = trim((string)$request->input('q', ''));
        $role = $request->input('role');
        $div  = $request->input('division_id');

        $totalUsers  = DB::table('users')->count();
        $activeUsers = DB::table('users')->where('is_active', 1)->count();

        $roles     = DB::table('roles')->orderBy('name')->get();
        $divisions = DB::table('divisions')->orderBy('name')->get();

        $users = DB::table('users')
            ->leftJoin('divisions', 'users.primary_division_id', '=', 'divisions.id')
            ->select('users.id','users.name','users.email','users.is_active','users.last_login_at',
                     'divisions.name as division_name','divisions.id as division_id')
            ->when($q, fn($qq)=>$qq->where(fn($w)=>$w->where('users.name','like',"%{$q}%")->orWhere('users.email','like',"%{$q}%")))
            ->when($div, fn($qq)=>$qq->where('users.primary_division_id',$div))
            ->when($role, function ($qq) use ($role) {
                $qq->whereExists(function ($sub) use ($role) {
                    $sub->from('user_roles')
                        ->join('roles','roles.id','=','user_roles.role_id')
                        ->whereColumn('user_roles.user_id','users.id')
                        ->where('roles.name',$role);
                });
            })
            ->orderByDesc('users.created_at')
            ->paginate($size)
            ->withQueryString();

        $rolesByUser = DB::table('user_roles')
            ->join('roles','roles.id','=','user_roles.role_id')
            ->whereIn('user_roles.user_id', collect($users->items())->pluck('id'))
            ->select('user_roles.user_id','roles.name')
            ->get()
            ->groupBy('user_id')
            ->map(fn($g)=>$g->pluck('name'));

        return view('admin.users.index', compact(
            'users','roles','divisions','size','q','role','div','activeUsers','totalUsers','rolesByUser'
        ));
    }

    public function create()
    {
        // ambil data untuk dropdown di form
        $roles     = DB::table('roles')->orderBy('name')->get();
        $divisions = DB::table('divisions')->orderBy('name')->get();

        return view('admin.users.create', compact('roles','divisions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required','string','max:150'],
            'email' => ['required','email','max:150','unique:users,email'],
            'password' => ['required','string','min:6'],
            'primary_division_id' => ['nullable','exists:divisions,id'],
            'is_active' => ['required', Rule::in(['0','1',0,1])],
            'role' => ['required','exists:roles,name'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $userId = DB::table('users')->insertGetId([
                'name' => $data['name'],
                'email'=> $data['email'],
                'password' => Hash::make($data['password']),
                'primary_division_id' => $data['primary_division_id'] ?? null,
                'is_active' => (int)$data['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $roleId = DB::table('roles')->where('name',$data['role'])->value('id');
            DB::table('user_roles')->updateOrInsert(['user_id'=>$userId,'role_id'=>$roleId]);

            // optional: log aktivitas
            DB::table('activity_logs')->insert([
                'subject_type' => 'users',
                'subject_id'   => $userId,
                'action'       => 'create_user',
                'causer_id'    => $request->user()->id ?? null,
                'properties'   => json_encode(['email'=>$data['email'],'role'=>$data['role']]),
                'ip_address'   => $request->ip(),
                'user_agent'   => substr((string)$request->userAgent(),0,255),
                'created_at'   => now(),
            ]);
        });

        return redirect()->route('admin.users.index')->with('success','User created successfully.');
    }
    public function edit($id)
    {
        $user = DB::table('users')->where('id',$id)->first();
        abort_if(!$user, 404);

        // keamanan ringan: admin tidak boleh edit super_admin
        $userRoleNames = DB::table('user_roles')
            ->join('roles','roles.id','=','user_roles.role_id')
            ->where('user_roles.user_id',$id)
            ->pluck('roles.name')->all();

        if (in_array('super_admin', $userRoleNames) && !auth()->user()->hasRole('super_admin')) {
            abort(403);
        }

        $roles     = DB::table('roles')->orderBy('name')->get();
        $divisions = DB::table('divisions')->orderBy('name')->get();

        $currentRole = DB::table('user_roles')
            ->join('roles','roles.id','=','user_roles.role_id')
            ->where('user_roles.user_id',$id)
            ->value('roles.name');

        return view('admin.users.edit', compact('user','roles','divisions','currentRole'));
    }

    public function update(Request $request, $id)
    {
        $auth = $request->user();
        abort_if(!$auth, 401);
        abort_if((int)$auth->id === (int)$id && !$auth->hasRole('super_admin') && $request->has('is_active') && (int)$request->input('is_active') === 0, 422);

        $target = DB::table('users')->where('id',$id)->first();
        abort_if(!$target, 404);

        // batasi admin edit super_admin
        $targetRoleNames = DB::table('user_roles')
            ->join('roles','roles.id','=','user_roles.role_id')
            ->where('user_roles.user_id',$id)
            ->pluck('roles.name')->all();

        if (in_array('super_admin', $targetRoleNames) && ! $auth->hasRole('super_admin')) {
            abort(403);
        }

        $data = $request->validate([
            'name'  => ['required','string','max:150'],
            'email' => ['required','email','max:150', Rule::unique('users','email')->ignore($id)],
            'password' => ['nullable','string','min:6'],
            'primary_division_id' => ['nullable','exists:divisions,id'],
            'is_active' => ['required', Rule::in(['0','1',0,1])],
            'role' => ['required','exists:roles,name'],
        ]);

        DB::transaction(function () use ($data, $id, $request) {
            $updates = [
                'name' => $data['name'],
                'email'=> $data['email'],
                'primary_division_id' => $data['primary_division_id'] ?? null,
                'is_active' => (int)$data['is_active'],
                'updated_at' => now(),
            ];
            if (!empty($data['password'])) {
                $updates['password'] = Hash::make($data['password']);
            }

            DB::table('users')->where('id',$id)->update($updates);

            // set single role
            $roleId = DB::table('roles')->where('name',$data['role'])->value('id');
            DB::table('user_roles')->where('user_id',$id)->delete();
            DB::table('user_roles')->insert(['user_id'=>$id,'role_id'=>$roleId]);

            // log
            DB::table('activity_logs')->insert([
                'subject_type' => 'users',
                'subject_id'   => $id,
                'action'       => 'update_user',
                'causer_id'    => $request->user()->id ?? null,
                'properties'   => json_encode(['email'=>$data['email'],'role'=>$data['role']]),
                'ip_address'   => $request->ip(),
                'user_agent'   => substr((string)$request->userAgent(),0,255),
                'created_at'   => now(),
            ]);
        });

        return redirect()->route('admin.users.index')->with('success','User updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $auth = $request->user();
        abort_if(!$auth, 401);

        // hanya super_admin boleh delete
        if (!$auth->hasRole('super_admin')) {
            abort(403);
        }

        // tidak boleh delete diri sendiri
        if ((int)$auth->id === (int)$id) {
            return back()->withErrors(['delete' => 'You cannot delete your own account.']);
        }

        $target = DB::table('users')->where('id',$id)->first();
        abort_if(!$target, 404);

        DB::transaction(function () use ($id, $request, $target) {
            // soft delete → set deleted_at
            DB::table('users')->where('id',$id)->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

            // opsional: juga nonaktifkan
            DB::table('users')->where('id',$id)->update(['is_active'=>0]);

            // log
            DB::table('activity_logs')->insert([
                'subject_type' => 'users',
                'subject_id'   => $id,
                'action'       => 'delete_user',
                'causer_id'    => $request->user()->id ?? null,
                'properties'   => json_encode(['email'=>$target->email]),
                'ip_address'   => $request->ip(),
                'user_agent'   => substr((string)$request->userAgent(),0,255),
                'created_at'   => now(),
            ]);
        });

        return redirect()->route('admin.users.index')->with('success','User deleted (soft) successfully.');
    }
}
