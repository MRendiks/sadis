@extends('layouts/contentNavbarLayout')

@section('title', 'Admin - Create User')

@section('content')
<div class="container-xxl container-p-y">
  <div class="mb-3 d-flex gap-2">
    <a href="{{ route('admin.users.index') }}" class="btn btn-light">
      <i class="bx bx-left-arrow-alt"></i> Back
    </a>

    {{-- Delete button only for super_admin --}}
    @if (auth()->user()->hasRole('super_admin'))
      <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
            onsubmit="return confirm('Yakin hapus user ini? (soft delete)');">
        @csrf @method('DELETE')
        <button class="btn btn-danger"><i class="bx bx-trash"></i> Delete</button>
      </form>
    @endif
  </div>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <h5 class="card-header">Edit User</h5>
    <div class="card-body">
      <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="row g-3">
        @csrf @method('PUT')

        <div class="col-md-6">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" required maxlength="150" value="{{ old('name',$user->name) }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required maxlength="150" value="{{ old('email',$user->email) }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Password (optional)</label>
          <input type="password" name="password" class="form-control" minlength="6" placeholder="Leave blank to keep current">
        </div>

        <div class="col-md-6">
          <label class="form-label">Primary Division</label>
          <select name="primary_division_id" class="form-select">
            <option value="">—</option>
            @foreach ($divisions as $d)
              <option value="{{ $d->id }}" @selected(old('primary_division_id',$user->primary_division_id)==$d->id)>{{ $d->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            @foreach ($roles as $r)
              <option value="{{ $r->name }}" @selected(old('role',$currentRole)==$r->name)>{{ $r->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Status</label>
          <select name="is_active" class="form-select">
            <option value="1" @selected(old('is_active',$user->is_active)==1)>Active</option>
            <option value="0" @selected(old('is_active',$user->is_active)==0)>Inactive</option>
          </select>
        </div>

        <div class="col-12 d-flex justify-content-end">
          <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
