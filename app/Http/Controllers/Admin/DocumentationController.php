<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Parsedown;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentationController extends Controller
{
    public function downloadPdf()
    {
        $filePath = base_path('DOCUMENTATION.md');

        if (!file_exists($filePath)) {
            return back()->with('error', 'File dokumentasi tidak ditemukan.');
        }

        $markdownContent = file_get_contents($filePath);

        // Convert Markdown to HTML
        $parsedown = new Parsedown();
        $htmlContent = $parsedown->text($markdownContent);

        // Prepare context for PDF
        $data = [
            'title' => 'Dokumentasi Aplikasi E-Sertifikat',
            'content' => $htmlContent,
            'date' => date('d F Y')
        ];

        // Generate PDF
        $pdf = Pdf::loadView('admin.system.documentation.pdf', $data);

        return $pdf->download('Dokumentasi_Esertifikat_' . date('Ymd') . '.pdf');
    }
}
