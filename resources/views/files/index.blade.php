@extends('layouts/contentNavbarLayout')

@section('title', 'Tables - Basic Tables')

@section('content')
<div class="container-xxl container-p-y">

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">File Management</h4>
    @if (auth()->user()->hasAnyRole(['super_admin','admin']))
      <a href="{{ route('files.create') }}" class="btn btn-primary">
        <i class="bx bx-upload"></i> Upload File
      </a>
    @endif
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="{{ route('files.index') }}" class="row g-2">
        <div class="col-sm-3">
          <input type="text" class="form-control" name="q" value="{{ $q }}" placeholder="Search title or filename">
        </div>
        <div class="col-sm-3">
          <select class="form-select" name="division_id">
            <option value="">All divisions</option>
            @foreach ($divisions as $d)
              <option value="{{ $d->id }}" @selected((string)request('division_id')===(string)$d->id)>{{ $d->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-3">
          <select class="form-select" name="folder_id">
            <option value="">All folders</option>
            @foreach ($folders as $f)
              <option value="{{ $f->id }}" @selected((string)request('folder_id')===(string)$f->id)>{{ $f->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-2">
          <select class="form-select" name="status">
            <option value="">All status</option>
            @foreach ($statuses as $st)
              <option value="{{ $st }}" @selected(request('status')===$st)>{{ $st }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-1 d-grid">
          <button class="btn btn-secondary"><i class="bx bx-filter-alt"></i></button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead class="table-dark">
          <tr>
            <th>Title</th>
            <th>Division</th>
            <th>Folder</th>
            <th>Uploader</th>
            <th>Status</th>
            <th>Size</th>
            <th>Uploaded</th>
            <th style="width:70px;">Aksi</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @forelse ($files as $f)
            <tr>
              <td>{{ $f->title }}<br><small class="text-muted">{{ $f->original_name }}</small></td>
              <td>{{ $f->division_name }}</td>
              <td>{{ $f->folder_name ?? '-' }}</td>
              <td>{{ $f->uploader_name ?? '-' }}</td>
              <td><span class="badge bg-label-info">{{ $f->status }}</span></td>
              <td>{{ number_format($f->size_bytes/1024,1) }} KB</td>
              <td>{{ $f->created_at }}</td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu">
                    {{-- PREVIEW (tab baru) --}}
                    <a class="dropdown-item" href="{{ route('files.preview', $f->id) }}" target="_blank" rel="noopener">
                      <i class="bx bx-show-alt me-1"></i> Preview
                    </a>
                    {{-- DOWNLOAD --}}
                    <a class="dropdown-item" href="{{ route('files.download', $f->id) }}">
                      <i class="bx bx-download me-1"></i> Download
                    </a>

                    @if (auth()->user()->hasAnyRole(['super_admin','admin']))
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item" href="{{ route('files.edit', $f->id) }}">
                        <i class="bx bx-edit-alt me-1"></i> Edit
                      </a>
                      <form action="{{ route('files.destroy', $f->id) }}" method="POST"
                            onsubmit="return confirm('Yakin hapus file ini? (soft delete)');">
                        @csrf @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger">
                          <i class="bx bx-trash me-1"></i> Delete
                        </button>
                      </form>
                    @endif
                  </div>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted">No files.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center">
      <form method="GET" action="{{ route('files.index') }}" class="d-flex align-items-center gap-2">
        <input type="hidden" name="q" value="{{ $q }}">
        <input type="hidden" name="division_id" value="{{ $div }}">
        <input type="hidden" name="folder_id" value="{{ $folder }}">
        <input type="hidden" name="status" value="{{ $status }}">
        <label class="mb-0">Page size</label>
        <select class="form-select form-select-sm" name="size" onchange="this.form.submit()">
          @foreach ([10,25,50,100] as $s)
            <option value="{{ $s }}" @selected($size==$s)>{{ $s }}</option>
          @endforeach
        </select>
      </form>
      {{ $files->onEachSide(1)->links() }}
    </div>
  </div>
</div>
@endsection
