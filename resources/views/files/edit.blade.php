@extends('layouts/contentNavbarLayout')

@section('title', 'Tables - Basic Tables')

@section('content')
<div class="container-xxl container-p-y">
  <div class="mb-3">
    <a href="{{ route('files.index') }}" class="btn btn-light">
      <i class="bx bx-left-arrow-alt"></i> Back
    </a>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <h5 class="card-header">Edit File</h5>
    <div class="card-body">
      <form method="POST" action="{{ route('files.update', $file->id) }}" enctype="multipart/form-data" class="row g-3">
        @csrf @method('PUT')

        <div class="col-md-6">
          <label class="form-label">Division</label>
          <select name="division_id" class="form-select" required>
            @foreach ($divisions as $d)
              <option value="{{ $d->id }}" @selected($file->division_id == $d->id)>{{ $d->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Folder</label>
          <select name="folder_id" class="form-select">
            <option value="">—</option>
            @foreach ($folders as $f)
              <option value="{{ $f->id }}" @selected($file->folder_id == $f->id)>{{ $f->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" maxlength="200" required value="{{ $file->title }}">
        </div>
        <div class="col-md-6">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            @foreach ($statuses as $s)
              <option value="{{ $s }}" @selected($file->status === $s)>{{ $s }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3">{{ $file->description }}</textarea>
        </div>

        <div class="col-12">
          <label class="form-label">Replace File (optional)</label>
          <input type="file" name="file" class="form-control">
          <small class="text-muted">Upload untuk membuat versi baru.</small>
        </div>

        <div class="col-12 d-flex justify-content-end">
          <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
