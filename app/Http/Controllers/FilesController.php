<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Folder;
use App\Models\Division;
use Illuminate\Support\Str;
use App\Support\StorageResolver;
use Illuminate\Validation\Rule;
use App\Support\FolderPathResolver;

class FilesController extends Controller
{
    public function index(Request $request)
    {
        $u     = $request->user();
        $q     = trim((string)$request->input('q',''));
        $div   = $request->input('division_id');
        $folder= $request->input('folder_id');
        $status= $request->input('status');
        $size  = (int)$request->input('size', 10);

        // Tentukan apakah user punya akses penuh
        $hasFullAccess = method_exists($u, 'hasAnyRole')
            ? $u->hasAnyRole(['super_admin','admin_arsip'])
            : ($u->hasRole('super_admin') || $u->hasRole('admin_arsip'));

        // Kumpulan division_id yang boleh dilihat user biasa
        $allowedDivisionIds = [];
        if (!$hasFullAccess) {
            $allowedDivisionIds = DB::table('user_divisions')
                ->where('user_id', $u->id)
                ->pluck('division_id')
                ->all();

            // fallback: kalau mapping kosong, pakai primary_division_id (jika ada)
            if (empty($allowedDivisionIds) && !empty($u->primary_division_id)) {
                $allowedDivisionIds = [$u->primary_division_id];
            }
            // Kalau tetap kosong, paksa ke array kosong agar tidak menampilkan apa pun
            if (empty($allowedDivisionIds)) {
                $allowedDivisionIds = [-1]; // id tidak mungkin
            }
        }

        // === DROPDOWN DIVISIONS (hanya yang diizinkan user) ===
        $divisions = DB::table('divisions')
            ->when(!$hasFullAccess, fn($q) =>
                $q->join('user_divisions','divisions.id','=','user_divisions.division_id')
                ->where('user_divisions.user_id', $u->id)
                ->select('divisions.*')
                ->distinct()
            )
            ->orderBy('name')
            ->get();

        // === DROPDOWN FOLDERS (hanya folder dari divisions yang boleh dilihat) ===
        $folders = DB::table('folders')
            ->when(!$hasFullAccess, fn($q) =>
                $q->whereIn('division_id', $allowedDivisionIds)
            )
            ->orderBy('name')
            ->get();

        // Jika user memilih division di luar allowed, kita abaikan/filter ulang
        if (!$hasFullAccess && $div && !in_array((int)$div, $allowedDivisionIds, true)) {
            $div = null; // atau bisa return 403 kalau mau strict
        }

        $filesQ = DB::table('files')
            ->join('divisions','files.division_id','=','divisions.id')
            ->leftJoin('folders','files.folder_id','=','folders.id')
            ->leftJoin('users as uploader','files.uploader_id','=','uploader.id')
            ->select(
                'files.id','files.title','files.original_name','files.status',
                'files.mime_type','files.size_bytes',
                'divisions.name as division_name','divisions.id as division_id',
                'folders.name as folder_name','files.created_at',
                'uploader.name as uploader_name'
            )
            ->when($q, fn($qq)=>$qq->where(fn($w)=>$w
                ->where('files.title','like',"%{$q}%")
                ->orWhere('files.original_name','like',"%{$q}%")
            ))
            ->when($div, fn($qq)=>$qq->where('files.division_id',$div))
            ->when($folder, fn($qq)=>$qq->where('files.folder_id',$folder))
            ->when($status, fn($qq)=>$qq->where('files.status',$status))
            ->whereNull('files.deleted_at');

        // Batasi files ke allowedDivisionIds untuk user biasa
        if (!$hasFullAccess) {
            $filesQ->whereIn('files.division_id', $allowedDivisionIds);
        }

        $files = $filesQ
            ->orderByDesc('files.created_at')
            ->paginate($size)
            ->withQueryString();

        // Opsi status (sesuaikan kebijakanmu)
        $statuses = ['submitted'];

        return view('files.index', compact(
            'files','divisions','folders','statuses','q','div','folder','status','size'
        ));
    }

    public function create()
    {
        $divisions = DB::table('divisions')->orderBy('name')->get();
        $folders   = DB::table('folders')->orderBy('name')->get();
        return view('files.create', compact('divisions','folders'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
            'folder_id'   => ['nullable', 'exists:folders,id'],
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'file'        => ['required', 'file', 'max:51200'], // 50MB
            'status'      => ['required', Rule::in(['draft', 'submitted', 'under_review', 'approved', 'rejected', 'archived'])],
        ]);

        $u = $request->user();
        $uploaded = $request->file('file');

        // Tentukan disk aktif (synology_sftp atau local_files)
        $disk = StorageResolver::disk(); // otomatis baca dari .env (FILESYSTEM_DISK_FINAL)

        // Tentukan direktori tujuan
        if (!empty($data['folder_id'])) {
            $folder = Folder::with(['division', 'parent'])->findOrFail($data['folder_id']);
            $dir = ltrim(FolderPathResolver::buildPath($folder), '/');
        } else {
            $division = Division::find($data['division_id']);
            $dir = ltrim($division ? ($division->code ?? $division->name ?? 'unknown_division') : 'unknown_division', '/');
        }

        // Pastikan direktori ada
        if (!Storage::disk($disk)->directoryExists($dir)) {
            Storage::disk($disk)->makeDirectory($dir);
        }

        // Nama file asli dan aman
        $originalName = $uploaded->getClientOriginalName();
        [$safeBase, $finalName] = $this->prepareSafeFilename($disk, $dir, $originalName);

        // Upload file
        try {
            $path = Storage::disk($disk)->putFileAs($dir, $uploaded, $finalName);
            if ($path === false) {
                throw new \RuntimeException('Failed to store file.');
            }
        } catch (\Throwable $e) {
            // Fallback ke local_files kalau NAS gagal
            \Log::warning('Upload to NAS failed, fallback to local_files', [
                'error' => $e->getMessage(),
                'dir'   => $dir,
                'name'  => $finalName,
            ]);

            $disk = 'local_files';
            if (!Storage::disk($disk)->directoryExists($dir)) {
                Storage::disk($disk)->makeDirectory($dir);
            }
            $path = Storage::disk($disk)->putFileAs($dir, $uploaded, $finalName);
        }

        // Metadata file
        $mime = $uploaded->getClientMimeType();
        $size = $uploaded->getSize();
        $hash = hash_file('sha256', $uploaded->getRealPath());

        // Simpan ke DB dalam satu transaksi
        DB::transaction(function () use ($data, $u, $disk, $path, $mime, $size, $hash, $uploaded, $request, $finalName) {
            $fileId = DB::table('files')->insertGetId([
                'division_id'     => $data['division_id'],
                'folder_id'       => $data['folder_id'] ?? null,
                'uploader_id'     => $u->id,
                'title'           => $data['title'],
                'description'     => $data['description'] ?? null,
                'original_name'   => $uploaded->getClientOriginalName(),
                'storage_disk'    => $disk,
                'storage_path'    => $path,
                'mime_type'       => $mime,
                'size_bytes'      => $size,
                'hash_sha256'     => $hash,
                'status'          => $data['status'],
                'current_version' => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            DB::table('file_versions')->insert([
                'file_id'      => $fileId,
                'version'      => 1,
                'storage_path' => $path,
                'storage_disk' => $disk,
                'size_bytes'   => $size,
                'mime_type'    => $mime,
                'hash_sha256'  => $hash,
                'uploaded_by'  => $u->id,
                'uploaded_at'  => now(),
                'notes'        => 'Initial upload',
            ]);

            DB::table('activity_logs')->insert([
                'subject_type' => 'files',
                'subject_id'   => $fileId,
                'action'       => 'upload_file',
                'causer_id'    => $u->id,
                'properties'   => json_encode([
                    'title'    => $data['title'],
                    'original' => $uploaded->getClientOriginalName(),
                    'saved_as' => $finalName,
                ]),
                'ip_address'   => $request->ip(),
                'user_agent'   => substr((string)$request->userAgent(), 0, 255),
                'created_at'   => now(),
            ]);
        });

        return redirect()->route('files.index')->with('success', 'File uploaded successfully.');
    }

    /**
     * Generate safe filename (avoid overwrite)
     */
    private function prepareSafeFilename(string $disk, string $dir, string $original): array
    {
        $name = pathinfo($original, PATHINFO_FILENAME);
        $ext  = pathinfo($original, PATHINFO_EXTENSION);
        $safe = Str::slug($name, '-');
        $candidate = $ext ? "{$safe}.{$ext}" : $safe;

        $i = 1;
        while (Storage::disk($disk)->exists(trim($dir, '/') . '/' . $candidate)) {
            $suffix = '-' . $i++;
            $candidate = $ext ? "{$safe}{$suffix}.{$ext}" : "{$safe}{$suffix}";
        }

        return [$safe, $candidate];
    }

    public function edit($id)
    {
        $file = DB::table('files')->where('id',$id)->whereNull('deleted_at')->first();
        abort_if(!$file, 404);
        $divisions = DB::table('divisions')->orderBy('name')->get();
        $folders   = DB::table('folders')->orderBy('name')->get();
        $statuses  = ['draft','submitted','under_review','approved','rejected','archived'];
        return view('files.edit', compact('file','divisions','folders','statuses'));
    }

    public function update(Request $request, $id)
    {
        $file = DB::table('files')->where('id',$id)->whereNull('deleted_at')->first();
        abort_if(!$file, 404);

        $data = $request->validate([
            'division_id' => ['required','exists:divisions,id'],
            'folder_id'   => ['nullable','exists:folders,id'],
            'title'       => ['required','string','max:200'],
            'description' => ['nullable','string'],
            'status'      => ['required', Rule::in(['draft','submitted','under_review','approved','rejected','archived'])],
            'file'        => ['nullable','file','max:51200'], // optional replace file
        ]);

        $u = $request->user();

        DB::transaction(function () use ($data, $u, $request, $file, $id) {
            $updates = [
                'division_id' => $data['division_id'],
                'folder_id'   => $data['folder_id'] ?? null,
                'title'       => $data['title'],
                'description' => $data['description'] ?? null,
                'status'      => $data['status'],
                'updated_at'  => now(),
            ];

            $newVersionAdded = false;

            if ($request->hasFile('file')) {
                $uploaded = $request->file('file');
                $disk = config('filesystems.default', 'local');
                $dir  = 'uploads/'.date('Y/m/d');
                $path = $uploaded->store($dir, $disk);

                $mime = $uploaded->getClientMimeType();
                $size = $uploaded->getSize();
                $hash = hash_file('sha256', $uploaded->getRealPath());

                // increment version
                $lastVersion = (int)DB::table('file_versions')->where('file_id',$id)->max('version');
                $ver = $lastVersion + 1;

                DB::table('file_versions')->insert([
                    'file_id'      => $id,
                    'version'      => $ver,
                    'storage_path' => $path,
                    'storage_disk' => $disk,
                    'size_bytes'   => $size,
                    'mime_type'    => $mime,
                    'hash_sha256'  => $hash,
                    'uploaded_by'  => $u->id,
                    'uploaded_at'  => now(),
                    'notes'        => 'Replace file',
                ]);

                // set as current file
                $updates = array_merge($updates, [
                    'original_name'  => $uploaded->getClientOriginalName(),
                    'storage_disk'   => $disk,
                    'storage_path'   => $path,
                    'mime_type'      => $mime,
                    'size_bytes'     => $size,
                    'hash_sha256'    => $hash,
                    'current_version'=> $ver,
                ]);

                $newVersionAdded = true;
            }

            DB::table('files')->where('id',$id)->update($updates);

            DB::table('activity_logs')->insert([
                'subject_type' => 'files',
                'subject_id'   => $id,
                'action'       => $newVersionAdded ? 'update_file_with_new_version' : 'update_file',
                'causer_id'    => $u->id,
                'properties'   => json_encode(['title'=>$data['title'],'status'=>$data['status']]),
                'ip_address'   => $request->ip(),
                'user_agent'   => substr((string)$request->userAgent(),0,255),
                'created_at'   => now(),
            ]);
        });

        return redirect()->route('files.index')->with('success','File updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $file = DB::table('files')->where('id',$id)->whereNull('deleted_at')->first();
        abort_if(!$file, 404);

        DB::table('files')->where('id',$id)->update([
            'deleted_at' => now(),
            'updated_at' => now(),
            'status'     => 'archived',
        ]);

        DB::table('activity_logs')->insert([
            'subject_type' => 'files',
            'subject_id'   => $id,
            'action'       => 'delete_file',
            'causer_id'    => $request->user()->id ?? null,
            'properties'   => json_encode(['title'=>$file->title]),
            'ip_address'   => $request->ip(),
            'user_agent'   => substr((string)$request->userAgent(),0,255),
            'created_at'   => now(),
        ]);

        return redirect()->route('files.index')->with('success','File deleted (soft) successfully.');
    }

    private function authorizeFileAccess(object $file, \App\Models\User $user): void
    {
        // super_admin, admin_arsip boleh semua
        if ($user->hasRole('super_admin') || $user->hasRole('admin_arsip')) {
            return;
        }

        // admin: batasan opsional -> hanya divisi sendiri (aktifkan jika mau strict)
        // if ($user->hasRole('admin')) {
        //     if ((int)$file->division_id === (int)$user->primary_division_id) return;
        //     abort(403);
        // }

        // user: hanya file di divisinya
        if ($user->hasRole('user')) {
            if ((int)$file->division_id === (int)$user->primary_division_id) return;
            abort(403);
        }

        // fallback
        abort(403);
    }

    public function preview(Request $request, int $id)
    {
        $file = DB::table('files')->where('id', $id)->first();
        abort_if(!$file, 404, 'File not found');

        // (opsional) cek izin user
        // $this->authorize('view', $file);

        $disk = $file->storage_disk ?? 'synology_sftp';
        $path = $file->storage_path;

        abort_if(!Storage::disk($disk)->exists($path), 404, 'File not found');

        // Baca stream dari storage (NAS via SFTP)
        $stream = Storage::disk($disk)->readStream($path);
        abort_if(!$stream, 404, 'Cannot read stream');

        $mime = $file->mime_type ?: (Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream');
        $filename = $file->original_name ?? basename($path);
        $disposition = 'inline'; // ubah ke 'attachment' kalau mau paksa download

        // Catat log activity
          DB::table('activity_logs')->insert([
              'subject_type' => 'files',
              'subject_id'   => $file->id,
              'action'       => 'preview_file', // atau 'download_file'
              'causer_id'    => $request->user()->id ?? null,
              'properties'   => json_encode(['title' => $file->title ?? $filename]),
              'ip_address'   => $request->ip(),
              'user_agent'   => substr((string)$request->userAgent(), 0, 255),
              'created_at'   => now(),
          ]);

        // Kirim file ke browser
        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) fclose($stream);
        }, 200, [
            'Content-Type'              => $mime,
            'Content-Disposition'       => $disposition . '; filename="' . addcslashes($filename, '"\\') . '"',
            'X-Content-Type-Options'    => 'nosniff',
            'Cache-Control'             => 'private, max-age=0, no-cache',
            'Pragma'                    => 'no-cache',
        ]);
    }

    public function download(Request $request, $id)
    {
        $file = DB::table('files')->where('id',$id)->whereNull('deleted_at')->first();
        abort_if(!$file, 404);
        $this->authorizeFileAccess($file, $request->user());

        $disk = $file->storage_disk ?? config('filesystems.default', 'local');
        $path = $file->storage_path;

        if (in_array($disk, ['s3','minio','spaces'])) {
            // 5 menit url sementara, force download
            $url = Storage::disk($disk)->temporaryUrl($path, now()->addMinutes(5), [
                'Response-Content-Type'        => $file->mime_type ?: 'application/octet-stream',
                'Response-Content-Disposition' => 'attachment; filename="'.($file->original_name ?? 'download').'"',
            ]);
            return redirect()->away($url);
        }

        abort_if(!Storage::disk($disk)->exists($path), 404, 'File not found');
        DB::table('activity_logs')->insert([
          'subject_type' => 'files',
          'subject_id'   => $file->id,
          'action'       => 'download_file',
          'causer_id'    => $request->user()->id ?? null,
          'properties'   => json_encode(['title'=>$file->title]),
          'ip_address'   => $request->ip(),
          'user_agent'   => substr((string)$request->userAgent(),0,255),
          'created_at'   => now(),
        ]);
        return Storage::disk($disk)->download($path, $file->original_name ?? basename($path), [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
