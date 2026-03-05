
<?php $__env->startSection('title','Tambah Event'); ?>


<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <h4 class="mb-0">Tambah Event</h4>
    <div class="text-muted">Buat event baru dan pilih template sertifikat (opsional).</div>
  </div>

  <a href="<?php echo e(route('admin.system.events.index')); ?>" class="btn btn-outline-secondary">
    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
  </a>
</div>

<?php if($errors->any()): ?>
  <div class="alert alert-danger">
    <div class="fw-semibold mb-1">Periksa kembali input:</div>
    <ul class="mb-0">
      <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li><?php echo e($err); ?></li>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="<?php echo e(route('admin.system.events.store')); ?>" class="card border-0 shadow-sm rounded-4">
  <?php echo csrf_field(); ?>
  <div class="card-body p-4">
    <div class="row g-3">

      <div class="col-md-8">
        <label class="form-label fw-semibold">Nama Event <span class="text-danger">*</span></label>
        <input type="text"
               name="name"
               class="form-control"
               value="<?php echo e(old('name')); ?>"
               placeholder="Contoh: Penguatan Literasi"
               required>
      </div>

      <div class="col-md-4">
        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select" required>
          <option value="active" <?php if(old('status', 'active') === 'active'): echo 'selected'; endif; ?>>Aktif (Active)</option>
          <option value="draft" <?php if(old('status') === 'draft'): echo 'selected'; endif; ?>>Draf (Draft)</option>
          <option value="closed" <?php if(old('status') === 'closed'): echo 'selected'; endif; ?>>Selesai (Closed)</option>
        </select>
        <div class="form-text">Event nonaktif tidak muncul di proses operasional.</div>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
        <input type="date"
               name="start_date"
               class="form-control"
               value="<?php echo e(old('start_date')); ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
        <input type="date"
               name="end_date"
               class="form-control"
               value="<?php echo e(old('end_date')); ?>" required>
      </div>

      <div class="col-12">
        <label class="form-label fw-semibold">Lokasi <span class="text-danger">*</span></label>
        <input type="text"
               name="location"
               class="form-control"
               value="<?php echo e(old('location')); ?>"
               placeholder="Contoh: Perpustakaan BPMP Kaltim" required>
      </div>

      <div class="col-12">
        <label class="form-label fw-semibold">Deskripsi <span class="text-danger">*</span></label>
        <textarea name="description" class="form-control" rows="4"
                  placeholder="Catatan event, keterangan kegiatan, dll." required><?php echo e(old('description')); ?></textarea>
      </div>

      <div class="col-12">
        <label class="form-label fw-semibold">Template Sertifikat (opsional)</label>
        <select name="certificate_template_id" class="form-select">
          <option value="">-- Pilih Template --</option>

          
          <?php if(!empty($templates)): ?>
            <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($t->id); ?>" <?php if((string)old('certificate_template_id') === (string)$t->id): echo 'selected'; endif; ?>>
                <?php echo e($t->name); ?> (<?php echo e($t->code); ?>)
              </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <?php endif; ?>
        </select>
        <div class="form-text">Hanya template aktif yang sebaiknya dipilih.</div>
      </div>

    </div>
  </div>

  <div class="card-footer bg-white border-0 p-4 pt-0 d-flex justify-content-end gap-2">
    <a href="<?php echo e(route('admin.system.events.index')); ?>" class="btn btn-outline-secondary">Batal</a>
    <button class="btn btn-primary">
      <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
    </button>
  </div>
</form>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\esertifikatv1\resources\views/admin/system/events/create.blade.php ENDPATH**/ ?>