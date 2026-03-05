
<?php $__env->startSection('title','Tambah Peserta'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-0">Tambah Peserta</h4>
    <div class="text-muted">Tambahkan peserta ke event.</div>
  </div>
  <a href="<?php echo e(route('admin.participants.index', ['event_id' => request('event_id')])); ?>"
     class="btn btn-outline-secondary rounded-3">
    Kembali
  </a>
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

<form method="POST" action="<?php echo e(route('admin.participants.store')); ?>" class="card border-0 shadow-sm rounded-4">
  <?php echo csrf_field(); ?>
  <div class="card-body">
    <div class="row g-3">

      <div class="col-md-6">
        <label class="form-label">Event <span class="text-danger">*</span></label>
        <select name="event_id" class="form-select" required>
          <option value="">-- Pilih Event --</option>
          <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($ev->id); ?>" <?php if(old('event_id', $eventId) == $ev->id): echo 'selected'; endif; ?>>
              <?php echo e($ev->name); ?>

            </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select" required>
          <option value="draft"  <?php if(old('status','draft')==='draft'): echo 'selected'; endif; ?>>Draft</option>
          <option value="terbit" <?php if(old('status')==='terbit'): echo 'selected'; endif; ?>>Terbit</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Nama <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="<?php echo e(old('name')); ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input name="email" type="email" class="form-control" value="<?php echo e(old('email')); ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">NIK (opsional)</label>
        <input name="nik" class="form-control" value="<?php echo e(old('nik')); ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Instansi <span class="text-danger">*</span></label>
        <input name="institution" class="form-control" value="<?php echo e(old('institution')); ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Kab./Kota/Propinsi <span class="text-danger">*</span></label>
        <select name="daerah" class="form-select" required>
          <option value="">-- Pilih Daerah --</option>
          <?php $__currentLoopData = ['Prov. Kalimantan Timur', 'Kab. Paser', 'Kab. Berau', 'Kab. Kutai Kartanegara', 'Kab. Kutai Barat', 'Kab. Kutai Timur', 'Kab. Penajam Paser Utara', 'Kab. Mahakam Ulu', 'Kota Balikpapan', 'Kota Samarinda', 'Kota Bontang']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($d); ?>" <?php if(old('daerah') === $d): echo 'selected'; endif; ?>><?php echo e($d); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Jenjang <span class="text-danger">*</span></label>
        <select name="jenjang" class="form-select" required>
          <option value="">-- Pilih Jenjang --</option>
          <?php $__currentLoopData = ['PAUD-TK', 'SD', 'SMP', 'SMA', 'SMK', 'PNF', 'Umum']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($j); ?>" <?php if(old('jenjang') === $j): echo 'selected'; endif; ?>><?php echo e($j); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Peran (opsional)</label>
        <input name="peran" class="form-control" value="<?php echo e(old('peran')); ?>">
      </div>

      <div class="col-md-12">
        <label class="form-label">Keterangan (opsional)</label>
        <textarea name="keterangan" class="form-control" rows="2"><?php echo e(old('keterangan')); ?></textarea>
      </div>

      <div class="col-md-12">
        <label class="form-label">Data Tambahan / Metadata (Format JSON, opsional)</label>
        <textarea name="metadata" class="form-control font-monospace" rows="3" placeholder='Contoh: {"nilai_praktek": 95, "jam_pelajaran": 40}'><?php echo e(old('metadata')); ?></textarea>
        <div class="form-text">Gunakan format JSON yang valid. Biarkan kosong jika tidak ada data tambahan.</div>
      </div>

    </div>
  </div>

  <div class="card-footer bg-white d-flex justify-content-end">
    <button class="btn btn-primary rounded-3">
      <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
    </button>
  </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\esertifikatv1\resources\views/participants/create.blade.php ENDPATH**/ ?>