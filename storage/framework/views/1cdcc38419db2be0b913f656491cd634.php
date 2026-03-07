
<?php $__env->startSection('title','Kelola Peserta'); ?>

<?php $__env->startSection('content'); ?>
<?php
  /** @var \Illuminate\Pagination\LengthAwarePaginator $participants */
  $events  = $events ?? collect();

  $q       = $q ?? request('q', '');
  $eventId = $eventId ?? request('event_id', '');
  $status  = $status ?? request('status', '');
  $sortBy  = $sortBy ?? request('sort', 'latest');
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1">Kelola Peserta</h4>
    <div class="text-muted small">Kelola basis data peserta dan sertifikat berdasarkan event.</div>
  </div>

  <div class="d-flex flex-wrap gap-2">
    
    <div class="bg-white p-1 rounded-4 shadow-sm border d-flex gap-1">
        <a href="<?php echo e(route('admin.participants.import.form', ['event_id' => $eventId])); ?>"
           class="btn btn-soft-primary rounded-3 btn-sm px-3 py-2">
          <i class="fa-solid fa-file-import me-1 small"></i> Import Data
        </a>

        <a href="<?php echo e(route('admin.participants.create', ['event_id' => $eventId])); ?>"
           class="btn btn-primary rounded-3 btn-sm px-3 py-2 shadow-sm">
          <i class="fa-solid fa-plus me-1 small"></i> Tambah Peserta
        </a>
    </div>
  </div>
</div>

<style>
    .btn-soft-primary {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
        border: 1px solid rgba(13, 110, 253, 0.2);
    }
    .btn-soft-primary:hover {
        background-color: #0d6efd;
        color: #fff;
    }
    .form-label-top { font-size: 0.75rem; font-weight: 600; color: #6c757d; display: block; margin-bottom: 4px; }
    .filter-card { border: none !important; transition: all 0.3s ease; }
    .filter-card:hover { transform: translateY(-2px); }
</style>

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


<form method="GET" action="<?php echo e(route('admin.participants.index')); ?>" 
      class="card filter-card border-0 shadow-sm rounded-4 mb-3">
  <div class="card-body p-3">
    <div class="row g-3">

      <div class="col-lg-3">
        <label class="form-label-top"><i class="fa-solid fa-magnifying-glass me-1"></i> Cari</label>
        <input type="text" name="q" class="form-control rounded-3" value="<?php echo e($q); ?>"
               placeholder="Nama, email, atau NIK...">
      </div>

      <div class="col-lg-3">
        <label class="form-label-top"><i class="fa-solid fa-calendar-event me-1"></i> Event</label>
        <select name="event_id" class="form-select rounded-3">
          <option value="">-- Semua Event --</option>
          <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($ev->id); ?>" <?php if((string)$eventId === (string)$ev->id): echo 'selected'; endif; ?>>
              <?php echo e($ev->name); ?>

            </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      <div class="col-lg-2">
        <label class="form-label-top"><i class="fa-solid fa-tag me-1"></i> Status</label>
        <select name="status" class="form-select rounded-3">
          <option value="">-- Semua Status --</option>
          <option value="draft"  <?php if($status === 'draft'): echo 'selected'; endif; ?>>Draft</option>
          <option value="terbit" <?php if($status === 'terbit'): echo 'selected'; endif; ?>>Terbit</option>
        </select>
      </div>

      <div class="col-lg-2">
        <label class="form-label-top"><i class="fa-solid fa-sort me-1"></i> Urutan</label>
        <select name="sort" class="form-select rounded-3">
          <option value="latest" <?php if($sortBy === 'latest'): echo 'selected'; endif; ?>>Terbaru</option>
          <option value="name_asc" <?php if($sortBy === 'name_asc'): echo 'selected'; endif; ?>>Nama A-Z</option>
          <option value="name_desc" <?php if($sortBy === 'name_desc'): echo 'selected'; endif; ?>>Nama Z-A</option>
          <option value="oldest" <?php if($sortBy === 'oldest'): echo 'selected'; endif; ?>>Terlama</option>
        </select>
      </div>

      <div class="col-lg-2 d-flex align-items-end gap-2">
        <button class="btn btn-primary h-100 flex-grow-1 rounded-3">
          <i class="fa-solid fa-filter me-1"></i> Filter
        </button>
        <a class="btn btn-outline-secondary h-100 px-3 rounded-3 d-flex align-items-center" href="<?php echo e(route('admin.participants.index')); ?>">
          <i class="fa-solid fa-rotate-left"></i>
        </a>
      </div>

    </div>
  </div>
</form>


<div class="card border-0 shadow-sm rounded-4">
  <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
    <div class="fw-semibold">Daftar Peserta</div>
    <div class="text-muted small">
      Total: <?php echo e($participants->total()); ?>

    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th width="5%">#</th>
          <th>Nama / Lokasi</th>
          <th>Email</th>
          <th>Event</th>
          <th width="120px" class="text-center">Status</th>
          <th width="100px" class="text-center">Aksi</th>
        </tr>
      </thead>

      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $participants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td><?php echo e(($participants->currentPage()-1)*$participants->perPage() + $loop->iteration); ?></td>

            <td class="fw-semibold">
              <?php echo e($p->name); ?>

              <div class="text-muted small">
                <?php echo e($p->institution ?? ''); ?>

                <?php if($p->jenjang): ?> - <?php echo e($p->jenjang); ?> <?php endif; ?>
                <?php if($p->daerah): ?> (<?php echo e($p->daerah); ?>) <?php endif; ?>
              </div>
              <?php if($p->peran): ?>
                <span class="badge bg-light text-dark border small mt-1"><?php echo e($p->peran); ?></span>
              <?php endif; ?>
            </td>

            <td><?php echo e($p->email ?? '-'); ?></td>

            <td class="text-truncate" style="max-width: 320px;">
              <?php echo e($p->event?->name ?? '-'); ?>

            </td>

            <td class="text-center">
              <?php
              $label = $p->cert_status ?? $p->status ?? 'draft';

              $badgeMap = [
                'draft'           => 'bg-dark bg-opacity-10 text-dark border-dark border-opacity-25',
                'pending'         => 'bg-warning bg-opacity-10 text-warning-emphasis border-warning border-opacity-25',
                'submitted'       => 'bg-warning bg-opacity-25 text-warning-emphasis border-warning border-opacity-50',
                'approved'        => 'bg-primary bg-opacity-10 text-primary border-primary border-opacity-25',
                'rejected'        => 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25',
                'final_generated' => 'bg-info bg-opacity-10 text-info border-info border-opacity-25',
                'signed'          => 'bg-success bg-opacity-10 text-success border-success border-opacity-25',
              ];

              $badge = $badgeMap[$label] ?? 'bg-secondary bg-opacity-10';
            ?>

              <span class="badge border <?php echo e($badge); ?> rounded-pill px-3 py-2 fw-medium shadow-none" style="font-size: 0.75rem; min-width: 100px;">
                <i class="fa-solid fa-circle me-1" style="font-size: 0.5rem; opacity: 0.6;"></i>
                <?php echo e(strtoupper($label)); ?>

              </span>
            </td>

            <td class="text-center">
              <div class="d-flex justify-content-center gap-1">
                <a href="<?php echo e(route('admin.participants.edit', $p->id)); ?>"
                   class="btn btn-outline-warning btn-sm border-0 rounded-circle active-shadow"
                   style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;"
                   title="Edit">
                  <i class="fa-solid fa-pen-to-square"></i>
                </a>

                <form action="<?php echo e(route('admin.participants.destroy', $p->id)); ?>"
                      method="POST"
                      onsubmit="return confirm('Yakin hapus peserta ini?')"
                      class="d-inline">
                  <?php echo csrf_field(); ?>
                  <?php echo method_field('DELETE'); ?>
                  <button class="btn btn-outline-danger btn-sm border-0 rounded-circle active-shadow"
                          style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;"
                          title="Hapus">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="6" class="text-center text-muted py-4">Belum ada peserta.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>


<?php if($participants && $participants->hasPages()): ?>
  <div class="card-footer bg-white border-0">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">

      <div class="text-muted small">
        Showing <strong><?php echo e($participants->firstItem()); ?></strong>
        to <strong><?php echo e($participants->lastItem()); ?></strong>
        of <strong><?php echo e($participants->total()); ?></strong> entries
      </div>

      <div class="d-flex justify-content-end">
        <?php echo e($participants->onEachSide(1)->links()); ?>

      </div>

    </div>
  </div>
<?php endif; ?>

</div>
<?php $__env->stopSection(); ?>


=
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\esertifikatv1\resources\views/participants/index.blade.php ENDPATH**/ ?>