@php
  $menus = config('menu');
  $user = auth()->user();
@endphp

<div class="p-3">

  <div class="d-flex align-items-center gap-2 mb-3">
    <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center"
         style="width:36px;height:36px;">
      <i class="fa-solid fa-award"></i>
    </div>
    <div>
      <div class="fw-bold">Panel Admin</div>
      <div class="text-muted small">{{ $user->email }}</div>
    </div>
  </div>

  <ul class="nav nav-pills flex-column gap-1">

    @foreach ($menus as $m)

      {{-- Divider --}}
      @if(isset($m['divider']) && $m['divider'] === true)
        <hr class="my-3">
        @continue
      @endif

      {{-- Header --}}
      @if(isset($m['header']))
        @php
          $showHeader = true;

          if(isset($m['permissionAny'])) {
            $showHeader = false;
            foreach($m['permissionAny'] as $p) {
              if($user->hasPermission($p)) { $showHeader = true; break; }
            }
          }
        @endphp

        @if($showHeader)
          <li class="mt-2 px-2 text-uppercase small text-muted">
            {{ $m['header'] }}
          </li>
        @endif
        @continue
      @endif

      {{-- Permission Check --}}
      @php
        $can = isset($m['permission']) ? $user->hasPermission($m['permission']) : true;
      @endphp

      @if($can)
        @php
          $routeName = $m['route'] ?? null;

          $href = '#';
          $disabled = true;

          if($routeName && \Illuminate\Support\Facades\Route::has($routeName)) {
            $href = route($routeName);
            $disabled = false;
          }

          $active = $routeName && request()->routeIs($routeName) ? 'active' : '';
        @endphp

        <li class="nav-item">
          <a class="nav-link d-flex align-items-center gap-2 {{ $active }} {{ $disabled ? 'disabled' : '' }}"
             href="{{ $href }}">
            <i class="{{ $m['icon'] }}"></i>
            <span>{{ $m['title'] }}</span>
          </a>
        </li>
      @endif

    @endforeach

  </ul>

  <hr class="my-3">

  <a class="btn btn-outline-secondary w-100" href="{{ route('landing') }}">
    <i class="fa-solid fa-globe me-1"></i> Halaman Publik
  </a>

</div>
