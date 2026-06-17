@extends('layouts.app')
@section('title', 'TTE strict rules')

@section('content')
<div class="row pt-2 pb-4">
    <div class="col-12 mt-2">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="page-title text-dark fw-bold mb-1">Pengaturan Sistem</h4>
                <div class="page-subtitle small">Kelola pengaturan global aplikasi (Super Admin)</div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="card card-soft mb-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.system.settings.update') }}" method="POST">
                    @csrf

                    <h5 class="fw-bold text-dark mb-4">Pengaturan Tanda Tangan Elektronik</h5>

                    <div class="mb-4">
                        <div class="form-check form-switch fs-5 mb-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="strictTteDate" name="strict_tte_date" value="1" {{ $strictTteDate ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="strictTteDate">
                                Validasi Ketat Tanggal TTE
                            </label>
                        </div>
                        <div class="form-text ms-1 mt-0 mb-3" style="max-width: 600px;">
                            Jika diaktifkan, sertifikat <strong>tidak akan bisa di-TTE</strong> sebelum tanggal yang tertera pada sertifikat tersebut terlewati. 
                            (Tanggal hari ini harus &ge; Tanggal jadwal sertifikat). Menonaktifkan fitur ini akan memperbolehkan sertifikat di-TTE mendahului/sebelum acara.
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch fs-5 mb-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="mediumTteDate" name="medium_tte_date" value="1" {{ $mediumTteDate ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="mediumTteDate">
                                Validasi Sedang Tanggal TTE
                            </label>
                        </div>
                        <div class="form-text ms-1 mt-0" style="max-width: 600px;">
                            Jika diaktifkan, sertifikat <strong>hanya bisa di-TTE tepat pada hari H</strong> kegiatan/jadwal sertifikat.
                            (Tanggal hari ini harus sama dengan Tanggal jadwal sertifikat). Ketika Validasi Ketat aktif, Validasi Sedang otomatis dinonaktifkan.
                        </div>
                    </div>
                    
                    <hr class="text-muted opacity-25">

                    <h5 class="fw-bold text-dark mb-4">Pengaturan Generator Nomor</h5>

                    <div class="mb-4">
                        <div class="form-check form-switch fs-5 mb-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="reuseDeletedNumbers" name="reuse_deleted_numbers" value="1" {{ $reuseDeletedNumbers ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="reuseDeletedNumbers">
                                Daur Ulang Nomor Urut Sertifikat
                            </label>
                        </div>
                        <div class="form-text ms-1 mt-0 mb-3" style="max-width: 600px;">
                            Jika diaktifkan, sistem akan mencari dan menggunakan kembali nomor urut sertifikat yang kosong 
                            (misalnya karena sertifikat sebelumnya dihapus akibat duplikasi). Jika dinonaktifkan, nomor urut akan selalu berlanjut dari nomor terakhir.
                        </div>

                        <!-- Cek Nomor Bolong -->
                        <div class="ms-1 p-3 bg-light rounded border" style="max-width: 600px;">
                            <h6 class="fw-bold mb-2"><i class="fa-solid fa-magnifying-glass-chart text-primary me-2"></i>Status Nomor Urut Sertifikat</h6>
                            <div class="small text-muted mb-2">Total maksimum sequence saat ini: <strong>{{ $maxSequence }}</strong></div>
                            @if(count($missingSequences) > 0)
                                <div class="alert alert-warning py-2 px-3 mb-0 small border-warning">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Terdapat <strong>{{ count($missingSequences) }}</strong> nomor urut yang kosong (belum terpakai/terhapus):
                                    <div class="mt-2 d-flex flex-wrap gap-1">
                                        @foreach($missingSequences as $num)
                                            <span class="badge bg-warning text-dark">{{ $num }}</span>
                                        @endforeach
                                    </div>
                                    <div class="mt-2 text-muted" style="font-size: 0.8rem;">
                                        <em>Nomor-nomor ini otomatis akan digunakan kembali untuk sertifikat baru jika fitur "Daur Ulang" di atas aktif.</em>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-success py-2 px-3 mb-0 small border-success">
                                    <i class="fa-solid fa-check-circle me-1"></i> Luar biasa! Tidak ada nomor urut yang bolong atau terbuang. Semua berjalan berurutan dengan sempurna.
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr class="text-muted opacity-25">

                    <h5 class="fw-bold text-dark mb-4">Pengaturan Impor & Penambahan Peserta</h5>

                    <div class="mb-4">
                        <div class="form-check form-switch fs-5 mb-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="allowDuplicateParticipants" name="allow_duplicate_participants" value="1" {{ $allowDuplicateParticipants ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="allowDuplicateParticipants">
                                Izinkan Peserta Duplikat (Nama & Email Sama)
                            </label>
                        </div>
                        <div class="form-text ms-1 mt-0 mb-3" style="max-width: 600px;">
                            Jika diaktifkan, sistem akan mengizinkan penambahan atau impor peserta dengan NIK atau kombinasi Nama dan Email yang sama persis di dalam satu event yang sama (berguna jika peserta tersebut menjadi narasumber/peserta di 2 angkatan yang digabung dalam 1 event).
                        </div>
                    </div>

                    <hr class="text-muted opacity-25">

                    <h5 class="fw-bold text-dark mb-4">Pengaturan Font Sertifikat</h5>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Font Nama Peserta</label>
                            <select name="font_name_participant" class="form-select">
                                <option value="'Great Vibes', cursive" {{ $fontNameParticipant == "'Great Vibes', cursive" ? 'selected' : '' }}>Great Vibes (Tegak Bersambung Halus)</option>
                                <option value="'Alex Brush', cursive" {{ $fontNameParticipant == "'Alex Brush', cursive" ? 'selected' : '' }}>Alex Brush (Tegak Bersambung Tegas)</option>
                                <option value="'Times-Roman', serif" {{ $fontNameParticipant == "'Times-Roman', serif" ? 'selected' : '' }}>Times New Roman (Standar Formal)</option>
                                <option value="'Helvetica', sans-serif" {{ $fontNameParticipant == "'Helvetica', sans-serif" ? 'selected' : '' }}>Helvetica / Arial (Standar Tegak Modern)</option>
                            </select>
                            <div class="form-text">Pilih jenis huruf (font) yang akan digunakan untuk mencetak Nama Peserta di PDF.</div>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <label class="form-label fw-semibold">Font Peran (Sebagai)</label>
                            <select name="font_role_participant" class="form-select">
                                <option value="'Alex Brush', cursive" {{ $fontRoleParticipant == "'Alex Brush', cursive" ? 'selected' : '' }}>Alex Brush (Tegak Bersambung Tegas)</option>
                                <option value="'Great Vibes', cursive" {{ $fontRoleParticipant == "'Great Vibes', cursive" ? 'selected' : '' }}>Great Vibes (Tegak Bersambung Halus)</option>
                                <option value="'Times-Roman', serif" {{ $fontRoleParticipant == "'Times-Roman', serif" ? 'selected' : '' }}>Times New Roman (Standar Formal)</option>
                                <option value="'Helvetica', sans-serif" {{ $fontRoleParticipant == "'Helvetica', sans-serif" ? 'selected' : '' }}>Helvetica / Arial (Standar Tegak Modern)</option>
                            </select>
                            <div class="form-text">Pilih jenis huruf (font) yang akan digunakan untuk mencetak Peran (misal: Peserta, Narasumber).</div>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25">

                    <h5 class="fw-bold text-dark mb-4 text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Pengecekan Selisih Data (Anomali)</h5>
                    <div class="mb-4">
                        <div class="form-text ms-1 mt-0 mb-3" style="max-width: 800px;">
                            Bagian ini mendeteksi sertifikat yang menyebabkan <strong>selisih jumlah</strong> pada Laporan Dashboard. 
                            Sertifikat yang masuk ke sini adalah yang berstatus selain <em>Draft, Waiting Approval, TTE Ready, atau Final (Terbit)</em>, 
                            seperti sertifikat yang <strong>Ditolak (Rejected)</strong>, menyangkut saat proses PDF <strong>(Generating)</strong>, 
                            atau <strong>Menunggu Jadwal (Scheduled)</strong>.
                        </div>

                        <div class="ms-1 p-3 bg-light rounded border" style="max-width: 800px;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0">Total Selisih Ditemukan: <span class="badge {{ count($anomalyCerts) > 0 ? 'bg-danger' : 'bg-success' }} fs-6">{{ count($anomalyCerts) }}</span></h6>
                            </div>

                            @if(count($anomalyCerts) > 0)
                                <div class="table-responsive bg-white rounded border">
                                    <table class="table table-hover table-sm mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3 py-2">ID</th>
                                                <th class="py-2">Nama Peserta</th>
                                                <th class="py-2">Event</th>
                                                <th class="py-2">Status</th>
                                                <th class="py-2 text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($anomalyCerts as $cert)
                                            <tr>
                                                <td class="ps-3">{{ $cert->id }}</td>
                                                <td>{{ $cert->participant->name ?? 'N/A' }}</td>
                                                <td>
                                                    @if($cert->event)
                                                        <span class="d-inline-block text-truncate" style="max-width: 150px;" title="{{ $cert->event->name }}">
                                                            {{ $cert->event->name }}
                                                        </span>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-danger text-uppercase">{{ $cert->status }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('admin.participants.edit', $cert->participant_id ?? 0) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Cek Peserta">
                                                        <i class="fa-solid fa-search"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-success py-2 px-3 mb-0 small border-success">
                                    <i class="fa-solid fa-check-circle me-1"></i> Luar biasa! Tidak ada selisih data sertifikat di sistem saat ini.
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr class="text-muted opacity-25">

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary btn-icon px-4">
                            <i class="fa-solid fa-save"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const strictSwitch = document.getElementById('strictTteDate');
    const mediumSwitch = document.getElementById('mediumTteDate');

    function handleStrictChange() {
        if (strictSwitch.checked) {
            mediumSwitch.checked = false;
            mediumSwitch.disabled = true;
        } else {
            mediumSwitch.disabled = false;
        }
    }

    if (strictSwitch && mediumSwitch) {
        strictSwitch.addEventListener('change', handleStrictChange);
        handleStrictChange(); // Inisialisasi awal saat halaman dimuat
    }
});
</script>
@endsection
