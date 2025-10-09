<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\Role;
use App\Models\Division;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    // public function index()
    // {
    //     return view('content.users.list-users');
    // }
    public function index(Request $request)
    {
        $size = (int)($request->input('size', 10));
        $q    = trim((string)$request->input('q', ''));
        $role = $request->input('role'); // name of role
        $div  = $request->input('division_id'); // divisions.id

        // stats
        $totalUsers  = DB::table('users')->count();
        $activeUsers = DB::table('users')->where('is_active', 1)->count();

        // filterable lists
        $roles     = DB::table('roles')->orderBy('name')->get();
        $divisions = DB::table('divisions')->orderBy('name')->get();

        $users = DB::table('users')
            ->leftJoin('divisions', 'users.primary_division_id', '=', 'divisions.id')
            ->select(
                'users.id', 'users.name', 'users.email',
                'users.is_active', 'users.last_login_at',
                'divisions.name as division_name', 'divisions.id as division_id'
            )
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('users.name', 'like', "%{$q}%")
                      ->orWhere('users.email', 'like', "%{$q}%");
                });
            })
            ->when($div, fn($qq) => $qq->where('users.primary_division_id', $div))
            ->when($role, function ($qq) use ($role) {
                $qq->whereExists(function ($sub) use ($role) {
                    $sub->from('user_roles')
                        ->join('roles','roles.id','=','user_roles.role_id')
                        ->whereColumn('user_roles.user_id','users.id')
                        ->where('roles.name', $role);
                });
            })
            ->orderBy('users.created_at','desc')
            ->paginate($size)
            ->withQueryString();

        // attach roles per user (ringkas; boleh dioptimasi pakai eager/collection map jika perlu)
        $userIds = collect($users->items())->pluck('id');
        $rolesByUser = DB::table('user_roles')
            ->join('roles','roles.id','=','user_roles.role_id')
            ->whereIn('user_roles.user_id', $userIds)
            ->select('user_roles.user_id','roles.name')
            ->get()
            ->groupBy('user_id')
            ->map(fn($g) => $g->pluck('name')->values());

        return view('content.users.list-users', compact(
            'users','roles','divisions','size','q','role','div','activeUsers','totalUsers','rolesByUser'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => ['required','string','max:150'],
            'email' => ['required','email','max:150','unique:users,email'],
            'password' => ['required','string','min:6'],
            'primary_division_id' => ['nullable','exists:divisions,id'],
            'is_active' => ['required', Rule::in(['0','1',0,1])],
            'role' => ['required','exists:roles,name'],           // single role picker
        ]);

        // create user
        $userId = DB::table('users')->insertGetId([
            'name' => $request->name,
            'email'=> $request->email,
            'password' => Hash::make($request->password),
            'primary_division_id' => $request->primary_division_id,
            'is_active' => (int)$request->is_active,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // attach role
        $roleId = DB::table('roles')->where('name',$request->role)->value('id');
        if ($roleId) {
            DB::table('user_roles')->updateOrInsert([
                'user_id' => $userId,
                'role_id' => $roleId,
            ]);
        }

        return redirect()
            ->route('content.users.list-users', $request->only(['size','q','role','division_id']))
            ->with('success','User created successfully.');
    }

    public function create()
    {
        $roles = Role::pluck('name','id');
        $divisions = Division::where('is_active',1)->get();

        return view('users.create', compact('roles','divisions'));
    }
}
