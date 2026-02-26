<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 0; }
    html, body { margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; }

    /* kanvas fixed agar posisi konsisten */
    .page {
      position: fixed;
      left: 0; top: 0;
      width: 1123px;   /* A4 landscape kira-kira @96dpi */
      height: 794px;
      overflow: hidden;
    }

    .field {
      position: absolute;
      white-space: normal;
      word-wrap: break-word;
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

  // ambil config per field (boleh kosong)
  $fNumber = $fields['number'] ?? [];
  $fName   = $fields['name']   ?? [];
  $fEvent  = $fields['event']  ?? [];
  $fDesc   = $fields['desc']   ?? [];
  $fDate   = $fields['date']   ?? [];

  // default posisi (kalau settings belum lengkap tetap tampil)
  $defaults = [
    'number' => ['x'=>0, 'y'=>210, 'w'=>1123, 'font'=>16, 'color'=>'#111111', 'align'=>'center', 'weight'=>'600'],
    'name'   => ['x'=>0, 'y'=>315, 'w'=>1123, 'font'=>48, 'color'=>'#0b5fa8', 'align'=>'center', 'weight'=>'700'],
    'event'  => ['x'=>0, 'y'=>410, 'w'=>1123, 'font'=>20, 'color'=>'#0b5fa8', 'align'=>'center', 'weight'=>'400'],
    'desc'   => ['x'=>120,'y'=>450, 'w'=>880,  'font'=>16, 'color'=>'#111111', 'align'=>'justify','weight'=>'400'],
    'date'   => ['x'=>0, 'y'=>567, 'w'=>1123, 'font'=>16, 'color'=>'#111111', 'align'=>'center', 'weight'=>'500'],
  ];

  // merge: settings menimpa defaults
  $fNumber = array_merge($defaults['number'], is_array($fNumber)?$fNumber:[]);
  $fName   = array_merge($defaults['name'],   is_array($fName)?$fName:[]);
  $fEvent  = array_merge($defaults['event'],  is_array($fEvent)?$fEvent:[]);
  $fDesc   = array_merge($defaults['desc'],   is_array($fDesc)?$fDesc:[]);
  $fDate   = array_merge($defaults['date'],   is_array($fDate)?$fDate:[]);

  // teks
  $numberText = $certificate->certificate_number ? "Nomor: {$certificate->certificate_number}" : "Nomor: -";
  $nameText   = $participant?->name ?? '-';
  $eventText  = $event?->name ?? '-';

  // format Indonesia (Februari, dll)
  $dateText = optional($event?->start_date)->locale('id')->translatedFormat('d F Y'); // 11 Februari 2026
  $dateText = $dateText ? "Samarinda, {$dateText}" : "";

  // deskripsi: ambil dari event->description (Opsional A)
  $descText = trim((string)($event?->description ?? ''));
@endphp

<div class="page">
  {{-- BACKGROUND --}}
  @if($bgAbs && file_exists($bgAbs))
    <img src="{{ $bgAbs }}"
         style="position:absolute; left:0; top:0; width:1123px; height:794px; z-index:-1;">
  @endif

  {{-- 1) NOMOR SERTIFIKAT --}}
  <div class="field"
       style="
         left: {{ (int)$get($fNumber,'x',0) }}px;
         top: {{ (int)$get($fNumber,'y',0) }}px;
         width: {{ (int)$get($fNumber,'w',1123) }}px;
         font-size: {{ (int)$get($fNumber,'font',16) }}px;
         color: {{ $get($fNumber,'color','#111111') }};
         text-align: {{ $get($fNumber,'align','center') }};
         font-weight: {{ $get($fNumber,'weight','600') }};
       ">
    {{ $numberText }}
  </div>

  {{-- 2) NAMA --}}
  <div class="field"
       style="
         left: {{ (int)$get($fName,'x',0) }}px;
         top: {{ (int)$get($fName,'y',0) }}px;
         width: {{ (int)$get($fName,'w',1123) }}px;
         font-size: {{ (int)$get($fName,'font',48) }}px;
         color: {{ $get($fName,'color','#0b5fa8') }};
         text-align: {{ $get($fName,'align','center') }};
         font-weight: {{ $get($fName,'weight','700') }};
       ">
    {{ $nameText }}
  </div>

  {{-- 3) NAMA KEGIATAN --}}
  <div class="field"
       style="
         left: {{ (int)$get($fEvent,'x',0) }}px;
         top: {{ (int)$get($fEvent,'y',0) }}px;
         width: {{ (int)$get($fEvent,'w',1123) }}px;
         font-size: {{ (int)$get($fEvent,'font',20) }}px;
         color: {{ $get($fEvent,'color','#0b5fa8') }};
         text-align: {{ $get($fEvent,'align','center') }};
         font-weight: {{ $get($fEvent,'weight','400') }};
       ">
    {{ $eventText }}
  </div>

  {{-- 4) DESKRIPSI (opsional) --}}
  @if($descText !== '')
    <div class="field"
         style="
           left: {{ (int)$get($fDesc,'x',120) }}px;
           top: {{ (int)$get($fDesc,'y',450) }}px;
           width: {{ (int)$get($fDesc,'w',880) }}px;
           font-size: {{ (int)$get($fDesc,'font',16) }}px;
           color: {{ $get($fDesc,'color','#111111') }};
           text-align: {{ $get($fDesc,'align','justify') }};
           font-weight: {{ $get($fDesc,'weight','400') }};
         ">
      {{ $descText }}
    </div>
  @endif

  {{-- 5) TANGGAL --}}
  @if($dateText !== '')
    <div class="field"
         style="
           left: {{ (int)$get($fDate,'x',0) }}px;
           top: {{ (int)$get($fDate,'y',567) }}px;
           width: {{ (int)$get($fDate,'w',1123) }}px;
           font-size: {{ (int)$get($fDate,'font',16) }}px;
           color: {{ $get($fDate,'color','#111111') }};
           text-align: {{ $get($fDate,'align','center') }};
           font-weight: {{ $get($fDate,'weight','500') }};
         ">
      {{ $dateText }}
    </div>
  @endif
</div>
</body>
</html>