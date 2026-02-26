<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tte\CertificateController;
use App\Http\Controllers\Tte\ApprovalController;
use App\Http\Controllers\Tte\SigningController;

Route::middleware(['auth:sanctum'])->prefix('tte')->group(function () {
    Route::post('/certificates', [CertificateController::class, 'store']);
    Route::post('/certificates/{id}/pdf', [CertificateController::class, 'uploadPdf']);

    Route::post('/certificates/{id}/review', [ApprovalController::class, 'review']);
    Route::post('/certificates/{id}/approve', [ApprovalController::class, 'approve']);
    Route::post('/certificates/{id}/reject', [ApprovalController::class, 'reject']);

    Route::post('/certificates/{id}/sign', [SigningController::class, 'signNow']);
});