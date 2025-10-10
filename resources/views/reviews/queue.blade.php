@extends('layouts/contentNavbarLayout')

@section('title', 'Review Queue')

@section('content')

<div class="container-xxl container-p-y">

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">File Review</h4>
    {{-- Tidak ada tombol upload di halaman review --}}
  </div>

  {{-- Filter Bar (mirror Files) --}}
  <div class="card mb-3">
    <div class="card-body">
      <form class="row g-2 align-items-end" method="GET" action="{{ route('reviews.queue') }}">
        <div class="col-sm-3">
          <label class="form-label">Search</label>
          <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari judul/nama file...">
        </div>
        <div class="col-sm-3">
          <label class="form-label">Division</label>
          <select class="form-select" name="division_id">
            <option value="">All divisions</option>
            @foreach ($divisions as $d)
              <option value="{{ $d->id }}" @selected((string)request('division_id')===(string)$d->id)>{{ $d->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-3">
          <label class="form-label">Folder</label>
          <select class="form-select" name="folder_id">
            <option value="">All folders</option>
            @foreach ($folders as $fd)
              <option value="{{ $fd->id }}" @selected((string)request('folder_id')===(string)$fd->id)>{{ $fd->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-2">
          <label class="form-label">Status</label>
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
            <th>File</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @forelse ($files as $f)
            <tr>
              <td class="fw-semibold">
                {{ $f->title }}
                <div class="small text-muted">
                  {{ $f->original_name }}
                  <div><code>{{ $f->mime_type }}</code> • {{ number_format(($f->size_bytes ?? 0)/1024, 1) }} KB</div>
                </div>
              </td>
              <td>{{ optional($f->division)->name ?? '-' }}</td>
              <td>{{ optional($f->folder)->name ?? '-' }}</td>
              <td>
                <div>{{ optional($f->uploader)->name ?? '-' }}</div>
                <div class="small text-muted">{{ optional($f->uploader)->email }}</div>
              </td>
              <td>
                @php
                  $badge = [
                    'draft' => 'bg-label-secondary',
                    'submitted' => 'bg-label-info',
                    'under_review' => 'bg-label-warning',
                    'approved' => 'bg-label-success',
                    'rejected' => 'bg-label-danger',
                    'archived' => 'bg-label-dark',
                  ][$f->status] ?? 'bg-label-secondary';
                @endphp
                <span class="badge {{ $badge }}">{{ $f->status }}</span>
                @if ($f->reviewed_by)
                  <div class="small text-muted">By: {{ optional($f->reviewer)->name }}</div>
                @endif
              </td>
              <td>
                <a href="{{ route('files.preview', $f->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                  <i class="bx bx-show-alt"></i> Preview
                </a>
                <a href="{{ route('files.download', $f->id) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bx bx-download"></i> Download
                </a>
              </td>
              <td class="text-end">
                @if (auth()->user()->hasAnyRole(['super_admin','admin_arsip']))
                  <div class="btn-group">
                    {{-- Take (claim review) --}}
                    <form action="{{ route('reviews.take', $f->id) }}" method="POST">
                      @csrf
                      <button type="submit" class="btn btn-label-info btn-sm">
                        <i class="bx bx-user-plus"></i> Take
                      </button>
                    </form>
                    {{-- Approve --}}
                    <form action="{{ route('reviews.approve', $f->id) }}" method="POST" onsubmit="return confirm('Approve file ini?');">
                      @csrf
                      <button type="submit" class="btn btn-success btn-sm">
                        <i class="bx bx-check"></i> Approve
                      </button>
                    </form>
                    {{-- Reject (modal alasan) --}}
                    <button
                      type="button"
                      class="btn btn-danger btn-sm"
                      data-bs-toggle="modal"
                      data-bs-target="#rejectModal"
                      data-file-id="{{ $f->id }}"
                      data-file-title="{{ $f->title }}"
                    >
                      <i class="bx bx-x"></i> Reject
                    </button>
                  </div>
                @else
                  <em class="text-muted">No action</em>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-5">
                <i class="bx bx-folder-open bx-sm"></i>
                <div class="mt-2">Tidak ada data untuk direview.</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card-body d-flex justify-content-between align-items-center">
      <form method="GET" class="d-flex align-items-center gap-2">
        <input type="hidden" name="q" value="{{ request('q') }}">
        <input type="hidden" name="division_id" value="{{ request('division_id') }}">
        <input type="hidden" name="folder_id" value="{{ request('folder_id') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <label class="mb-0">Page size</label>
        <select class="form-select form-select-sm" name="size" onchange="this.form.submit()">
          @php $size = (int) request('size', 10); @endphp
          @foreach ([10,25,50,100] as $s)
            <option value="{{ $s }}" @selected($size==$s)>{{ $s }}</option>
          @endforeach
        </select>
      </form>
      {{ $files->onEachSide(1)->links() }}
    </div>
  </div>
</div>

{{-- Modal Reject --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="rejectForm" method="POST" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Tolak File</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2 small text-muted" id="rejectFileTitle"></div>
        <div class="mb-3">
          <label class="form-label">Alasan penolakan</label>
          {{-- Controller expects "notes" field --}}
          <textarea class="form-control" name="notes" rows="3" required maxlength="1000" placeholder="Tuliskan alasan penolakan..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-danger" type="submit">Kirim</button>
      </div>
    </form>
  </div>
</div>

@endsection
