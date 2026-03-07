
<?php $__env->startSection('title','Manajemen Event'); ?>

<?php $__env->startSection('content'); ?>
<?php
  $q      = $q ?? request('q', '');
  $status = $status ?? request('status', '');
?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
  <div>
    <h4 class="mb-0">Manajemen Event</h4>
    <div class="text-muted">Kelola daftar event untuk e-sertifikat.</div>
  </div>

  <?php if(auth()->user()->role?->name !== 'operator'): ?>
  <a href="<?php echo e(route('admin.system.events.create')); ?>" class="btn btn-primary rounded-3">
    <i class="fa-solid fa-plus me-1"></i> Tambah Event
  </a>
  <?php endif; ?>
</div>

<?php if(session('success')): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo e(session('success')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if(session('error')): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo e(session('error')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>


<form method="GET" action="<?php echo e(route('admin.system.events.index')); ?>" class="card border-0 shadow-sm rounded-4 mb-3">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-lg-6">
        <label class="form-label small text-muted mb-1">Cari</label>
        <input
          type="text"
          name="q"
          class="form-control"
          value="<?php echo e($q); ?>"
          placeholder="Cari nama event / lokasi..."
        >
      </div>

      <div class="col-lg-3">
        <label class="form-label small text-muted mb-1">Status</label>
        <select name="status" class="form-select">
          <option value="">-- Semua Status --</option>
          <option value="draft"  <?php if($status==='draft'): echo 'selected'; endif; ?>>Draft</option>
          <option value="active" <?php if($status==='active'): echo 'selected'; endif; ?>>Active</option>
          <option value="closed" <?php if($status==='closed'): echo 'selected'; endif; ?>>Closed</option>
        </select>
      </div>

      <div class="col-lg-3 d-flex gap-2">
        <button class="btn btn-primary w-100" title="Cari" type="submit">
          <i class="fa-solid fa-magnifying-glass me-1"></i> Cari
        </button>
        <a class="btn btn-outline-secondary" href="<?php echo e(route('admin.system.events.index')); ?>" title="Reset">
          Reset
        </a>
      </div>
    </div>
  </div>
</form>

<div class="card border-0 shadow-sm rounded-4">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th width="5%">#</th>
          <th>Nama Event</th>
          <th>Tanggal</th>
          <th>Lokasi</th>
          <th width="10%" class="text-center">Peserta</th>
          <th width="10%">Status</th>
          <?php if(auth()->user()->role?->name !== 'operator'): ?>
          <th width="12%">Aksi</th>
          <?php endif; ?>
        </tr>
      </thead>

      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <?php
            $st = $e->status ?? 'draft';

            $badge = match($st) {
              'active' => 'bg-success',
              'closed' => 'bg-secondary',
              'draft'  => 'bg-warning text-dark',
              default  => 'bg-light text-dark',
            };

            $label = match($st) {
              'active' => 'Active',
              'closed' => 'Closed',
              'draft'  => 'Draft',
              default  => ucfirst((string)$st),
            };

            $start = $e->start_date?->format('d M Y');
            $end   = $e->end_date?->format('d M Y');
          ?>

          <tr>
            <td><?php echo e(($events->currentPage()-1)*$events->perPage() + $loop->iteration); ?></td>

            <td class="fw-semibold">
              <?php echo e($e->name); ?>

              <?php if(!empty($e->description)): ?>
                <div class="text-muted small text-truncate" style="max-width:520px;">
                  <?php echo e($e->description); ?>

                </div>
              <?php endif; ?>
            </td>

            <td>
              <?php echo e($start ?? '-'); ?>

              <?php if($end): ?>
                <span class="text-muted">-</span> <?php echo e($end); ?>

              <?php endif; ?>
            </td>

            <td><?php echo e($e->location ?? '-'); ?></td>

            <td class="text-center">
              <span class="badge bg-info">
                <?php echo e($e->participants_count ?? 0); ?>

              </span>
            </td>

            <td>
              <span class="badge <?php echo e($badge); ?>"><?php echo e($label); ?></span>
            </td>

            <?php if(auth()->user()->role?->name !== 'operator'): ?>
            <td>
              <div class="d-flex gap-2">
                <?php if(in_array(auth()->user()->role?->name, ['admin', 'superadmin'])): ?>
                <a
                  href="<?php echo e(route('admin.system.events.downloadSigned', $e->id)); ?>"
                  class="btn btn-success btn-sm rounded-3"
                  title="Download Sertifikat TTE"
                >
                  <i class="fa-solid fa-file-pdf"></i>
                </a>
                <?php endif; ?>

                <a
                  href="<?php echo e(route('admin.system.events.edit', $e->id)); ?>"
                  class="btn btn-warning btn-sm rounded-3"
                  title="Edit"
                >
                  <i class="fa-solid fa-pen-to-square"></i>
                </a>

                <form
                  action="<?php echo e(route('admin.system.events.destroy', $e->id)); ?>"
                  method="POST"
                  onsubmit="return confirm('Yakin hapus event ini?')"
                >
                  <?php echo csrf_field(); ?>
                  <?php echo method_field('DELETE'); ?>
                  <button class="btn btn-danger btn-sm rounded-3" title="Hapus" type="submit">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </form>
              </div>
            </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="<?php echo e(auth()->user()->role?->name !== 'operator' ? 7 : 6); ?>" class="text-center text-muted py-4">Belum ada event.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if($events->hasPages()): ?>
    <div class="card-footer bg-white border-0">
      <?php echo e($events->links()); ?>

    </div>
  <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\esertifikatv1\resources\views/admin/system/events/index.blade.php ENDPATH**/ ?>