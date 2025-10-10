@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = ($navbarDetached ?? '');
$user = Auth::user();
@endphp

<!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{$containerNav}} navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
@endif
@if(isset($navbarDetached) && $navbarDetached == '')
<nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
  <div class="{{$containerNav}}">
@endif

  <!-- Brand / Toggle -->
  <div class="navbar-nav align-items-center">
    <button class="nav-item nav-link px-0 me-2 btn btn-link d-xl-none" data-bs-toggle="collapse" data-bs-target="#layout-menu">
      <i class="bx bx-menu bx-sm"></i>
    </button>
    <a href="{{ route('dashboard') }}" class="navbar-brand d-none d-md-inline-flex align-items-center">
      <span class="fw-bold">Dashboard</span>
    </a>
  </div>

  <div class="navbar-nav ms-auto flex-row align-items-center">

    {{-- Quick links (optional) --}}
    <ul class="navbar-nav d-none d-md-flex me-2">
      @auth
        @if($user?->hasAnyRole(['super_admin','admin']))
          <li class="nav-item me-2">
            <a href="{{ route('files.index') }}" class="nav-link"><i class="bx bx-folder-open"></i> Files</a>
          </li>
          <li class="nav-item me-2">
            <a href="{{ route('reviews.queue') }}" class="nav-link"><i class="bx bx-check-shield"></i> Review</a>
          </li>
        @endif
      @endauth
    </ul>

    <!-- User -->
    <ul class="navbar-nav flex-row align-items-center ms-auto">
      @guest
        <li class="nav-item">
          <a class="btn btn-sm btn-primary" href="{{ route('login') }}">Login</a>
        </li>
      @else
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
          <a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center" href="#" data-bs-toggle="dropdown">
            <div class="avatar avatar-online me-2">
              <img src="{{ $user->profile_photo_url ?? asset('assets/img/avatars/6.png') }}" alt class="w-px-40 h-auto rounded-circle" />
            </div>
            <div class="d-none d-lg-block text-start">
              <div class="fw-semibold">{{ $user->name }}</div>
              <div class="small text-muted">
                @if(method_exists($user,'getRoleNames'))
                  {{ $user->getRoleNames()->join(', ') }}
                @else
                  {{ $user->role ?? 'User' }}
                @endif
              </div>
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li class="px-3 py-2">
              <div class="d-flex align-items-center">
                <div class="avatar me-2">
                  <img src="{{ $user->profile_photo_url ?? asset('assets/img/avatars/6.png') }}" class="w-px-40 h-auto rounded-circle" />
                </div>
                <div>
                  <div class="fw-semibold">{{ $user->name }}</div>
                  <div class="small text-muted">{{ $user->email }}</div>
                </div>
              </div>
            </li>
            <li><hr class="dropdown-divider"></li>

            <li>
              <a class="dropdown-item" href="{{ route('profile.show') }}">
                <i class="bx bx-user me-2"></i> Profile
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="{{ route('settings.index') }}">
                <i class="bx bx-cog me-2"></i> Settings
              </a>
            </li>

            <li><hr class="dropdown-divider"></li>

            <li>
              <form method="POST" action="{{ route('logout') }}" id="logout-form">
                @csrf
                <button type="submit" class="dropdown-item text-danger">
                  <i class="bx bx-power-off me-2"></i> Log Out
                </button>
              </form>
            </li>
          </ul>
        </li>
      @endguest
    </ul>
    <!--/ User -->
  </div>

@if(!isset($navbarDetached) || $navbarDetached == '')
  </div>
@endif
</nav>
<!-- / Navbar -->
