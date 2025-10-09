@extends('layouts/contentNavbarLayout')

@section('title', 'Tables - Basic Tables')


@section('content')
<div class="container-xxl container-p-y">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Dashboard</h4>
    <span class="text-muted">Hi, {{ $user->name }} 👋</span>
  </div>

  {{-- KPI CARDS --}}
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted">Total Files</div>
            <div class="h3 mb-0">{{ number_format($totalFiles) }}</div>
          </div>
          <i class="bx bx-file bx-lg text-primary"></i>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted">Total Accounts</div>
            <div class="h3 mb-0">{{ number_format($totalAccounts) }}</div>
          </div>
          <i class="bx bx-user bx-lg text-success"></i>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted">Storage Used</div>
            <div class="h3 mb-0">
              @php
                $gb = $storageUsedBytes / (1024*1024*1024);
              @endphp
              {{ number_format($gb, 2) }} GB
            </div>
            <small class="text-muted">{{ number_format($storageUsedBytes/1024/1024,1) }} MB</small>
          </div>
          <i class="bx bx-hdd bx-lg text-info"></i>
        </div>
      </div>
    </div>
  </div>

  {{-- ROW: DISTRIBUTIONS --}}
  <div class="row g-3 mb-4">
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <span>Total Accounts per Division</span>
        </div>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead>
              <tr>
                <th>Division</th>
                <th class="text-end">Users</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($accountsPerDivision as $row)
                <tr>
                  <td>{{ $row->division }}</td>
                  <td class="text-end">{{ number_format($row->total) }}</td>
                </tr>
              @empty
                <tr><td colspan="2" class="text-center text-muted">No data.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <span>Total Files per Division</span>
        </div>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead>
              <tr>
                <th>Division</th>
                <th class="text-end">Files</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($filesPerDivision as $row)
                <tr>
                  <td>{{ $row->division }}</td>
                  <td class="text-end">{{ number_format($row->total) }}</td>
                </tr>
              @empty
                <tr><td colspan="2" class="text-center text-muted">No data.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- ROW: STATUS & TOP UPLOADER --}}
  <div class="row g-3 mb-4">
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">Files by Status</div>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead>
              <tr>
                <th>Status</th>
                <th class="text-end">Total</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($filesByStatus as $row)
                <tr>
                  <td><span class="badge bg-label-info text-capitalize">{{ $row->status }}</span></td>
                  <td class="text-end">{{ number_format($row->total) }}</td>
                </tr>
              @empty
                <tr><td colspan="2" class="text-center text-muted">No data.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">Top Uploader (Last 7 days)</div>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead>
              <tr>
                <th>Uploader</th>
                <th class="text-end">Files</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($topUploader7d as $row)
                <tr>
                  <td>{{ $row->uploader }}</td>
                  <td class="text-end">{{ number_format($row->uploaded_count) }}</td>
                </tr>
              @empty
                <tr><td colspan="2" class="text-center text-muted">No data.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- ACTIVITY last 7d (ringkas per hari) --}}
  <div class="card mb-4">
    <div class="card-header">Activity (Last 7 days)</div>
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead>
          <tr>
            <th>Date</th>
            <th class="text-end">Total Activities</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($activity7d as $row)
            <tr>
              <td>{{ $row->d }}</td>
              <td class="text-end">{{ number_format($row->total) }}</td>
            </tr>
          @empty
            <tr><td colspan="2" class="text-center text-muted">No data.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- RECENT ACTIVITY LOGS --}}
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Recent Activity</span>
      <small class="text-muted">Latest 15</small>
    </div>
    <div class="table-responsive">
      <table class="table table-sm">
        <thead class="table-dark">
          <tr>
            <th>When</th>
            <th>Action</th>
            <th>Subject</th>
            <th>Causer</th>
            <th>IP</th>
            <th>User Agent</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($recentLogs as $log)
            <tr>
              <td>{{ $log->created_at }}</td>
              <td><span class="badge bg-label-primary">{{ $log->action }}</span></td>
              <td>{{ $log->subject_type }}#{{ $log->subject_id }}</td>
              <td>{{ $log->causer_id ?? '-' }}</td>
              <td>{{ $log->ip_address ?? '-' }}</td>
              <td class="text-truncate" style="max-width: 300px;">{{ $log->user_agent ?? '-' }}</td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted">No activity.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection
