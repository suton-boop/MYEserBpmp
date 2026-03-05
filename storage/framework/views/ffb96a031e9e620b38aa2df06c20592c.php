
<?php $__env->startSection('title','Edit Event'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-0">Edit Event</h4>
    <div class="text-muted">Ubah data event.</div>
  </div>
  <a href="<?php echo e(route('admin.system.events.index')); ?>" class="btn btn-outline-secondary rounded-3">Kembali</a>
</div>

<?php if($errors->any()): ?>
  <div class="alert alert-danger">
    <div class="fw-semibold mb-1">Periksa input:</div>
    <ul class="mb-0">
      <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li><?php echo e($error); ?></li>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="<?php echo e(route('admin.system.events.update', $event->id)); ?>" class="card border-0 shadow-sm rounded-4">
  <?php echo csrf_field(); ?>
  <?php echo method_field('PATCH'); ?>

  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Nama Event <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="<?php echo e(old('name', $event->name)); ?>" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <?php $st = old('status', $event->status); ?>
        <select name="status" class="form-select" required>
          <option value="draft" <?php if($st==='draft'): echo 'selected'; endif; ?>>draft</option>
          <option value="active" <?php if($st==='active'): echo 'selected'; endif; ?>>active</option>
          <option value="closed" <?php if($st==='closed'): echo 'selected'; endif; ?>>closed</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
        <input type="date" name="start_date" class="form-control"
               value="<?php echo e(old('start_date', optional($event->start_date)->format('Y-m-d'))); ?>" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">Tanggal Selesai (opsional)</label>
        <input type="date" name="end_date" class="form-control"
               value="<?php echo e(old('end_date', optional($event->end_date)->format('Y-m-d'))); ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Lokasi (opsional)</label>
        <input name="location" class="form-control" value="<?php echo e(old('location', $event->location)); ?>">
      </div>

      <div class="col-12">
        <label class="form-label">Deskripsi (opsional)</label>
        <textarea name="description" rows="4" class="form-control"><?php echo e(old('description', $event->description)); ?></textarea>
      </div>
    </div>
  </div>

 
  <div class="mb-3">
  
  <div class="form-text">Template ini dipakai saat generate sertifikat untuk event ini.</div>
</div>
<div class="mb-3">
  <label class="form-label">Pilih Template Sertifikat</label>
  <select name="certificate_template_id" class="form-select <?php $__errorArgs = ['certificate_template_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
    <option value="">-- Default / Belum ditentukan --</option>
    <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <option value="<?php echo e($t->id); ?>"
        <?php if((string)old('certificate_template_id', $event->certificate_template_id) === (string)$t->id): echo 'selected'; endif; ?>>
        <?php echo e($t->name); ?> <?php if(!$t->is_active): ?> (Nonaktif) <?php endif; ?>
      </option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
  <?php $__errorArgs = ['certificate_template_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
 <div class="card-footer bg-white d-flex justify-content-end gap-2">
    <button class="btn btn-primary rounded-3">
      <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
    </button>
  </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\esertifikatv1\resources\views/admin/system/events/edit.blade.php ENDPATH**/ ?>