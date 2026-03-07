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
  </style>
</head>
<body>
<?php
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
?>

<div class="page">
  
  <?php if($bgAbs && file_exists($bgAbs)): ?>
    <img src="<?php echo e($bgAbs); ?>"
         style="position:absolute; left:0; top:0; width:1122px; height:793px; z-index:-1;">
  <?php endif; ?>

  
  <div class="field"
       style="
         left: <?php echo e((int)$get($fNumber,'x')); ?>px;
         top: <?php echo e((int)$get($fNumber,'y')); ?>px;
         width: <?php echo e((int)$get($fNumber,'w')); ?>px;
         font-size: <?php echo e($getFontSize($numberText, $fNumber, 16)); ?>px;
         color: <?php echo e($get($fNumber,'color')); ?>;
         text-align: <?php echo e($get($fNumber,'align')); ?>;
         font-weight: <?php echo e($get($fNumber,'weight')); ?>;
       ">
    <?php echo e($numberText); ?>

  </div>

  
  <div class="field"
       style="
         left: <?php echo e((int)$get($fName,'x')); ?>px;
         top: <?php echo e((int)$get($fName,'y')); ?>px;
         width: <?php echo e((int)$get($fName,'w')); ?>px;
         font-size: <?php echo e($getFontSize($nameText, $fName, 48)); ?>px;
         color: <?php echo e($get($fName,'color')); ?>;
         text-align: <?php echo e($get($fName,'align')); ?>;
         font-weight: <?php echo e($get($fName,'weight')); ?>;
         white-space: nowrap;
       ">
    <?php echo e($nameText); ?>

  </div>

  
  <div class="field"
       style="
         left: <?php echo e((int)$get($fEvent,'x')); ?>px;
         top: <?php echo e((int)$get($fEvent,'y')); ?>px;
         width: <?php echo e((int)$get($fEvent,'w')); ?>px;
         font-size: <?php echo e($getFontSize($eventText, $fEvent, 20)); ?>px;
         color: <?php echo e($get($fEvent,'color')); ?>;
         text-align: <?php echo e($get($fEvent,'align')); ?>;
         font-weight: <?php echo e($get($fEvent,'weight')); ?>;
         white-space: nowrap;
       ">
    <?php echo e($eventText); ?>

  </div>

  
  <?php if($descText !== ''): ?>
    <div class="field"
         style="
           left: <?php echo e((int)$get($fDesc,'x')); ?>px;
           top: <?php echo e((int)$get($fDesc,'y')); ?>px;
           width: <?php echo e((int)$get($fDesc,'w')); ?>px;
           font-size: <?php echo e($getFontSize($descText, $fDesc, 16)); ?>px;
           color: <?php echo e($get($fDesc,'color')); ?>;
           text-align: <?php echo e($get($fDesc,'align')); ?>;
           font-weight: <?php echo e($get($fDesc,'weight')); ?>;
         ">
      <?php echo e($descText); ?>

    </div>
  <?php endif; ?>

  
  <?php if($dateText !== ''): ?>
    <div class="field"
         style="
           left: <?php echo e((int)$get($fDate,'x')); ?>px;
           top: <?php echo e((int)$get($fDate,'y')); ?>px;
           width: <?php echo e((int)$get($fDate,'w')); ?>px;
           font-size: <?php echo e($getFontSize($dateText, $fDate, 16)); ?>px;
           color: <?php echo e($get($fDate,'color')); ?>;
           text-align: <?php echo e($get($fDate,'align')); ?>;
           font-weight: <?php echo e($get($fDate,'weight')); ?>;
         ">
      <?php echo e($dateText); ?>

    </div>
  <?php endif; ?>
</div>

<?php if(!empty($template->page_2_html)): ?>
  <?php
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
  ?>

  <div class="page" style="page-break-before: always; position: relative;">
    
    <?php if(!empty($bgDataUri2)): ?>
      <img src="<?php echo e($bgDataUri2); ?>" style="position:absolute; left:0; top:0; width:1122px; height:793px; z-index:-1;">
    <?php endif; ?>
    
    <div style="position: absolute; left: 0; top: 0; width: 100%; height: 100%; padding: 50px; box-sizing: border-box; z-index: 10;">
        <?php echo $page2Content; ?>

    </div>
  </div>
<?php endif; ?>

</body>
</html><?php /**PATH C:\laragon\www\esertifikatv1\resources\views/certificates/pdf.blade.php ENDPATH**/ ?>