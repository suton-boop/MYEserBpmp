
<?php $__env->startSection('title','Edit User'); ?>
<?php $isSuper = $user->role?->name === 'superadmin'; ?>
...
<select name="role_id" class="form-select" required <?php if($isSuper): echo 'disabled'; endif; ?>>
<?php if($isSuper): ?>
  <input type="hidden" name="role_id" value="<?php echo e($user->role_id); ?>">
  <div class="text-muted small mt-1">Role superadmin dikunci.</div>
<?php endif; ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-0">Edit User</h4>
    <div class="text-muted">Ubah data user dan role.</div>
  </div>
  <a href="<?php echo e(route('admin.system.users.index')); ?>" class="btn btn-outline-secondary rounded-3">Kembali</a>
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

<form method="POST" action="<?php echo e(route('admin.system.users.update', $user->id)); ?>" class="card border-0 shadow-sm rounded-4">
  <?php echo csrf_field(); ?>
  <?php echo method_field('PATCH'); ?>

  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Nama</label>
        <input name="name" class="form-control" value="<?php echo e(old('name', $user->name)); ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" value="<?php echo e(old('email', $user->email)); ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Role</label>
        <select name="role_id" class="form-select" required>
          <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($r->id); ?>" <?php if(old('role_id', $user->role_id)==$r->id): echo 'selected'; endif; ?>>
              <?php echo e($r->name); ?>

            </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      <div class="col-md-6"></div>

      <div class="col-md-6">
        <label class="form-label">Password Baru (opsional)</label>
        <input name="password" type="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
      </div>

      <div class="col-md-6">
        <label class="form-label">Konfirmasi Password Baru</label>
        <input name="password_confirmation" type="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
      </div>
    </div>

    <div class="text-muted small mt-2">
      Jika password dikosongkan, password tidak berubah.
    </div>
  </div>

  <div class="card-footer bg-white d-flex justify-content-end">
    <button class="btn btn-primary rounded-3">Simpan Perubahan</button>
  </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\esertifikatv1\resources\views/admin/system/users/edit.blade.php ENDPATH**/ ?>