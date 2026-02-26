<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $__env->yieldContent('title','Dashboard'); ?> - E-Sertifikat</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    :root{
      --card-radius: 18px;
    }
    body { background:#f6f7fb; }
    .sidebar { width: 280px; min-height: 100vh; background:#fff; border-right: 1px solid #e9ecef; }
    .nav-link { border-radius: .8rem; }
    .nav-link.active, .nav-link:hover { background:#eef3ff; }
    .nav-link.disabled { pointer-events: none; opacity: .55; }

    /* ✅ UI polish */
    .page-wrap { max-width: 1200px; }
    .page-title { font-weight: 700; letter-spacing: .2px; }
    .page-subtitle { color:#6c757d; }
    .card-soft { border:0; border-radius: var(--card-radius); box-shadow: 0 10px 30px rgba(16,24,40,.06); }
    .form-label { font-weight: 600; color:#344054; }
    .form-text { color:#6c757d; }
    .form-control, .form-select {
      border-radius: 12px;
      padding: .60rem .85rem;
    }
    .btn { border-radius: 12px; }
    .btn-icon { display:inline-flex; align-items:center; gap:.5rem; }
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
  </style>
</head>
<body>
<div class="d-flex">

  <aside class="sidebar">
    <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  </aside>

  <main class="flex-grow-1">
    <nav class="navbar bg-white border-bottom">
      <div class="container-fluid">
        <div class="fw-bold">E-Sertifikat</div>

        <div class="d-flex align-items-center gap-3">
          <span class="badge text-bg-light border">
            <?php echo e(auth()->user()->role->name ?? '-'); ?>

          </span>
          <span class="text-muted small"><?php echo e(auth()->user()->name); ?></span>

          <form method="POST" action="<?php echo e(route('logout')); ?>" class="m-0">
            <?php echo csrf_field(); ?>
            <button class="btn btn-outline-danger btn-sm btn-icon">
              <i class="fa-solid fa-right-from-bracket"></i> Logout
            </button>
          </form>
        </div>
      </div>
    </nav>

    <div class="container-fluid p-4">
      <div class="page-wrap">
        <?php echo $__env->yieldContent('content'); ?>
      </div>
    </div>
  </main>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html><?php /**PATH C:\laragon\www\esertifikatv1\resources\views/layouts/app.blade.php ENDPATH**/ ?>