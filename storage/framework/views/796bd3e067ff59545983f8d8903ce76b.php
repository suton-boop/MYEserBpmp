
<?php $__env->startSection('title','Persetujuan Sertifikat'); ?>

<?php $__env->startSection('content'); ?>
<?php
  $events = $events ?? collect();
  $eventId = $eventId ?? request('event_id', '');
?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
  <div>
    <h4 class="mb-0">Persetujuan Sertifikat</h4>
    <div class="text-muted">Daftar sertifikat dengan status <b>SUBMITTED</b> yang menunggu persetujuan.</div>
  </div>

  <div class="d-flex gap-2 align-items-start flex-wrap">

    
    <form method="POST" action="<?php echo e(route('admin.system.approvals.approveAll')); ?>">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="event_id" value="<?php echo e($eventId); ?>">
      <button class="btn btn-success rounded-3" <?php echo e($certificates->total() ? '' : 'disabled'); ?>

              onclick="return confirm('Approve SEMUA data yang tampil (sesuai filter)? Nomor akan dikunci permanen.')">
        <i class="fa-solid fa-check me-1"></i> Approve All
      </button>
    </form>

    
    <form method="POST" action="<?php echo e(route('admin.system.approvals.rejectAll')); ?>" class="d-flex gap-2">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="event_id" value="<?php echo e($eventId); ?>">
      <input type="text"
             name="rejected_note"
             class="form-control"
             style="min-width:320px"
             placeholder="Catatan penolakan massal (wajib)"
             required>
      <button class="btn btn-danger rounded-3" <?php echo e($certificates->total() ? '' : 'disabled'); ?>

              onclick="return confirm('Reject SEMUA data yang tampil (sesuai filter)?')">
        <i class="fa-solid fa-xmark me-1"></i> Reject All
      </button>
    </form>
  </div>
</div>

<?php if(session('success')): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <?php echo e(session('success')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if(session('error')): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <?php echo e(session('error')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>


<form method="GET" action="<?php echo e(route('admin.system.approvals.index')); ?>" class="card border-0 shadow-sm rounded-4 mb-3">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-lg-6">
        <label class="form-label small text-muted mb-1">Event</label>
        <select name="event_id" class="form-select">
          <option value="">-- Semua Event --</option>
          <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($ev->id); ?>" <?php if((string)$eventId === (string)$ev->id): echo 'selected'; endif; ?>>
              <?php echo e($ev->name); ?>

            </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      <div class="col-lg-3 d-flex gap-2">
        <button class="btn btn-primary w-100" title="Filter">
          <i class="fa-solid fa-magnifying-glass me-1"></i> Filter
        </button>
        <a class="btn btn-outline-secondary" href="<?php echo e(route('admin.system.approvals.index')); ?>" title="Reset">
          Reset
        </a>
      </div>

      <div class="col-lg-3 text-end text-muted small">
        Total: <?php echo e($certificates->total()); ?>

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
          <th>Peserta</th>
          <th>Event</th>
          <th width="20%">Diajukan</th>
          <th width="20%">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $certificates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td><?php echo e(($certificates->currentPage()-1)*$certificates->perPage() + $loop->iteration); ?></td>
            <td class="fw-semibold">
              <?php echo e($c->participant?->name ?? '-'); ?>

              <div class="text-muted small"><?php echo e($c->participant?->institution ?? ''); ?></div>
            </td>
            <td><?php echo e($c->event?->name ?? '-'); ?></td>
            <td class="text-muted">
              <?php echo e($c->submitted_at?->format('d M Y H:i') ?? '-'); ?>

            </td>
            <td class="d-flex gap-2">
              <form method="POST" action="<?php echo e(route('admin.system.approvals.approve', $c->id)); ?>">
                <?php echo csrf_field(); ?>
                <button class="btn btn-success btn-sm rounded-3"
                        onclick="return confirm('Approve sertifikat ini? Nomor akan dikunci.')">
                  Approve
                </button>
              </form>

              <form method="POST" action="<?php echo e(route('admin.system.approvals.reject', $c->id)); ?>" class="d-flex gap-2">
                <?php echo csrf_field(); ?>
                <input type="text" name="rejected_note" class="form-control form-control-sm"
                       placeholder="Catatan reject" required>
                <button class="btn btn-danger btn-sm rounded-3"
                        onclick="return confirm('Reject sertifikat ini?')">
                  Reject
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="5" class="text-center text-muted py-4">
              Tidak ada sertifikat yang menunggu persetujuan.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if($certificates->hasPages()): ?>
    <div class="card-footer bg-white border-0">
      <?php echo e($certificates->links()); ?>

    </div>
  <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\esertifikatv1\resources\views/admin/approvals/index.blade.php ENDPATH**/ ?>