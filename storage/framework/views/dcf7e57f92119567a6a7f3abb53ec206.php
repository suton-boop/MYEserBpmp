
<?php $__env->startSection('title','Generate Sertifikat'); ?>

<?php $__env->startSection('content'); ?>
<?php
  $events       = $events ?? collect();
  $participants = $participants ?? null;

  $q       = $q ?? request('q', '');
  $eventId = $eventId ?? request('event_id', '');
  $status  = $status ?? request('status', '');
  $certMap = $certMap ?? collect();
  $sortBy  = $sortBy ?? request('sort', 'latest');
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1">Generate Sertifikat</h4>
    <div class="text-muted small">Kelola penerbitan sertifikat digital secara masal.</div>
  </div>

  <div class="d-flex flex-wrap gap-2">
    
    <div class="bg-white p-1 rounded-4 shadow-sm border d-flex gap-1">
        <form method="POST" action="<?php echo e(route('admin.certificates.generateAll')); ?>">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="event_id" value="<?php echo e($eventId); ?>">
          <button class="btn btn-primary rounded-3 btn-sm px-3 py-2" <?php echo e($eventId ? '' : 'disabled'); ?>>
            <i class="fa-solid fa-wand-magic-sparkles me-1 small"></i> Draft Semua
          </button>
        </form>

        <form method="POST" action="<?php echo e(route('admin.certificates.submitAll')); ?>">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="event_id" value="<?php echo e($eventId); ?>">
          <button class="btn btn-outline-primary rounded-3 btn-sm px-3 py-2" <?php echo e($eventId ? '' : 'disabled'); ?>>
            <i class="fa-solid fa-paper-plane me-1 small"></i> Ajukan Semua
          </button>
        </form>

        <form method="POST" action="<?php echo e(route('admin.certificates.generatePdfAll')); ?>">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="event_id" value="<?php echo e($eventId); ?>">
          <button class="btn btn-soft-success rounded-3 btn-sm px-3 py-2" <?php echo e($eventId ? '' : 'disabled'); ?>>
            <i class="fa-solid fa-file-pdf me-1 small"></i> Generate PDF (Approved)
          </button>
        </form>
    </div>
  </div>
</div>

<style>
    .btn-soft-success {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.2);
    }
    .btn-soft-success:hover {
        background-color: #198754;
        color: #fff;
    }
    .form-label-top { font-size: 0.75rem; font-weight: 600; color: #6c757d; display: block; margin-bottom: 4px; }
    .filter-card { border: none !important; transition: all 0.3s ease; }
    .filter-card:hover { transform: translateY(-2px); }
</style>

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

<form method="GET" action="<?php echo e(route('admin.certificates.index')); ?>"
      class="card filter-card shadow-sm rounded-4 mb-3 border-0">
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
          <option value="">-- Pilih Event --</option>
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
          <option value="">-- Semua --</option>
          <option value="draft" <?php if($status === 'draft'): echo 'selected'; endif; ?>>Draft</option>
          <option value="submitted" <?php if($status === 'submitted'): echo 'selected'; endif; ?>>Submitted</option>
          <option value="approved" <?php if($status === 'approved'): echo 'selected'; endif; ?>>Approved</option>
          <option value="final_generated" <?php if($status === 'final_generated'): echo 'selected'; endif; ?>>Final Generated</option>
          <option value="signed" <?php if($status === 'signed'): echo 'selected'; endif; ?>>Signed</option>
          <option value="rejected" <?php if($status === 'rejected'): echo 'selected'; endif; ?>>Rejected</option>
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
        <a class="btn btn-outline-secondary h-100 px-3 rounded-3 d-flex align-items-center" href="<?php echo e(route('admin.certificates.index')); ?>">
          <i class="fa-solid fa-rotate-left"></i>
        </a>
      </div>
    </div>
  </div>
</form>

<div class="card border-0 shadow-sm rounded-4">
  <div class="card-header bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <div class="fw-semibold">Daftar Peserta</div>
      <div class="text-muted small">
        File PDF tersimpan di: <code>storage/app/public/{pdf_path}</code> dan bisa diakses:
        <code>public/storage/{pdf_path}</code> (setelah <code>php artisan storage:link</code>)
      </div>
    </div>
    <div class="text-muted small">Total: <?php echo e($participants?->total() ?? 0); ?></div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th width="5%">#</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Event</th>
          <th width="16%">Sertifikat</th>
          <th width="22%" class="text-end">Aksi</th>
        </tr>
      </thead>

      <tbody>
      <?php if(!$participants): ?>
        <tr>
          <td colspan="6" class="text-center text-muted py-4">
            Data peserta belum dikirim dari controller.
          </td>
        </tr>
      <?php else: ?>
        <?php $__empty_1 = true; $__currentLoopData = $participants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <?php
            $key      = ($p->event_id ?? 0).':'.$p->id;
            $cert     = $certMap->get($key);
            $hasCert  = !empty($cert);
            $hasPdf   = $hasCert && !empty($cert->pdf_path);

            $statusVal = $hasCert ? ($cert->status ?? \App\Models\Certificate::STATUS_DRAFT) : null;

            $badgeClass = match($statusVal) {
              \App\Models\Certificate::STATUS_DRAFT => 'bg-warning text-dark',
              \App\Models\Certificate::STATUS_SUBMITTED => 'bg-info text-dark',
              \App\Models\Certificate::STATUS_APPROVED => 'bg-primary',
              \App\Models\Certificate::STATUS_FINAL_GENERATED => 'bg-success',
              \App\Models\Certificate::STATUS_SIGNED => 'bg-success',
              \App\Models\Certificate::STATUS_REJECTED => 'bg-danger',
              default => 'bg-secondary'
            };
          ?>

          <tr>
            <td><?php echo e(($participants->currentPage()-1)*$participants->perPage() + $loop->iteration); ?></td>

            <td class="fw-semibold">
              <?php echo e($p->name); ?>

              <?php if($p->institution): ?>
                <div class="text-muted small"><?php echo e($p->institution); ?></div>
              <?php endif; ?>
            </td>

            <td><?php echo e($p->email ?? '-'); ?></td>
            <td><?php echo e($p->event?->name ?? '-'); ?></td>

            <td>
              <?php if($hasCert): ?>
                <span class="badge <?php echo e($badgeClass); ?>"><?php echo e(ucfirst($statusVal)); ?></span>
                <div class="text-muted small mt-1">
                  <?php echo e($cert->certificate_number ?? $cert->certificate_no ?? $cert->certificate_no ?? '-'); ?>

                </div>
              <?php else: ?>
                <span class="text-muted small">Belum ada</span>
              <?php endif; ?>
            </td>

            <td class="text-end">
              <div class="d-inline-flex gap-2">

                
                <form method="POST" action="<?php echo e(route('admin.certificates.generateOne', $p->id)); ?>" class="d-inline">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="event_id" value="<?php echo e($p->event_id); ?>">
                  <button class="btn btn-warning btn-sm rounded-3"
                          title="<?php echo e($hasCert ? 'Draft sudah ada' : 'Generate Draft'); ?>"
                          <?php echo e($hasCert ? 'disabled' : ''); ?>>
                    <i class="fa-solid fa-bolt"></i>
                  </button>
                </form>

                
                <?php if($hasCert && $statusVal === \App\Models\Certificate::STATUS_DRAFT): ?>
                  <form method="POST" action="<?php echo e(route('admin.certificates.submit', $cert->id)); ?>" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-primary btn-sm rounded-3" title="Ajukan ke Persetujuan">
                      <i class="fa-solid fa-paper-plane"></i>
                    </button>
                  </form>
                <?php else: ?>
                  <button class="btn btn-outline-secondary btn-sm rounded-3" disabled title="Ajukan hanya untuk Draft">
                    <i class="fa-solid fa-paper-plane"></i>
                  </button>
                <?php endif; ?>

                
                <?php if($hasCert && $statusVal === \App\Models\Certificate::STATUS_APPROVED): ?>
                  <form method="POST" action="<?php echo e(route('admin.certificates.generatePdfOne', $cert->id)); ?>" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-success btn-sm rounded-3" title="Generate PDF Final">
                      <i class="fa-solid fa-file-pdf"></i>
                    </button>
                  </form>
                <?php else: ?>
                  <button class="btn btn-outline-secondary btn-sm rounded-3" disabled title="PDF final hanya setelah Approved">
                    <i class="fa-solid fa-file-pdf"></i>
                  </button>
                <?php endif; ?>

                
                <?php if($hasPdf): ?>
                  <a class="btn btn-outline-success btn-sm rounded-3"
                     href="<?php echo e(route('admin.certificates.view', $cert->id)); ?>"
                     target="_blank"
                     title="Preview PDF">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                <?php else: ?>
                  <button class="btn btn-outline-secondary btn-sm rounded-3" disabled title="PDF belum ada">
                    <i class="fa-solid fa-eye"></i>
                  </button>
                <?php endif; ?>

                
                <?php if($hasPdf): ?>
                  <a class="btn btn-outline-primary btn-sm rounded-3"
                     href="<?php echo e(route('admin.certificates.download', $cert->id)); ?>"
                     title="Download PDF">
                    <i class="fa-solid fa-download"></i>
                  </a>
                <?php else: ?>
                  <button class="btn btn-outline-secondary btn-sm rounded-3" disabled title="PDF belum ada">
                    <i class="fa-solid fa-download"></i>
                  </button>
                <?php endif; ?>
                
                
                <?php if($hasCert && in_array($statusVal, [\App\Models\Certificate::STATUS_FINAL_GENERATED, \App\Models\Certificate::STATUS_SIGNED]) && in_array(auth()->user()->role?->name, ['admin', 'superadmin'])): ?>
                  <form method="POST" action="<?php echo e(route('admin.certificates.revise', $cert->id)); ?>" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin melakukan revisi? Sertifikat akan di-reset ke status Approved dan file PDF lama akan dihapus. Nomor sertifikat TETAP.')">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-danger btn-sm rounded-3" title="Reset / Revisi (Nomor Tetap)">
                      <i class="fa-solid fa-rotate"></i>
                    </button>
                  </form>
                <?php endif; ?>

              </div>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="6" class="text-center text-muted py-4">Belum ada peserta.</td>
          </tr>
        <?php endif; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if($participants && $participants->hasPages()): ?>
    <div class="card-footer bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div class="text-muted small">
        Menampilkan <?php echo e($participants->firstItem()); ?> - <?php echo e($participants->lastItem()); ?>

        dari <?php echo e($participants->total()); ?> data
      </div>
      <div><?php echo e($participants->links()); ?></div>
    </div>
  <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\esertifikatv1\resources\views/certificates/index.blade.php ENDPATH**/ ?>