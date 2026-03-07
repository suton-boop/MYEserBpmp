

<?php $__env->startSection('title', 'Hasil Verifikasi - BPMP Kaltim'); ?>
<?php $__env->startSection('breadcrumb', 'Hasil Verifikasi'); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center" data-aos="fade-up">
    <div class="col-lg-10 mb-5">
        
        <?php if($isValid && $cert): ?>
        <!-- Success State -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
            <div class="card-header bg-success bg-opacity-10 border-0 py-4 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-check fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-success">Sertifikat Valid</h5>
                        <p class="mb-0 text-success opacity-75 small">Terdaftar secara resmi di database BPMP Provinsi Kaltim</p>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <div class="row g-5">
                    <div class="col-md-7">
                        <div class="mb-5">
                            <label class="text-uppercase text-muted fw-bold small mb-2 tracking-wider" style="letter-spacing: 1px;">Nomor Sertifikat</label>
                            <h4 class="fw-bold text-dark font-monospace"><?php echo e($cert->certificate_number); ?></h4>
                        </div>
                        
                        <div class="row g-4 mb-5">
                            <div class="col-12">
                                <label class="text-uppercase text-muted fw-bold small mb-2 tracking-wider" style="letter-spacing: 1px;">Nama Peserta</label>
                                <h3 class="fw-800 text-dark mb-1" style="font-weight: 800;"><?php echo e($cert->participant->name); ?></h3>
                                <div class="text-primary fw-semibold"><?php echo e($cert->participant->institution ?? '-'); ?></div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="text-uppercase text-muted fw-bold small mb-2 tracking-wider" style="letter-spacing: 1px;">Informasi Kegiatan</label>
                            <h5 class="fw-bold text-dark mb-3 lh-base"><?php echo e($cert->event->name); ?></h5>
                            
                            <div class="d-flex flex-wrap gap-4 mt-3">
                                <div>
                                    <div class="text-muted small mb-1">Tanggal Kegiatan</div>
                                    <div class="fw-semibold text-dark">
                                        <i class="fa-regular fa-calendar-check me-1 text-primary"></i>
                                        <?php
                                            $displayDate = ($cert->event->is_date_per_participant)
                                                ? ($cert->participant->custom_date ?? $cert->event->start_date)
                                                : $cert->event->start_date;
                                        ?>
                                        <?php echo e($displayDate ? \Carbon\Carbon::parse($displayDate)->translatedFormat('d M Y') : '-'); ?>

                                    </div>
                                </div>
                                <div>
                                    <div class="text-muted small mb-1">Status Dokumen</div>
                                    <div class="fw-semibold text-success">
                                        <i class="fa-solid fa-circle-check me-1"></i> Terbit
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                        <div class="h-100 bg-light rounded-4 p-4 d-flex flex-column justify-content-between border border-light shadow-sm">
                            <div>
                                <h6 class="fw-bold text-dark mb-3">Tanda Tangan Elektronik</h6>
                                <div class="d-flex gap-3 mb-4">
                                    <div class="bg-white p-3 rounded-3 shadow-sm text-center" style="width: 70px; height: 70px;">
                                        <i class="fa-solid fa-file-signature text-primary fs-3"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">TTE Valid & Terverifikasi</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Sesuai dengan regulasi TTE di lingkungan Kemendikbudristek</div>
                                    </div>
                                </div>
                                <p class="small text-muted mb-4">
                                    Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE), BSSN.
                                </p>
                            </div>
                            
                            <div class="mt-auto">
                                <a href="<?php echo e(route('public.download', $cert->verify_token)); ?>" target="_blank" class="btn btn-primary btn-lg w-100 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 py-3 shadow-sm ripple">
                                    <i class="fa-solid fa-cloud-arrow-down"></i> UNDUH SERTIFIKAT
                                </a>
                                <p class="text-center mt-3 mb-0">
                                    <a href="<?php echo e(route('public.verify.form')); ?>" class="text-decoration-none small text-muted hover-primary">
                                        <i class="fa-solid fa-chevron-left me-1"></i> Verifikasi Nomor Lain
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Error State -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4" data-aos="zoom-in">
            <div class="card-body p-5 text-center">
                <div class="mb-4">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="fa-solid fa-circle-xmark" style="font-size: 3.5rem;"></i>
                    </div>
                    <h2 class="fw-bold text-dark mb-2">Verifikasi Gagal</h2>
                    <p class="text-muted fs-5">Maaf, data sertifikat tersebut tidak dapat ditemukan dalam database kami.</p>
                </div>
                
                <div class="bg-light rounded-4 p-4 mb-5 mx-auto text-start border border-danger border-opacity-10" style="max-width: 600px;">
                    <h6 class="fw-bold text-danger mb-2">Kemungkinan Penyebab:</h6>
                    <ul class="text-muted small mb-0">
                        <li class="mb-2">Nomor sertifikat yang Anda masukkan salah atau typo.</li>
                        <li class="mb-2">Sertifikat belum diterbitkan atau masih dalam proses TTE.</li>
                        <li class="mb-2">Data tersebut bukan merupakan sertifikat resmi yang diterbitkan oleh BPMP Kaltim.</li>
                    </ul>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?php echo e(route('public.verify.form')); ?>" class="btn btn-primary btn-lg px-5 fw-bold rounded-3 shadow-sm">
                        <i class="fa-solid fa-rotate-left me-2"></i> Coba Lagi
                    </a>
                    <a href="<?php echo e(route('public.home')); ?>" class="btn btn-outline-dark btn-lg px-5 fw-bold rounded-3">
                        Halaman Utama
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<style>
    .fw-800 { font-weight: 800; }
    .tracking-wider { letter-spacing: 0.1em; }
    .ripple {
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .ripple:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(13, 110, 253, 0.2) !important;
    }
    .hover-primary:hover {
        color: var(--primary-color) !important;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\esertifikatv1\resources\views/public/verify-show.blade.php ENDPATH**/ ?>