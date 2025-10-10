<?php // app/Http/Controllers/FolderManagementController.php
namespace App\Http\Controllers;

use App\Http\Controllers\StoreFolderRequest;
use App\Models\Folder;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Support\FolderPathResolver;

class FolderManagementController extends Controller
{
    public function __construct()
    {
        // Only call middleware if the method exists on the controller to avoid undefined method errors
        if (method_exists($this, 'middleware')) {
            $this->middleware('superadmin');
        }
    }

    public function index()
    {
        $folders = Folder::with('division','parent')->orderBy('created_at','desc')->paginate(20);
        return view('folders.index', compact('folders'));
    }

    public function create()
    {
        $divisions = DB::table('divisions')->where('is_active',1)->orderBy('name')->get();
        $parents   = DB::table('folders')->orderBy('name')->get();
        return view('folders.create', compact('divisions','parents'));
    }

    public function store(StoreFolderRequest $request)
    {
        $u = $request->user();
        $data = $request->validated();

        // slug fallback jika kosong
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['created_by'] = $u->id;
        $data['updated_by'] = $u->id;

        // (Opsional) Buat folder fisik di NAS/local sesuai struktur division/slug
        $folder = Folder::create($data);

        // Pastikan relasi tersedia
        $folder->load(['division','parent']);

        // Buat direktori rekursif: {division}/{parent...}/{slug}
        $diskName = app(\App\Support\StorageResolver::class)::disk();
        $disk = app(\Illuminate\Filesystem\FilesystemManager::class)->disk($diskName);

        $relativePath = FolderPathResolver::buildPath($folder); // contoh: APD/SUB/SUB-1
        $disk->makeDirectory($relativePath, 0755, true);

        ActivityLog::create([
            'subject_type'=>'folders','subject_id'=>$folder->id,'action'=>'create_folder',
            'causer_id'=>$u->id,'properties'=>['name'=>$folder->name,'slug'=>$folder->slug],
            'ip_address'=>$request->ip(),'user_agent'=>substr((string)$request->userAgent(),0,255),'created_at'=>now(),
        ]);

        return redirect()->route('folders.index')->with('success','Folder created.');
    }

    public function destroy(Request $request, Folder $folder)
    {
        $folder->delete();
        ActivityLog::create([
            'subject_type'=>'folders','subject_id'=>$folder->id,'action'=>'delete_folder',
            'causer_id'=>$request->user()->id,'properties'=>['name'=>$folder->name],
            'ip_address'=>$request->ip(),'user_agent'=>substr((string)$request->userAgent(),0,255),'created_at'=>now(),
        ]);
        return back()->with('success','Folder deleted (soft).');
    }
}
