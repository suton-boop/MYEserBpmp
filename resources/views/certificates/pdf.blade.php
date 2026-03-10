<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <style>
    @page { size: A4 landscape; margin: 0; }
    html, body { margin: 0; padding: 0; width: 297mm; height: 210mm; }
    body { font-family: DejaVu Sans, sans-serif; -webkit-print-color-adjust: exact; }

    /* Kanvas relative agar posisi setiap field konsisten tapi tak berulang di next page */
    .page {
      position: relative;
      width: 297mm;
      height: 210mm;
      display: block;
      clear: both;
      page-break-after: always;
    }
    .page:last-child {
      page-break-after: avoid;
    }

    .field {
      position: absolute;
      white-space: normal;
      word-wrap: break-word;
      line-height: 1.1; /* Mengurangi ruang kosong atas bawah teks agar tak saling tabrak */
    }

    /* CSS Khusus Halaman 2 (Nilai/Transkrip) */
    .page-2-content {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 10px;
      line-height: 1.1;
      color: #333;
      padding: 10mm 15mm;
      box-sizing: border-box;
      z-index: 10;
    }
    .page-2-content .transcript-header {
      width: 100% !important;
      margin: 0 auto 10px auto;
      border-collapse: collapse;
    }
    .page-2-content .transcript-header td {
      padding: 3px 0;
      vertical-align: top;
      border: none !important;
      font-size: 12px;
    }
    .page-2-content .transcript-header td:first-child { width: 160px; }
    .page-2-content .transcript-header td:nth-child(2) { width: 15px; }
    .page-2-content table.main-table {
      width: 98% !important; /* Diperkecil sedikit agar tidak memicu halaman baru */
      margin-left: 0;
      margin-right: auto;
      border-collapse: collapse !important;
      table-layout: fixed !important; /* Paksa lebar kolom agar tidak meluber */
      border: 1.5px solid #0B3D91 !important;
      margin-top: 5px;
    }
    .page-2-content table.main-table th, 
    .page-2-content table.main-table td {
      border: 1px solid #0B3D91 !important;
      padding: 4px 8px !important;
      vertical-align: middle;
      font-size: 10px !important;
      line-height: 1.1;
      box-sizing: border-box;
      word-wrap: break-word !important; /* Paksa teks turun jika kolom sempit */
    }
    .page-2-content table.main-table th {
      background-color: #FFD700 !important;
      color: #0B3D91 !important;
      font-weight: bold !important;
      text-transform: uppercase;
      text-align: center !important;
    }
    .page-2-content table.main-table .col-no { 
      width: 40px !important; 
      text-align: center; 
    }
    .page-2-content table.main-table .col-nilai { 
      width: 80px !important; 
      text-align: center; 
    }
    .page-2-content table.main-table .col-predikat { 
      width: 100px !important; 
      text-align: center; 
    }
    .page-2-content table.main-table .col-komponen { 
      width: auto !important; 
    }
    
    .page-2-content .text-center { text-align: center !important; }
    .page-2-content .fw-bold { font-weight: bold !important; }
    .page-2-title {
      text-align: left;
      font-size: 16px;
      font-weight: 800;
      margin-top: 0;
      margin-bottom: 5px;
      color: #0B3D91;
      text-transform: uppercase;
      letter-spacing: 1.1px;
    }
    .transcript-footer {
      font-size: 10px;
      color: #555;
      font-style: italic;
      margin-top: 5px;
      width: 100% !important;
    }

    /* Penyeimbang agar tabel rapi */
    .page-2-content table {
      width: 100% !important;
      border-collapse: collapse !important;
      margin-bottom: 10px;
    }
    
    .page-2-content tr.row-header {
       background-color: #FFD700 !important;
    }
    
    .page-2-content td.bg-yellow {
       background-color: #FFD700 !important;
       font-weight: bold;
    }

    .page-break {
      page-break-after: always;
    }
  </style>
</head>
<body>
@php
  /** @var \App\Models\Certificate $certificate */
  $event       = $certificate->event;
  $participant = $certificate->participant;
  $template    = optional($event)->certificateTemplate;

  // settings JSON
  $settings = $template?->settings;
  if (is_string($settings)) $settings = json_decode($settings, true);
  if (!is_array($settings)) $settings = [];

  $fields = $settings['fields'] ?? [];

  // background file (pastikan file_path berisi relative path: certificate-templates/xxx.png)
  $bgRel = $template?->file_path;
  $bgAbs = $bgRel ? public_path('storage/'.$bgRel) : null;

  // helper getter aman
  $get = function($arr, $key, $default = null) {
    return (is_array($arr) && array_key_exists($key, $arr)) ? $arr[$key] : $default;
  };

  // helper Auto-Resize Font untuk menghindari Overlap (Teks Bertumpuk)
  $getFontSize = function($text, $config, $defaultSize) use ($get) {
      $baseSize = (float)$get($config, 'font', $defaultSize);
      $boxWidth = (float)$get($config, 'w', 1123);
      
      $charCount = mb_strlen(trim(strip_tags((string)$text)));
      if ($charCount <= 0) return $baseSize;

      // Estimasi lebar: Karakter rata-rata (font DejaVu Sans).
      // Huruf kapital/lebar biasanya butuh ~0.8 dari size, huruf kecil ~0.6.
      // Kita hitung proporsi huruf besar untuk estimasi yang lebih akurat.
      $upperCount = preg_match_all('/[A-Z0-9]/', $text);
      $upperRatio = $upperCount / $charCount;
      $multiplier = 0.6 + ($upperRatio * 0.25); // Range 0.6 s/d 0.85

      $estimatedWidth = $charCount * ($baseSize * $multiplier);
      
      // Gunakan batas aman (Margin) agar tidak mepet ke pinggir gambar (Safe Area ~92%)
      $safeWidth = $boxWidth * 0.92;
      
      if ($estimatedWidth > $safeWidth) {
          $newSize = $baseSize * ($safeWidth / $estimatedWidth);
          return max($newSize, 10); // Paling mini 10px
      }
      return $baseSize;
  };

  // default posisi (kalau settings belum lengkap tetap tampil)
  $defaults = [
    'number'      => ['x'=>0, 'y'=>210, 'w'=>1123, 'font'=>16, 'color'=>'#111111', 'align'=>'center', 'weight'=>'600'],
    'name'        => ['x'=>0, 'y'=>315, 'w'=>1123, 'font'=>48, 'color'=>'#0b5fa8', 'align'=>'center', 'weight'=>'700'],
    'event'       => ['x'=>0, 'y'=>410, 'w'=>1123, 'font'=>20, 'color'=>'#0b5fa8', 'align'=>'center', 'weight'=>'400'],
    'desc'        => ['x'=>95, 'y'=>460, 'w'=>933,  'font'=>16, 'color'=>'#111111', 'align'=>'justify','weight'=>'400'],
    'date'        => ['x'=>0, 'y'=>567, 'w'=>1123, 'font'=>16, 'color'=>'#111111', 'align'=>'center', 'weight'=>'500'],
    'nik'         => ['x'=>0, 'y'=>345, 'w'=>1123, 'font'=>20, 'color'=>'#0b5fa8', 'align'=>'center', 'weight'=>'600'],
    'peran'       => ['x'=>0, 'y'=>425, 'w'=>1123, 'font'=>20, 'color'=>'#0b5fa8', 'align'=>'center', 'weight'=>'400'],
    'institution' => ['x'=>0, 'y'=>450, 'w'=>1123, 'font'=>20, 'color'=>'#0b5fa8', 'align'=>'center', 'weight'=>'400'],
  ];

  // ambil config per field (boleh kosong)
  $fNumber      = $fields['number']      ?? [];
  $fName        = $fields['name']        ?? [];
  $fEvent       = $fields['event']       ?? [];
  $fDesc        = $fields['desc']        ?? [];
  $fDate        = $fields['date']        ?? [];
  $fNik         = $fields['nik']         ?? [];
  $fPeran       = $fields['peran']       ?? [];
  $fInstitution = $fields['institution'] ?? [];

  // merge: settings menimpa defaults
  $fNumber      = array_merge($defaults['number'],      is_array($fNumber)?$fNumber:[]);
  $fName        = array_merge($defaults['name'],        is_array($fName)?$fName:[]);
  $fEvent       = array_merge($defaults['event'],       is_array($fEvent)?$fEvent:[]);
  $fDesc        = array_merge($defaults['desc'],        is_array($fDesc)?$fDesc:[]);
  $fDate        = array_merge($defaults['date'],        is_array($fDate)?$fDate:[]);
  $fNik         = array_merge($defaults['nik'],         is_array($fNik)?$fNik:[]);
  $fPeran       = array_merge($defaults['peran'],       is_array($fPeran)?$fPeran:[]);
  $fInstitution = array_merge($defaults['institution'], is_array($fInstitution)?$fInstitution:[]);

  // teks
  $numberText      = $certificate->certificate_number ? "Nomor: {$certificate->certificate_number}" : "Nomor: -";
  $nameText        = $participant?->name ?? '-';
  $eventText       = $event?->name ?? '-';
  $nikText         = $participant?->nik ? "NISN: {$participant->nik}" : "";
  $peranText       = $participant?->peran ?? "";
  $institutionText = $participant?->institution ?? "";

  // format Indonesia (Februari, dll)
  // PRIORITAS: 
  // Jika Event diatur "Dinamis", ambil dari custom_date peserta (fallback ke event).
  // Jika Event diatur "Fix", ambil dari start_date event (tutup kemungkinan beda tanggal).
  $rawDate = ($event?->is_date_per_participant) 
             ? ($participant?->custom_date ?? $event?->start_date) 
             : $event?->start_date;

  $dateText = $rawDate ? $rawDate->locale('id')->translatedFormat('d F Y') : "";
  $dateText = $dateText ? "Samarinda, {$dateText}" : "";

  // deskripsi: ambil dari event->description (Opsional A)
  // PRIORITAS (Sama seperti Tanggal): Jika dinamis, ambil dari keterangan peserta (fallback event).
  $descText = ($event?->is_date_per_participant) 
             ? ($participant?->keterangan ?: $event?->description) 
             : $event?->description;

  $descText = trim((string)($descText ?? ''));
@endphp

<div class="page {{ !empty($template->page_2_html) ? 'page-break' : '' }}">
  {{-- BACKGROUND --}}
  @if(!empty($bgDataUri))
    <img src="{{ $bgDataUri }}"
         style="position:absolute; left:0; top:0; width:297mm; height:210mm; z-index:-1;">
  @elseif($bgAbs && file_exists($bgAbs))
    <img src="{{ $bgAbs }}"
         style="position:absolute; left:0; top:0; width:297mm; height:210mm; z-index:-1;">
  @endif

  {{-- 1) NOMOR SERTIFIKAT --}}
  @if(isset($fields['number']))
  <div class="field"
       style="
         left: {{ (int)$get($fNumber,'x') }}px;
         top: {{ (int)$get($fNumber,'y') }}px;
         width: {{ (int)$get($fNumber,'w') }}px;
         font-size: {{ $getFontSize($numberText, $fNumber, 16) }}px;
         color: {{ $get($fNumber,'color') }};
         text-align: {{ $get($fNumber,'align') }};
         font-weight: {{ $get($fNumber,'weight') }};
       ">
    {{ $numberText }}
  </div>
  @endif

  {{-- 2) NAMA --}}
  @if(isset($fields['name']))
  <div class="field"
       style="
         left: {{ (int)$get($fName,'x') }}px;
         top: {{ (int)$get($fName,'y') }}px;
         width: {{ (int)$get($fName,'w') }}px;
         font-size: {{ $getFontSize($nameText, $fName, 48) }}px;
         color: {{ $get($fName,'color') }};
         text-align: {{ $get($fName,'align') }};
         font-weight: {{ $get($fName,'weight') }};
         white-space: nowrap;
       ">
    {{ $nameText }}
  </div>
  @endif

  {{-- 2.1) NIK --}}
  @if(isset($fields['nik']) && $nikText !== '')
    <div class="field"
         style="
           left: {{ (int)$get($fNik,'x') }}px;
           top: {{ (int)$get($fNik,'y') }}px;
           width: {{ (int)$get($fNik,'w') }}px;
           font-size: {{ $getFontSize($nikText, $fNik, 20) }}px;
           color: {{ $get($fNik,'color') }};
           text-align: {{ $get($fNik,'align') }};
           font-weight: {{ $get($fNik,'weight') }};
         ">
      {{ $nikText }}
    </div>
  @endif

  {{-- 3) NAMA KEGIATAN --}}
  @if(isset($fields['event']))
  <div class="field"
       style="
         left: {{ (int)$get($fEvent,'x') }}px;
         top: {{ (int)$get($fEvent,'y') }}px;
         width: {{ (int)$get($fEvent,'w') }}px;
         font-size: {{ $getFontSize($eventText, $fEvent, 20) }}px;
         color: {{ $get($fEvent,'color') }};
         text-align: {{ $get($fEvent,'align') }};
         font-weight: {{ $get($fEvent,'weight') }};
         white-space: nowrap;
       ">
    {{ $eventText }}
  </div>
  @endif

  {{-- 3.1) PERAN --}}
  @if(isset($fields['peran']) && $peranText !== '')
    <div class="field"
         style="
           left: {{ (int)$get($fPeran,'x') }}px;
           top: {{ (int)$get($fPeran,'y') }}px;
           width: {{ (int)$get($fPeran,'w') }}px;
           font-size: {{ $getFontSize($peranText, $fPeran, 20) }}px;
           color: {{ $get($fPeran,'color') }};
           text-align: {{ $get($fPeran,'align') }};
           font-weight: {{ $get($fPeran,'weight') }};
         ">
      {{ $peranText }}
    </div>
  @endif

  {{-- 3.2) INSTITUTION --}}
  @if(isset($fields['institution']) && $institutionText !== '')
    <div class="field"
         style="
           left: {{ (int)$get($fInstitution,'x') }}px;
           top: {{ (int)$get($fInstitution,'y') }}px;
           width: {{ (int)$get($fInstitution,'w') }}px;
           font-size: {{ $getFontSize($institutionText, $fInstitution, 20) }}px;
           color: {{ $get($fInstitution,'color') }};
           text-align: {{ $get($fInstitution,'align') }};
           font-weight: {{ $get($fInstitution,'weight') }};
         ">
      {{ $institutionText }}
    </div>
  @endif

  {{-- 4) DESKRIPSI (opsional) --}}
  @if(isset($fields['desc']) && $descText !== '')
    <div class="field"
         style="
           left: {{ (int)$get($fDesc,'x') }}px;
           top: {{ (int)$get($fDesc,'y') }}px;
           width: {{ (int)$get($fDesc,'w') }}px;
           font-size: {{ $get($fDesc,'font', 16) }}px;
           color: {{ $get($fDesc,'color') }};
           text-align: {{ $get($fDesc,'align') }};
           font-weight: {{ $get($fDesc,'weight') }};
           line-height: 1.5;
         ">
      {{ $descText }}
    </div>
  @endif

  {{-- 5) TANGGAL --}}
  @if(isset($fields['date']) && $dateText !== '')
    <div class="field"
         style="
           left: {{ (int)$get($fDate,'x') }}px;
           top: {{ (int)$get($fDate,'y') }}px;
           width: {{ (int)$get($fDate,'w') }}px;
           font-size: {{ $getFontSize($dateText, $fDate, 16) }}px;
           color: {{ $get($fDate,'color') }};
           text-align: {{ $get($fDate,'align') }};
           font-weight: {{ $get($fDate,'weight') }};
         ">
      {{ $dateText }}
    </div>
  @endif
</div>

@if(!empty($template->page_2_html))
  @php
      $page2Content = $template->page_2_html;
      $metadata = is_array($participant->metadata) ? $participant->metadata : [];
      foreach ($metadata as $key => $val) {
          $page2Content = str_replace(['{{ '.$key.' }}', '{{'.$key.'}}', '{'.$key.'}'], $val ?? '', $page2Content);
      }
      $page2Content = str_replace([
          '{{ name }}', '{{ nik }}', '{{ institution }}', '{{ daerah }}', '{{ jenjang }}', '{{ peran }}', '{{ keterangan }}',
          'width:1000px', 'width: 1000px', 'width="1000"', 'width="180"', 'margin-left:auto; margin-right:auto;'
      ], [
          $nameText, $participant->nik ?? '-', $participant->institution ?? '-', $participant->daerah ?? '-', 
          $participant->jenjang ?? '-', $participant->peran ?? '-', $participant->keterangan ?? '-',
          'width:100%', 'width:100%', 'width="100%"', 'width="18%"', 'margin-left:auto; margin-right:auto;'
      ], $page2Content);
  @endphp
  <div class="page" style="position: relative;">
    {{-- BACKGROUND PAGE 2 --}}
    @if(!empty($bgDataUri2))
      <img src="{{ $bgDataUri2 }}" style="position:absolute; left:0; top:0; width:297mm; height:210mm; z-index:-1;">
    @endif
    <div class="page-2-content">
        <div class="transcript-wrapper">
            {!! $page2Content !!}
        </div>
    </div>
  </div>
@endif

</body>
</html>