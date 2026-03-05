<?php $__env->startSection('title', 'Profil Pengguna'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="page-title mb-1">Profil Pengguna</h4>
        <div class="page-subtitle">Perbarui informasi profil dan kata sandi Anda di sini.</div>
    </div>
</div>

<div class="row">
    <!-- Profil Info -->
    <div class="col-md-6">
        <div class="card card-soft mb-4">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-1">Informasi Akun</h5>
                <p class="text-muted small mb-4">Perbarui nama lengkap dan alamat email Anda.</p>

                <?php if(session('status') === 'profile-updated'): ?>
                    <div class="alert alert-success alert-dismissible fade show text-sm" role="alert">
                        <strong>Berhasil!</strong> Profil Anda telah diperbarui.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?php echo e(route('admin.profile.update')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('patch'); ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input id="name" name="name" type="text" class="form-control" value="<?php echo e(old('name', $user->name)); ?>" required autofocus autocomplete="name" />
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" class="form-control" value="<?php echo e(old('email', $user->email)); ?>" required autocomplete="username" />
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save me-1"></i> Simpan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Ganti Sandi -->
    <div class="col-md-6">
        <div class="card card-soft mb-4">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-1">Perbarui Kata Sandi</h5>
                <p class="text-muted small mb-4">Pastikan Anda menggunakan kata sandi yang kuat dan aman.</p>

                <?php if(session('status') === 'password-updated'): ?>
                    <div class="alert alert-success alert-dismissible fade show text-sm" role="alert">
                        <strong>Berhasil!</strong> Kata sandi Anda telah diperbarui.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?php echo e(route('password.update')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('put'); ?>

                    <div class="mb-3">
                        <label for="update_password_current_password" class="form-label">Kata Sandi Saat Ini</label>
                        <input id="update_password_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password" />
                        <?php $__errorArgs = ['current_password', 'updatePassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <label for="update_password_password" class="form-label">Kata Sandi Baru</label>
                        <input id="update_password_password" name="password" type="password" class="form-control" autocomplete="new-password" />
                        <?php $__errorArgs = ['password', 'updatePassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-4">
                        <label for="update_password_password_confirmation" class="form-label">Konfirmasi Kata Sandi Baru</label>
                        <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" />
                        <?php $__errorArgs = ['password_confirmation', 'updatePassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="fa-solid fa-key me-1"></i> Simpan Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\esertifikatv1\resources\views/profile/edit.blade.php ENDPATH**/ ?>