<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 0; }
    html, body { margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; }

    /* Kanvas relative agar posisi setiap field konsisten tapi tak berulang di next page */
    .page {
      position: relative;
      width: 1122px;   /* A4 landscape kira-kira @96dpi (1122.5px x 793.7px) */
      height: 793px;
      overflow: hidden;
      display: block;
      page-break-inside: avoid;
    }

    .field {
      position: absolute;
      white-space: normal;
      word-wrap: break-word;
      line-height: 1.1; /* Mengurangi ruang kosong atas bawah teks agar tak saling tabrak */
    }

    /* CSS Khusus Halaman 2 (Nilai/Transkrip) */
    .page-2-content {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 13px;
      line-height: 1.25;
      color: #333;
    }
    .page-2-content .transcript-header {
      width: 100%;
      margin-bottom: 20px;
      border-collapse: collapse;
    }
    .page-2-content .transcript-header td {
      padding: 3px 0;
      vertical-align: top;
      border: none !important;
    }
    .page-2-content table.main-table {
      width: 950px !important; /* Paksa lebar total agar tidak meluber dari A4 Landscape */
      margin-left: auto;
      margin-right: auto;
      border-collapse: collapse;
      table-layout: fixed !important; 
      border: 1px solid #000 !important;
    }
    .page-2-content table.main-table th {
      background-color: #996515 !important; /* Warna Emas/Coklat Premium */
      color: #ffffff !important;
      font-weight: bold !important;
      text-align: center !important;
      text-transform: uppercase;
      letter-spacing: 1px;
      padding: 10px 5px !important; /* Padding lebih tebal agar cantik */
      border: 1px solid #7d5311 !important; /* Border warna senada */
      font-size: 11px !important;
    }
    .page-2-content table.main-table th, 
    .page-2-content table.main-table td {
      border: 1px solid #000 !important;
      padding: 6px 5px !important;
      vertical-align: middle;
      font-size: 11px !important;
      line-height: 1.2;
      word-wrap: break-word;
    }
    /* Kunci Lebar Kolom secara Absolut */
    .page-2-content table.main-table th.col-no,
    .page-2-content table.main-table td.col-no {
      width: 30px !important; 
      text-align: center;
    }
    .page-2-content table.main-table th.col-nilai,
    .page-2-content table.main-table td.col-nilai {
      width: 70px !important;
      text-align: center;
    }
    .page-2-content table.main-table th.col-predikat,
    .page-2-content table.main-table td.col-predikat {
      width: 80px !important;
      text-align: center;
    }
    /* Kolom Komponen mengambil sisanya secara otomatis */
    .page-2-content table.main-table .col-komponen {
      width: auto !important;
    }

    .page-2-content .text-center { text-align: center !important; }
    .page-2-content .fw-bold { font-weight: bold !important; }
    
    .page-2-title {
      text-align: center;
      font-size: 21px;
      font-weight: 800;
      margin-bottom: 25px;
      color: #000;
      letter-spacing: 1px;
    }

    .transcript-footer {
      margin-top: 10px;
      font-size: 11px;
      line-height: 1.4;
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
      $width = (float)$get($config, 'w', 1123);
      
      // Estimasi lebar dalam pixel (Karakter font berasumsi mengambil ~65% dari ukurannya agar lebih aman bagi teks kapital tebal)
      $estimatedWidth = mb_strlen(trim(strip_tags((string)$text))) * ($baseSize * 0.65);
      
      // Jika diprediksi kepanjangan melebihi batas kotak 95%, kita paksa perkecil Font-nya
      if ($estimatedWidth > ($width * 0.95) && $estimatedWidth > 0) {
          $newSize = $baseSize * (($width * 0.95) / $estimatedWidth);
          return max($newSize, 12); // Paling mini jangan kurang dari 12px
      }
      return $baseSize;
  };

  // default posisi (kalau settings belum lengkap tetap tampil)
  $defaults = [
    'number'      => ['x'=>0, 'y'=>210, 'w'=>1123, 'font'=>16, 'color'=>'#111111', 'align'=>'center', 'weight'=>'600'],
    'name'        => ['x'=>0, 'y'=>315, 'w'=>1123, 'font'=>48, 'color'=>'#0b5fa8', 'align'=>'center', 'weight'=>'700'],
    'event'       => ['x'=>0, 'y'=>410, 'w'=>1123, 'font'=>20, 'color'=>'#0b5fa8', 'align'=>'center', 'weight'=>'400'],
    'desc'        => ['x'=>120,'y'=>450, 'w'=>880,  'font'=>16, 'color'=>'#111111', 'align'=>'justify','weight'=>'400'],
    'date'        => ['x'=>0, 'y'=>567, 'w'=>1123, 'font'=>16, 'color'=>'#111111', 'align'=>'center', 'weight'=>'500'],
    'nik'         => ['x'=>0, 'y'=>345, 'w'=>1123, 'font'=>20, 'color'=>'#0b5fa8', 'align'=>'center', 'weight'=>'600'],
    'peran'       => ['x'=>0, 'y'=>410, 'w'=>1123, 'font'=>20, 'color'=>'#0b5fa8', 'align'=>'center', 'weight'=>'400'],
    'institution' => ['x'=>0, 'y'=>440, 'w'=>1123, 'font'=>20, 'color'=>'#0b5fa8', 'align'=>'center', 'weight'=>'400'],
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
  $descText = trim((string)($event?->description ?? ''));
@endphp

<div class="page">
  {{-- BACKGROUND --}}
  @if($bgAbs && file_exists($bgAbs))
    <img src="{{ $bgAbs }}"
         style="position:absolute; left:0; top:0; width:1122px; height:793px; z-index:-1;">
  @endif

  {{-- 1) NOMOR SERTIFIKAT --}}
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

  {{-- 2) NAMA --}}
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

  {{-- 2.1) NIK --}}
  @if($nikText !== '')
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

  {{-- 3.1) PERAN --}}
  @if($peranText !== '')
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
  @if($institutionText !== '')
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
  @if($descText !== '')
    <div class="field"
         style="
           left: {{ (int)$get($fDesc,'x') }}px;
           top: {{ (int)$get($fDesc,'y') }}px;
           width: {{ (int)$get($fDesc,'w') }}px;
           font-size: {{ $getFontSize($descText, $fDesc, 16) }}px;
           color: {{ $get($fDesc,'color') }};
           text-align: {{ $get($fDesc,'align') }};
           font-weight: {{ $get($fDesc,'weight') }};
         ">
      {{ $descText }}
    </div>
  @endif

  {{-- 5) TANGGAL --}}
  @if($dateText !== '')
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
      // Eksekusi replace {{ key }} dengan database kolom atau metadara
      $page2Content = $template->page_2_html;
      $metadata = is_array($participant->metadata) ? $participant->metadata : [];
      
      // Mengubah string statis {{ nama_kolom }} menjadi data aslinya
      foreach ($metadata as $key => $val) {
          $page2Content = str_replace('{{ ' . $key . ' }}', $val ?? '', $page2Content);
          $page2Content = str_replace('{{' . $key . '}}', $val ?? '', $page2Content); // tanpa spasi
          
          // support old style {key}
          $page2Content = str_replace('{' . $key . '}', $val ?? '', $page2Content);
      }
      
      // Fallback untuk var default
      $page2Content = str_replace('{{ name }}', $nameText, $page2Content);
      $page2Content = str_replace('{{ nik }}', $participant->nik ?? '-', $page2Content);
      $page2Content = str_replace('{{ institution }}', $participant->institution ?? '-', $page2Content);
      $page2Content = str_replace('{{ daerah }}', $participant->daerah ?? '-', $page2Content);
      $page2Content = str_replace('{{ jenjang }}', $participant->jenjang ?? '-', $page2Content);
      $page2Content = str_replace('{{ peran }}', $participant->peran ?? '-', $page2Content);
      $page2Content = str_replace('{{ keterangan }}', $participant->keterangan ?? '-', $page2Content);
  @endphp

  <div class="page" style="page-break-before: always; position: relative;">
    {{-- BACKGROUND PAGE 2 --}}
    @if(!empty($bgDataUri2))
      <img src="{{ $bgDataUri2 }}" style="position:absolute; left:0; top:0; width:1122px; height:793px; z-index:-1;">
    @endif
    
    <div class="page-2-content" style="position: absolute; left: 0; top: 0; width: 100%; height: 100%; padding: 40px 60px; box-sizing: border-box; z-index: 10;">
        <div class="transcript-wrapper">
            {!! $page2Content !!}
        </div>
    </div>
  </div>
@endif

</body>
</html>