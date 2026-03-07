<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ParticipantsTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'name',
            'email',
            'nik',
            'institution',
            'status',
            'daerah',
            'jenjang',
            'peran',
            'keterangan',
            'tanggal_kunjungan'
        ];
    }

    public function array(): array
    {
        return [
            ['Budi Santoso', 'budi@email.com', '6401xxxxxxxxxxxx', 'SMPN 1 Samarinda', 'draft', 'Samarinda', 'SMP', 'Peserta', '-', '2026-03-08'],
            ['Siti Aminah', 'siti@email.com', '6401xxxxxxxxxxxx', 'BPMP Kaltim', 'terbit', 'Balikpapan', 'SMA', 'Panitia', '-', '2026-03-09']
        ];
    }
}
