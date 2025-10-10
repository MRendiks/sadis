<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;



class FolderController extends Controller
{
    public function create() {
  $folders = \App\Models\Folder::whereNull('deleted_at')->orderBy('name')->get();
  return view('file_requests.create', compact('folders'));
}

public function store(Request $request) {
  $data = $request->validate([
    'folder_id' => 'required|exists:folders,id',
    'file'      => 'required|file|max:51200', // 50MB contoh
  ]);

  $uploaded = $request->file('file');
  $tempPath = $uploaded->storeAs(
    'temp/'.date('Y/m/d'),
    \Str::random(40).'.'.$uploaded->getClientOriginalExtension(),
    'local'
  );

    FileRequest::create([
    'folder_id'     => $data['folder_id'],
    'requester_id'  => $request->user()->id,
    'original_name' => $uploaded->getClientOriginalName(),
    'mime_type'     => $uploaded->getMimeType(),
    'size_bytes'    => $uploaded->getSize(),
    'temp_disk'     => 'local',
    'temp_path'     => $tempPath,
    'status'        => 'pending',
    'created_at'    => now(),
    'updated_at'    => now(),
  ]);

  return back()->with('success', 'Pengajuan upload terkirim. Menunggu review.');
}
}