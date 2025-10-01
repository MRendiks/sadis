<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\StoreFileRequest;

class FilesController extends Controller
{
    public function index(Request $request)
    {
        $u = Auth::user();
        $q = $request->query('q');

        // Pastikan User model punya hasAnyRole() & divisionIds() (dari trait kita)
        $isPrivileged = $u->hasAnyRole(['super_admin','admin_arsip']);

        $files = DB::table('files as f')
            ->leftJoin('divisions as d','d.id','=','f.division_id')
            ->leftJoin('folders as fo','fo.id','=','f.folder_id')
            ->when(!$isPrivileged, function($qq) use ($u) {
                // jika belum bikin trait, ganti $u->divisionIds() dengan subquery user_divisions (lihat catatan di bawah)
                $qq->whereIn('f.division_id', $u->divisionIds());
            })
            ->when($q, fn($qq)=>$qq->where(function($w) use ($q){
                $w->where('f.title','like',"%$q%")
                  ->orWhere('f.original_name','like',"%$q%");
            }))
            ->select('f.*','d.code as division_code','fo.name as folder_name')
            ->orderByDesc('f.created_at')
            ->paginate(20)
            ->withQueryString();

        $canUpload = $isPrivileged;

        return view('files.index', compact('files','canUpload'));
    }

    // route ini sudah dibatasi middleware role:super_admin,admin_arsip di routes/web.php
    public function create()
    {
        $divisions = DB::table('divisions')->where('is_active',1)->get();
        $folders   = DB::table('folders')->get();
        return view('files.create', compact('divisions','folders'));
    }

    public function store(StoreFileRequest $request)
    {
        // Hanya super_admin/admin_arsip yang lolos ke sini (authorize di FormRequest)
        $data = $request->validated();
        $uploaded = $request->file('file');

        $disk = config('filesystems.default', 'local');
        $subdir = 'uploads/'.($data['division_id']).'/'.date('Y/m');
        $storedPath = $uploaded->store($subdir, $disk);

        $mime = $uploaded->getClientMimeType();
        $size = $uploaded->getSize();
        $hash = hash_file('sha256', $uploaded->getRealPath());

        $fileId = DB::table('files')->insertGetId([
            'division_id'     => $data['division_id'],
            'folder_id'       => $data['folder_id'] ?? null,
            'uploader_id'     => Auth::id(),                       // ✅
            'title'           => $data['title'],
            'description'     => $data['description'] ?? null,
            'original_name'   => $uploaded->getClientOriginalName(),
            'storage_disk'    => $disk,
            'storage_path'    => $storedPath,
            'mime_type'       => $mime,
            'size_bytes'      => $size,
            'hash_sha256'     => $hash,
            'status'          => 'draft',
            'current_version' => 1,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        DB::table('file_versions')->insert([
            'file_id'      => $fileId,
            'version'      => 1,
            'storage_path' => $storedPath,
            'storage_disk' => $disk,
            'size_bytes'   => $size,
            'mime_type'    => $mime,
            'hash_sha256'  => $hash,
            'uploaded_by'  => Auth::id(),                         // ✅
            'uploaded_at'  => now(),
            'notes'        => 'Initial upload',
        ]);

        DB::table('activity_logs')->insert([
            'subject_type' => 'File',
            'subject_id'   => $fileId,
            'action'       => 'file.uploaded',
            'properties'   => json_encode(['disk'=>$disk,'path'=>$storedPath]),
            'causer_id'    => Auth::id(),                         // ✅
            'ip_address'   => request()->ip(),
            'user_agent'   => substr((string)request()->userAgent(),0,255),
            'created_at'   => now(),
        ]);

        return redirect()->route('files.index')->with('success','File berhasil diupload');
    }
}
