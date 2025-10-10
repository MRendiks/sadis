@extends('layouts/contentNavbarLayout')

@section('title', 'Folder - Create')

@section('content')
<div class="container-fluid">
<div class="d-flex align-items-center justify-content-between mb-3">
<h4 class="mb-0">Buat Folder</h4>
<a href="{{ route('folders.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>


@include('_partials.alerts')


<div class="card">
<div class="card-body">
<form method="POST" action="{{ route('folders.store') }}">
@csrf
<div class="row g-3">
<div class="col-md-4">
<label class="form-label">Divisi <span class="text-danger">*</span></label>
<select name="division_id" class="form-select" required>
<option value="" disabled selected>Pilih divisi</option>
@foreach($divisions as $d)
<option value="{{ $d->id }}" @selected(old('division_id')==$d->id)>{{ $d->name }}</option>
@endforeach
</select>
</div>
<div class="col-md-4">
<label class="form-label">Parent Folder</label>
<select name="parent_id" class="form-select">
<option value="">— Tidak ada —</option>
@foreach($parents as $p)
<option value="{{ $p->id }}" @selected(old('parent_id')==$p->id)>{{ $p->name }}</option>
@endforeach
</select>
</div>
<div class="col-md-4">
<label class="form-label">Visibility <span class="text-danger">*</span></label>
<select name="visibility" class="form-select" required>
<option value="public" @selected(old('visibility')==='public')>Public</option>
<option value="private" @selected(old('visibility')==='private')>Private</option>
</select>
</div>
<div class="col-md-6">
<label class="form-label">Nama Folder <span class="text-danger">*</span></label>
<input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Mis. Laporan Bulanan" required>
</div>
<div class="col-md-6">
<label class="form-label">Slug (opsional)</label>
<input type="text" name="slug" value="{{ old('slug') }}" class="form-control" placeholder="otomatis dari nama jika dikosongkan">
</div>
</div>
<div class="mt-4 d-flex gap-2">
<button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Simpan</button>
<a href="{{ route('folders.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>
</form>
</div>
</div>
</div>
@endsection