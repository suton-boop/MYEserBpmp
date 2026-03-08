
<?php $__env->startSection('title','Audit Trail'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1">Audit Trail</h4>
    <div class="text-muted small">Riwayat aktivitas sistem dan jejak audit keamanan.</div>
  </div>
</div>


<form method="GET" action="<?php echo e(route('admin.audit.index')); ?>" class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3">
        <div class="row g-3">
            <div class="col-lg-4">
                <label class="form-label small text-muted mb-1 fw-semibold">Tipe Event</label>
                <select name="event_type" class="form-select rounded-3">
                    <option value="">-- Semua Event --</option>
                    <option value="certificate.reviewed" <?php if(request('event_type') == 'certificate.reviewed'): echo 'selected'; endif; ?>>Certificate Reviewed</option>
                    <option value="certificate.approved" <?php if(request('event_type') == 'certificate.approved'): echo 'selected'; endif; ?>>Certificate Approved</option>
                    <option value="certificate.rejected" <?php if(request('event_type') == 'certificate.rejected'): echo 'selected'; endif; ?>>Certificate Rejected</option>
                    <option value="certificate.signed" <?php if(request('event_type') == 'certificate.signed'): echo 'selected'; endif; ?>>Certificate Signed (TTE)</option>
                </select>
            </div>
            
            <div class="col-lg-2 d-flex align-items-end gap-2">
                <button class="btn btn-primary h-100 flex-grow-1 rounded-3">
                    <i class="fa-solid fa-filter me-1 small"></i> Filter
                </button>
                <a class="btn btn-outline-secondary h-100 px-3 rounded-3 d-flex align-items-center" href="<?php echo e(route('admin.audit.index')); ?>">
                  <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
        <div class="fw-bold"><i class="fa-solid fa-list-check me-2 text-primary"></i>Log Aktivitas</div>
        <div class="text-muted small">Total: <?php echo e($logs->total()); ?> record</div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th width="180px">Waktu</th>
                    <th>Aktor / User</th>
                    <th>Event</th>
                    <th>Subject ID</th>
                    <th>IP Address</th>
                    <th class="text-end">Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="small text-muted">
                            <div><?php echo e($log->created_at->format('d M Y')); ?></div>
                            <div class="fw-bold text-dark"><?php echo e($log->created_at->format('H:i:s')); ?></div>
                        </td>
                        <td>
                            <?php if($log->actor): ?>
                                <div class="fw-bold"><?php echo e($log->actor->name); ?></div>
                                <div class="text-muted small"><?php echo e($log->actor->email); ?></div>
                            <?php else: ?>
                                <span class="text-muted small">System / Guest</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                                $badgeClass = match($log->event_type) {
                                    'certificate.approved' => 'bg-success bg-opacity-10 text-success border-success',
                                    'certificate.rejected' => 'bg-danger bg-opacity-10 text-danger border-danger',
                                    'certificate.signed'   => 'bg-info bg-opacity-10 text-info border-info border-opacity-25',
                                    'certificate.reviewed' => 'bg-primary bg-opacity-10 text-primary border-primary',
                                    default => 'bg-secondary bg-opacity-10 text-muted border-secondary'
                                };
                            ?>
                            <span class="badge border <?php echo e($badgeClass); ?> rounded-pill px-2 py-1 fw-medium" style="font-size: 0.7rem;">
                                <?php echo e(strtoupper(str_replace('.', ' ', $log->event_type))); ?>

                            </span>
                        </td>
                        <td class="small text-muted font-monospace" style="font-size: 0.75rem;">
                            <?php echo e($log->subject_id); ?>

                        </td>
                        <td class="small">
                            <code><?php echo e($log->actor_ip ?? '127.0.0.1'); ?></code>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary border-0 rounded-circle" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modal-<?php echo e($log->id); ?>"
                                    style="width: 32px; height: 32px;">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            
                            
                            <div class="modal fade text-start" id="modal-<?php echo e($log->id); ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow rounded-4">
                                        <div class="modal-header border-0">
                                            <h6 class="modal-title fw-bold">Detail Log</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body bg-light rounded-bottom-4">
                                            <div class="mb-3">
                                                <label class="small text-muted fw-bold d-block mb-1">HASH VERIFICATION</label>
                                                <div class="p-2 bg-white border rounded small text-break font-monospace">
                                                    <?php echo e($log->hash); ?>

                                                </div>
                                            </div>
                                            <div>
                                                <label class="small text-muted fw-bold d-block mb-1">METADATA</label>
                                                <pre class="bg-dark text-success p-3 rounded small mb-0" style="max-height: 200px; overflow: auto;"><?php echo e(json_encode($log->metadata, JSON_PRETTY_PRINT)); ?></pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted"><i class="fa-solid fa-database mb-2 fs-2"></i></div>
                            <div class="fw-bold text-muted">Belum ada riwayat aktivitas.</div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white border-0 py-3">
        <?php echo e($logs->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\esertifikatv1\resources\views/admin/audit/index.blade.php ENDPATH**/ ?>