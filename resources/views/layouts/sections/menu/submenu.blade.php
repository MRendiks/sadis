@php
  // Jika helper $canSee tidak dikirim dari parent, define ulang biar aman
  if (!isset($canSee) || !is_callable($canSee)) {
      $user = auth()->user();

      // Ambil nama role user (pakai Spatie atau kolom role biasa)
      $roleNames = collect(method_exists($user, 'getRoleNames') ? $user->getRoleNames() : [optional($user)->role])
          ->filter()
          ->values()
          ->all();

      // Helper cek apakah item boleh terlihat
      $canSee = function ($item) use ($roleNames) {
          if (!isset($item->role)) return true;
          $required = is_array($item->role) ? $item->role : [$item->role];
          return count(array_intersect($roleNames, $required)) > 0;
      };
  }
@endphp

<ul class="menu-sub">
  @foreach ($menu as $child)
    @if ($canSee($child))
      <li class="menu-item {{ (isset($child->active) && $child->active) ? 'active' : '' }}">
        <a href="{{ $child->url ?? 'javascript:void(0);' }}" class="menu-link">
          @isset($child->icon)
            <i class="{{ $child->icon }}"></i>
          @endisset
          <div>{{ $child->name ?? '' }}</div>
          @isset($child->badge)
            <div class="badge rounded-pill bg-{{ $child->badge[0] }} text-uppercase ms-auto">{{ $child->badge[1] }}</div>
          @endisset
        </a>

        {{-- Jika masih punya submenu di dalamnya, render recursive --}}
        @isset($child->submenu)
          @php
            $filtered = collect($child->submenu)
                ->filter(fn($sub) => $canSee($sub))
                ->values()
                ->all();
          @endphp

          @if (count($filtered))
            @include('layouts.sections.menu.submenu', ['menu' => $filtered, 'canSee' => $canSee])
          @endif
        @endisset
      </li>
    @endif
  @endforeach
</ul>
