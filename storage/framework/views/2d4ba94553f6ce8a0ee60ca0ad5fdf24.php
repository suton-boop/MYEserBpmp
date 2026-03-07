
<?php $__env->startSection('title', 'Signer Certificates'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <h4 class="mb-0">Signer Certificates</h4>
    <div class="text-muted">Kelola kunci RSA (encrypted at rest) untuk penandatangan.</div>
  </div>
  <a class="btn btn-primary" href="<?php echo e(route('admin.tte.signers.create')); ?>">Tambah Signer</a>
</div>

<?php if(session('success')): ?>
  <div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if(session('error')): ?>
  <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
<?php endif; ?>

<div class="card rounded-3">
  <div class="table-responsive">
    <table class="table table-striped mb-0 align-middle">
      <thead>
        <tr>
          <th>Code</th>
          <th>Name</th>
          <th>Fingerprint</th>
          <th>Status</th>
          <th>Valid</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td class="fw-semibold"><?php echo e($it->code); ?></td>
            <td><?php echo e($it->name); ?></td>
            <td><code><?php echo e(substr($it->private_key_fingerprint,0,20)); ?>…</code></td>
            <td>
              <?php if($it->is_active): ?>
                <span class="badge bg-success">Active</span>
              <?php else: ?>
                <span class="badge bg-secondary">Inactive</span>
              <?php endif; ?>
            </td>
            <td class="text-muted">
              <?php echo e($it->valid_from?->format('Y-m-d') ?? '-'); ?> → <?php echo e($it->valid_to?->format('Y-m-d') ?? '-'); ?>

            </td>
            <td class="text-end">
              <?php if($it->is_active): ?>
                <form method="POST" action="<?php echo e(route('admin.tte.signers.deactivate', $it->id)); ?>" class="d-inline">
                  <?php echo csrf_field(); ?>
                  <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Nonaktifkan signer ini?')">
                    Deactivate
                  </button>
                </form>
              <?php else: ?>
                <form method="POST" action="<?php echo e(route('admin.tte.signers.activate', $it->id)); ?>" class="d-inline">
                  <?php echo csrf_field(); ?>
                  <button class="btn btn-sm btn-outline-success" onclick="return confirm('Aktifkan signer ini?')">
                    Activate
                  </button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Belum ada signer.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">
  <?php echo e($items->links()); ?>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\esertifikatv1\resources\views/admin/tte/signers/index.blade.php ENDPATH**/ ?>