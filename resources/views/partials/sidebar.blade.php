@php
  $menus = config('menu', []);
  $user  = auth()->user();
@endphp

<div class="p-3">
  <div class="d-flex align-items-center gap-2 mb-3">
    <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
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
      @if(!empty($m['divider']))
        <hr class="my-2">
        @continue
      @endif

      {{-- Header --}}
      @if(isset($m['header']))
        @php
          $showHeader = true;

          if(isset($m['permissionAny']) && is_array($m['permissionAny'])) {
            $showHeader = false;
            foreach ($m['permissionAny'] as $p) {
              if($user->hasPermission($p)) { $showHeader = true; break; }
            }
          }
        @endphp

        @if($showHeader)
          <li class="mt-3 px-2 text-uppercase small text-muted">{{ $m['header'] }}</li>
        @endif
        @continue
      @endif

      @php
        // Permission check
        $can = true;
        if(!empty($m['permission'])) {
          $can = $user->hasPermission($m['permission']);
        }

        // Route check
        $routeName = $m['route'] ?? null;
        $routeOk   = $routeName && \Illuminate\Support\Facades\Route::has($routeName);

        // Active pattern (boleh string / array)
        $activePattern = $m['active'] ?? $routeName;
        $active = '';
        if($routeOk) {
          if(is_array($activePattern)) {
            foreach($activePattern as $pat) {
              if(request()->routeIs($pat)) { $active = 'active'; break; }
            }
          } else {
            $active = request()->routeIs($activePattern) ? 'active' : '';
          }
        }

        $href = $routeOk ? route($routeName) : '#';
      @endphp

      @if($can)
        <li class="nav-item">
          <a class="nav-link d-flex align-items-center gap-2 {{ $active }}"
             href="{{ $href }}"
             @if(!$routeOk) aria-disabled="true" title="Route belum tersedia: {{ $routeName }}" @endif>
            <i class="{{ $m['icon'] ?? 'fa-solid fa-circle' }}"></i>
            <span>{{ $m['title'] ?? '-' }}</span>
          </a>
        </li>
      @endif

    @endforeach
  </ul>

  <hr class="my-3">

  <a class="btn btn-outline-secondary w-100" href="{{ route('public.home') }}" target="_blank" rel="noopener">
    <i class="fa-solid fa-globe me-1"></i> Halaman Publik
  </a>
</div>