<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        // Statistik Antrean
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();

        // Membaca draf vs yang gagal di sertifikat
        $totalCertificatesGenerations = DB::table('certificates')
            ->whereNotNull('pdf_path')
            ->count();

        $totalTteSigned = DB::table('certificates')
            ->whereNotNull('signed_pdf_path')
            ->count();

        // Antrian TTE (Sudah ada PDF, tapi belum di TTE)
        $pendingTte = DB::table('certificates')
            ->whereNotNull('pdf_path')
            ->whereNull('signed_pdf_path')
            ->count();

        // Info nomor surat terakhir yang sudah digunakan
        $lastCertificate = DB::table('certificates')
            ->whereNotNull('certificate_number')
            ->orderBy('year', 'desc')
            ->orderBy('sequence', 'desc')
            ->first(['certificate_number']);

        $lastNumber = $lastCertificate ? $lastCertificate->certificate_number : '-';

        // Ambil data queue/jobs terbaru
        $recentJobs = DB::table('jobs')
            ->orderBy('id', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($job) {
            return (object)[
            'id' => $job->id,
            'queue' => $job->queue,
            'payload' => json_decode($job->payload),
            'attempts' => $job->attempts,
            'created_at' => Carbon::createFromTimestamp($job->created_at)->format('Y-m-d H:i:s')
            ];
        });

        // Ambil data failed jobs terbaru
        $recentFailedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.monitoring.index', compact(
            'pendingJobs',
            'failedJobs',
            'totalCertificatesGenerations',
            'totalTteSigned',
            'recentJobs',
            'recentFailedJobs',
            'lastNumber',
            'pendingTte'
        ));
    }

    public function retryAllFailed()
    {
        \Illuminate\Support\Facades\Artisan::call('queue:retry all');
        return back()->with('success', 'Semua antrean gagal telah dijadwalkan ulang.');
    }

    public function clearAllFailed()
    {
        \Illuminate\Support\Facades\Artisan::call('queue:forget all');
        return back()->with('success', 'Semua riwayat kegagalan (failed jobs) telah dihapus dari sistem.');
    }
}
