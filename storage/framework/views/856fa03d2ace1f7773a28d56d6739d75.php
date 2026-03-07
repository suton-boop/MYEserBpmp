
<?php $__env->startSection('title','Sertifikat Terbit'); ?>

<?php $__env->startSection('content'); ?>
<?php
  $events       = $events ?? collect();
  $certificates = $certificates ?? null;

  $q       = $q ?? request('q', '');
  $eventId = $eventId ?? request('event_id', '');
  $sortBy  = $sortBy ?? request('sort', 'latest');
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1">Sertifikat Terbit</h4>
    <div class="text-muted small">Daftar sertifikat yang telah diterbitkan (TTE).</div>
  </div>
</div>

<style>
    .form-label-top { font-size: 0.75rem; font-weight: 600; color: #6c757d; display: block; margin-bottom: 4px; }
    .filter-card { border: none !important; transition: all 0.3s ease; }
    .filter-card:hover { transform: translateY(-2px); }
</style>

<form method="GET" action="<?php echo e(route('admin.certificates.published')); ?>"
      class="card filter-card border-0 shadow-sm rounded-4 mb-3">
  <div class="card-body p-3">
    <div class="row g-3">
      <div class="col-lg-4">
        <label class="form-label-top"><i class="fa-solid fa-magnifying-glass me-1"></i> Cari</label>
        <input type="text" name="q" class="form-control rounded-3" value="<?php echo e($q); ?>"
               placeholder="Nama, nomor referensi, atau NIK...">
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

      <div class="col-lg-3">
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
        <a class="btn btn-outline-secondary h-100 px-3 rounded-3 d-flex align-items-center" href="<?php echo e(route('admin.certificates.published')); ?>">
          <i class="fa-solid fa-rotate-left"></i>
        </a>
      </div>
    </div>
  </div>
</form>

<div class="card border-0 shadow-sm rounded-4">
  <div class="card-header bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <div class="fw-semibold">Daftar Sertifikat Terbit</div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="text-muted small">Total: <?php echo e($certificates?->total() ?? 0); ?></div>
        
        <?php if(!empty($eventId) && in_array(auth()->user()->role?->name, ['admin', 'superadmin'])): ?>
            <a href="<?php echo e(route('admin.system.events.downloadSigned', $eventId)); ?>" class="btn btn-sm btn-primary fw-medium">
                <i class="fa-solid fa-file-pdf me-1"></i> Download Semua PDF TTE
            </a>
        <?php endif; ?>

        <a href="<?php echo e(route('admin.certificates.published.export', request()->query())); ?>" class="btn btn-sm btn-success text-white fw-medium">
            <i class="fa-solid fa-file-excel me-1"></i> Eksport ke Excel
        </a>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th width="5%">#</th>
          <th>Nama Peserta</th>
          <th>Nomor Sertifikat</th>
          <th>Event</th>
          <th>Status</th>
          <th width="15%" class="text-end">Aksi</th>
        </tr>
      </thead>

      <tbody>
      <?php if(!$certificates || $certificates->isEmpty()): ?>
        <tr>
          <td colspan="6" class="text-center text-muted py-4">Belum ada sertifikat terbit.</td>
        </tr>
      <?php else: ?>
        <?php $__currentLoopData = $certificates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td><?php echo e(($certificates->currentPage()-1)*$certificates->perPage() + $loop->iteration); ?></td>

            <td class="fw-semibold">
              <?php echo e($cert->participant->name ?? '-'); ?>

              <?php if($cert->participant->institution): ?>
                <div class="text-muted small"><?php echo e($cert->participant->institution); ?></div>
              <?php endif; ?>
            </td>

            <td>
              <span class="fw-semibold text-primary"><?php echo e($cert->certificate_number ?? '-'); ?></span>
            </td>

            <td><?php echo e($cert->event?->name ?? '-'); ?></td>

            <td>
              <span class="badge bg-success"><i class="fa-solid fa-check-circle me-1"></i> Terbit</span>
            </td>

            <td class="text-end">
              <div class="d-inline-flex gap-2">

                <?php
                  $hasPdf = !empty($cert->signed_pdf_path) || !empty($cert->pdf_path);
                ?>

                <?php if($hasPdf): ?>
                  
                  <a class="btn btn-outline-success btn-sm rounded-3"
                     href="<?php echo e(route('admin.certificates.view', $cert->id)); ?>"
                     target="_blank"
                     title="Preview PDF Final">
                    <i class="fa-solid fa-eye"></i>
                  </a>

                  
                  <a class="btn btn-outline-primary btn-sm rounded-3"
                     href="<?php echo e(route('admin.certificates.download', $cert->id)); ?>"
                     title="Download PDF">
                    <i class="fa-solid fa-download"></i>
                  </a>
                <?php else: ?>
                  <button class="btn btn-outline-secondary btn-sm rounded-3" disabled title="PDF belum ada">
                    <i class="fa-solid fa-eye"></i>
                  </button>
                  <button class="btn btn-outline-secondary btn-sm rounded-3" disabled title="PDF belum ada">
                    <i class="fa-solid fa-download"></i>
                  </button>
                <?php endif; ?>

              </div>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if($certificates && $certificates->hasPages()): ?>
    <div class="card-footer bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div class="text-muted small">
        Menampilkan <?php echo e($certificates->firstItem()); ?> - <?php echo e($certificates->lastItem()); ?>

        dari <?php echo e($certificates->total()); ?> data
      </div>
      <div><?php echo e($certificates->links()); ?></div>
    </div>
  <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\esertifikatv1\resources\views/certificates/published.blade.php ENDPATH**/ ?>