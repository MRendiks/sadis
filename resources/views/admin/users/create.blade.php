@extends('layouts/contentNavbarLayout')

@section('title', 'Admin - Create User')


@section('content')
<div class="container-xxl container-p-y">

  <div class="mb-3">
    <a href="{{ route('admin.users.index') }}" class="btn btn-light">
      <i class="bx bx-left-arrow-alt"></i> Back to Users
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
    <h5 class="card-header">Create User</h5>
    <div class="card-body">
      <form method="POST" action="{{ route('admin.users.store') }}" class="row g-3">
        @csrf

        <div class="col-md-6">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" required maxlength="150" value="{{ old('name') }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required maxlength="150" value="{{ old('email') }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required minlength="6">
        </div>

        <div class="col-md-6">
          <label class="form-label">Primary Division</label>
          <select name="primary_division_id" class="form-select">
            <option value="">—</option>
            @foreach ($divisions as $d)
              <option value="{{ $d->id }}" @selected(old('primary_division_id')==$d->id)>{{ $d->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            @foreach ($roles as $r)
              <option value="{{ $r->name }}" @selected(old('role')==$r->name)>{{ $r->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6">
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
</div>
@endsection
