@extends('layouts/contentNavbarLayout')

@section('title','Settings')

@section('content')
<div class="container-xxl container-p-y">
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Preferences</h5>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
              <label class="form-label">Default Page Size</label>
              <select name="page_size" class="form-select">
                @foreach([10,25,50,100] as $opt)
                  <option value="{{ $opt }}" @selected((int)($prefs['page_size'] ?? 10) === $opt)>{{ $opt }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Theme</label>
              <select name="theme" class="form-select">
                @foreach(['system'=>'System','light'=>'Light','dark'=>'Dark'] as $val=>$label)
                  <option value="{{ $val }}" @selected(($prefs['theme'] ?? 'system') === $val)>{{ $label }}</option>
                @endforeach
              </select>
              <small class="text-muted">System will follow your OS/browser setting.</small>
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
