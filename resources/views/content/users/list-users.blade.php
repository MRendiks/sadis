@extends('layouts/contentNavbarLayout')

@section('title', 'Tables - Basic Tables')

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

  {{-- CREATE USER FORM --}}
  <div class="card mb-4">
    <h5 class="card-header d-flex justify-content-between align-items-center">
      <span>Create New User</span>
      {{-- kalau mau modal, ubah tombol ini dan pindahkan form ke modal --}}
      <a class="btn btn-sm btn-primary" href="#createUserSection"><i class="bx bx-plus"></i> Add New User</a>
    </h5>
    <div class="card-body" id="createUserSection">
      <form method="POST" action="{{ route('admin.users.store') }}" class="row g-3">
        @csrf
        <input type="hidden" name="size" value="{{ $size }}">
        <input type="hidden" name="q" value="{{ $q }}">
        <input type="hidden" name="role" value="{{ request('role') }}">
        <input type="hidden" name="division_id" value="{{ request('division_id') }}">

        <div class="col-md-4">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" required maxlength="150" value="{{ old('name') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required maxlength="150" value="{{ old('email') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required minlength="6">
        </div>

        <div class="col-md-4">
          <label class="form-label">Primary Division</label>
          <select name="primary_division_id" class="form-select">
            <option value="">—</option>
            @foreach ($divisions as $d)
              <option value="{{ $d->id }}" @selected(old('primary_division_id')==$d->id)>{{ $d->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            @foreach ($roles as $r)
              <option value="{{ $r->name }}" @selected(old('role')==$r->name)>{{ $r->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select name="is_active" class="form-select">
            <option value="1" @selected(old('is_active','1')==='1')>Active</option>
            <option value="0" @selected(old('is_active')==='0')>Inactive</option>
          </select>
        </div>

        <div class="col-12 d-flex justify-content-end">
          <button class="btn btn-success"><i class="bx bx-save me-1"></i> Create</button>
        </div>
      </form>
    </div>
  </div>

  {{-- TABLE LIST USERS --}}
  <div class="card">
    <h5 class="card-header d-flex justify-content-between align-items-center">
      <span>Daftar User</span>
      {{-- tombol add new user di header table --}}
      <a class="btn btn-sm btn-primary" href="#createUserSection"><i class="bx bx-plus"></i> Add New User</a>
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
                    <a class="dropdown-item" href="javascript:void(0);">
                      <i class="bx bx-edit-alt me-1"></i> Edit
                    </a>
                    <a class="dropdown-item text-danger" href="javascript:void(0);">
                      <i class="bx bx-trash me-1"></i> Delete
                    </a>
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
