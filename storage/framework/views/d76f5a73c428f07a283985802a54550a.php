<?php
  $menus = config('menu', []);
  $user  = auth()->user();
?>

<div class="p-3">
  <div class="d-flex align-items-center gap-2 mb-3">
    <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
      <i class="fa-solid fa-award"></i>
    </div>
    <div>
      <div class="fw-bold">Panel Admin</div>
      <div class="text-muted small"><?php echo e($user->email); ?></div>
    </div>
  </div>

  <ul class="nav nav-pills flex-column gap-1">
    <?php $__currentLoopData = $menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

      
      <?php if(!empty($m['divider'])): ?>
        <hr class="my-2">
        <?php continue; ?>
      <?php endif; ?>

      
      <?php if(isset($m['header'])): ?>
        <?php
          $showHeader = true;

          if(isset($m['permissionAny']) && is_array($m['permissionAny'])) {
            $showHeader = false;
            foreach ($m['permissionAny'] as $p) {
              if($user->hasPermission($p)) { $showHeader = true; break; }
            }
          }
        ?>

        <?php if($showHeader): ?>
          <li class="mt-3 px-2 text-uppercase small text-muted"><?php echo e($m['header']); ?></li>
        <?php endif; ?>
        <?php continue; ?>
      <?php endif; ?>

      <?php
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
      ?>

      <?php if($can): ?>
        <li class="nav-item">
          <a class="nav-link d-flex align-items-center gap-2 <?php echo e($active); ?>"
             href="<?php echo e($href); ?>"
             <?php if(!$routeOk): ?> aria-disabled="true" title="Route belum tersedia: <?php echo e($routeName); ?>" <?php endif; ?>>
            <i class="<?php echo e($m['icon'] ?? 'fa-solid fa-circle'); ?>"></i>
            <span><?php echo e($m['title'] ?? '-'); ?></span>
          </a>
        </li>
      <?php endif; ?>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </ul>

  <hr class="my-3">

  <a class="btn btn-outline-secondary w-100" href="<?php echo e(route('public.home')); ?>" target="_blank" rel="noopener">
    <i class="fa-solid fa-globe me-1"></i> Halaman Publik
  </a>
</div><?php /**PATH C:\laragon\www\esertifikatv1\resources\views/partials/sidebar.blade.php ENDPATH**/ ?>