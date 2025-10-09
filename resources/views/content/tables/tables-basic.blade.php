@extends('layouts/contentNavbarLayout')

@section('title', 'Tables - Basic Tables')

@section('content')
<div class="card">
  <h5 class="card-header">Daftar File Review</h5>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead class="table-dark">
        <tr>
          <th>Nama</th>
          <th>Jabatan</th>
          <th>Bidang</th>
          <th>Tanggal</th>
          <th>Nama Laporan</th>
          <th>Folder</th>
          <th>File</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        <tr>
          <td><i class="bx bxl-angular bx-md text-danger me-4"></i> <span>Dinda</span></td>
          <td>APD</td>
          <td>
            Test
          </td>
          <td><span class="badge bg-label-primary me-1">2025-09-21</span></td>
          <td><span>test</span></td>
          <td><span>APD</span></td>
          <td><span>test.pdf</span></td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-trash me-1"></i> Delete</a>
              </div>
            </div>
          </td>
        </tr>
       </tbody>
    </table>
  </div>
</div>
@endsection
