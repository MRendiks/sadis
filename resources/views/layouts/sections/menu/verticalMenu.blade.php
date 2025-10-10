@php
  use Illuminate\Support\Facades\Auth;

  /** @var \App\Models\User $user */
  $user = Auth::user();

  // Ambil nama role user (Spatie) atau fallback kolom role biasa
    $roleNames = collect(
      method_exists($user, 'getRoleNames')
          ? $user->getRoleNames()
          : ($user?->roles?->pluck('name') ?? collect([optional($user)->role]))
  )->filter()->values()->all();
  // Helper: cek apakah item menu boleh terlihat oleh user
  $canSee = function ($item) use ($roleNames) {
      // Jika item tidak punya pembatasan role → semua boleh lihat
      if (!isset($item->role)) return true;

      // role di JSON bisa string / array → normalkan jadi array
      $required = is_array($item->role) ? $item->role : [$item->role];

      // Tampilkan hanya bila ada irisan role user vs role yang dibolehkan
      return count(array_intersect($roleNames, $required)) > 0;
  };
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <!-- Brand -->
  <div class="app-brand demo">
    <a href="{{ url('/') }}" class="app-brand-link">
      <span class="app-brand-logo">
        <img src="{{ asset('assets/img/logo.jpg') }}" alt="Logo" width="70">
      </span>
      {{-- <span class="app-brand-text demo menu-text fw-bold ms-2">{{ config('variables.templateName') }}</span> --}}
    </a>

    {{-- Toggle mobile (opsional) --}}
    {{-- <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="bx bx-chevron-left bx-sm d-flex align-items-center justify-content-center"></i>
    </a> --}}
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">

    {{-- Loop menu utama --}}
    @foreach($menuData[0]->menu as $menu)
      @if($canSee($menu))
        <li class="menu-item {{ isset($menu->submenu) ? 'menu-item-open' : '' }} {{ (isset($menu->active) && $menu->active) ? 'active' : '' }}">
          <a href="{{ $menu->url ?? 'javascript:void(0);' }}" class="menu-link {{ isset($menu->submenu) ? 'menu-toggle' : '' }}">
            @isset($menu->icon)
              <i class="{{ $menu->icon }}"></i>
            @endisset
            <div>{{ $menu->name }}</div>
            @isset($menu->badge)
              <div class="badge rounded-pill bg-{{ $menu->badge[0] }} text-uppercase ms-auto">{{ $menu->badge[1] }}</div>
            @endisset
          </a>

          {{-- Submenu (difilter juga per role) --}}
          @isset($menu->submenu)
            @php
              $filteredSub = collect($menu->submenu)->filter(fn($it) => $canSee($it))->values()->all();
            @endphp
            @if(count($filteredSub))
              @include('layouts.sections.menu.submenu', ['menu' => $filteredSub, 'canSee' => $canSee])
            @endif
          @endisset
        </li>
      @endif
    @endforeach

  </ul>

</aside>
