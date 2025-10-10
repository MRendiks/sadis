@extends('layouts/contentNavbarLayout')

@section('title', 'Folders - Index')

@section('content')
<div class="container-fluid">
<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-striped table-hover align-middle mb-0">
<thead class="table-dark">
<tr>
<th>#</th>
<th>Nama</th>
<th>Slug</th>
<th>Divisi</th>
<th>Parent</th>
<th>Visibility</th>
<th>Dibuat</th>
<th class="text-end">Aksi</th>
</tr>
</thead>
<tbody>
@forelse($folders as $idx=>$f)
<tr>
<td>{{ $folders->firstItem() + $idx }}</td>
<td>{{ $f->name }}</td>
<td><code>{{ $f->slug }}</code></td>
<td>{{ $f->division?->name ?? '-' }}</td>
<td>{{ $f->parent?->name ?? '-' }}</td>
<td>
<span class="badge bg-{{ $f->visibility==='public'?'success':'secondary' }}">{{ strtoupper($f->visibility) }}</span>
</td>
<td>{{ $f->created_at?->format('d M Y H:i') }}</td>
<td class="text-end">
<form method="POST" action="{{ route('folders.destroy',$f) }}" onsubmit="return confirm('Hapus folder ini? (data DB akan soft delete, folder fisik tidak dihapus)')">
@csrf @method('DELETE')
<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Hapus</button>
</form>
</td>
</tr>
@empty
<tr><td colspan="8" class="text-center text-muted">Belum ada folder.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@if($folders->hasPages())
<div class="card-footer">{{ $folders->withQueryString()->links() }}</div>
@endif
</div>
</div>
@endsection