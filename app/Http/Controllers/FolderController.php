<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Admin\StoreFileRequest;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Illuminate\Validation\Rule;


class FolderController extends Controller
{
    public function store(Request $request)
    {
    $this->authorize('manage-folders');

    $data = $request->validate([
        'name' => 'required|string|max:150',
        'division_id' => 'required|integer',
    ]);

    $slug = \Str::slug($data['name']);
    $storageRoot = $slug; // bisa kamu perluas: "{$divisionSlug}/{$slug}"
    $disk = \App\Support\StorageDisk::finalDisk();

    if (app()->isLocal()) {
        \Storage::disk('local')->makeDirectory("folders/{$storageRoot}");
    } else {
        \Storage::disk($disk)->makeDirectory($storageRoot);
    }

    \App\Models\Folder::create([
        'division_id' => $data['division_id'],
        'name'        => $data['name'],
        'slug'        => $slug,
        'visibility'  => 'private',
        'created_by'  => $request->user()->id,
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    return redirect()->route('folders.index')->with('success', 'Folder dibuat.');
    }
}