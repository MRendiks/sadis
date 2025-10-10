@extends('layouts/contentNavbarLayout')

@section('title', 'List Users')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Alert flash --}}
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- STAT CARDS --}}
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <h6 class="mb-1">Active Users</h6>
            <h3 class="mb-0">{{ $activeUsers }}</h3>
            <small class="text-muted">of {{ $totalUsers }} total</small>
          </div>
          <i class="bx bx-user-check bx-lg text-success"></i>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card">
        <div class="card-body d-flex gap-3 flex-wrap">
          {{-- QUICK FILTERS BAR --}}
          <form class="row g-2 align-items-end w-100" method="GET" action="{{ route('admin.users.index') }}">
            <div class="col-sm-3">
              <label class="form-label">Role</label>
              <select class="form-select" name="role">
                <option value="">All</option>
                @foreach ($roles as $r)
                  <option value="{{ $r->name }}" @selected(request('role')===$r->name)>{{ $r->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-sm-3">
              <label class="form-label">Divisi</label>
              <select class="form-select" name="division_id">
                <option value="">All</option>
                @foreach ($divisions as $d)
                  <option value="{{ $d->id }}" @selected((string)request('division_id')===(string)$d->id)>{{ $d->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-sm-3">
              <label class="form-label">Search</label>
              <input type="text" class="form-control" name="q" value="{{ $q }}" placeholder="Name or email">
            </div>
            <div class="col-sm-2">
              <label class="form-label">Page size</label>
              <select class="form-select" name="size" onchange="this.form.submit()">
                @foreach ([10,25,50,100] as $s)
                  <option value="{{ $s }}" @selected($size==$s)>{{ $s }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-sm-1 d-grid">
              <button class="btn btn-primary"><i class="bx bx-filter-alt"></i></button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- TABLE LIST USERS --}}
  <div class="card">
    <h5 class="card-header d-flex justify-content-between align-items-center">
      <span>Daftar User</span>
      <a class="btn btn-sm btn-primary" href="{{ route('admin.users.create') }}">
    <i class="bx bx-plus"></i> Add New User
</a>
    </h5>

    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead class="table-dark">
          <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Bidang</th>
            <th>Last Login</th>
            <th>Status</th>
            <th style="width:70px;">Aksi</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @forelse ($users as $u)
            <tr>
              <td>{{ $u->name }}</td>
              <td>{{ $u->email }}</td>
              <td>
                @php $uroles = $rolesByUser->get($u->id, collect()); @endphp
                @forelse ($uroles as $rn)
                  <span class="badge bg-label-primary me-1">{{ $rn }}</span>
                @empty
                  <span class="text-muted">—</span>
                @endforelse
              </td>
              <td>{{ $u->division_name ?? '-' }}</td>
              <td><span class="badge bg-label-info">{{ $u->last_login_at ?? '-' }}</span></td>
              <td>
                @if ($u->is_active)
                  <span class="badge bg-success">Active</span>
                @else
                  <span class="badge bg-secondary">Inactive</span>
                @endif
              </td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('admin.users.edit', $u->id) }}">
                      <i class="bx bx-edit-alt me-1"></i> Edit
                    </a>

                    @if (auth()->user()->hasRole('super_admin') && auth()->id() !== $u->id)
                      <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST"
                            onsubmit="return confirm('Yakin hapus user ini? (soft delete)');">
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
            <tr><td colspan="7" class="text-center text-muted">No users.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex align-items-center gap-2">
          <input type="hidden" name="q" value="{{ $q }}">
          <input type="hidden" name="role" value="{{ $role }}">
          <input type="hidden" name="division_id" value="{{ $div }}">
          <label class="mb-0">Page size</label>
          <select class="form-select form-select-sm" name="size" onchange="this.form.submit()">
            @foreach ([10,25,50,100] as $s)
              <option value="{{ $s }}" @selected($size==$s)>{{ $s }}</option>
            @endforeach
          </select>
        </form>
      </div>
      <div>
        {{ $users->onEachSide(1)->links() }}
      </div>
    </div>
  </div>

</div>
@endsection
