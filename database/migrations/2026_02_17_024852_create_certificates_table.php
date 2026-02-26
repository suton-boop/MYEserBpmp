<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('certificates')) {
            return;
        }

        Schema::create('certificates', function (Blueprint $table) {
            // PK utama: mengikuti aplikasi awal (bigint auto increment)
            $table->id();

            // Relasi ke event & peserta (aplikasi awal)
            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();

            $table->foreignId('participant_id')
                ->constrained('participants')
                ->cascadeOnDelete();

            // Nomor sertifikat (aplikasi awal)
            $table->string('certificate_number')->nullable()->index();

            // (Opsional) tambahan dari versi baru: certificate_no (nomor resmi)
            // Jika kamu ingin tetap pakai certificate_number saja, kamu boleh hapus kolom ini.
            $table->string('certificate_no')->nullable()->unique();

            // Judul sertifikat (untuk kebutuhan TTE / metadata)
            $table->string('title')->nullable();

            // Data pemilik (opsional untuk verifikasi publik)
            $table->string('owner_name')->nullable();
            $table->string('owner_identifier')->nullable();

            // Lokasi file PDF (dipakai bersama: e-sertifikat + TTE)
            $table->string('pdf_path')->nullable();

            // CHECKSUM integritas PDF (TTE)
            $table->char('pdf_checksum', 64)->nullable()->index(); // SHA256 HEX

            // Token verifikasi publik (aplikasi awal)
            $table->uuid('verify_token')->unique();

            /**
             * Status flow gabungan (e-sertifikat + TTE)
             * e-sertifikat: draft | generated | sent
             * TTE: draft | generated | reviewed | approved | signed | archived
             *
             * NOTE: status string agar fleksibel (tidak enum) supaya kompatibel.
             */
            $table->string('status')->default('draft')->index();

            // Timestamps proses (gabungan)
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('sent_at')->nullable();     // dari e-sertifikat lama

            // TTE workflow timestamps
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('archived_at')->nullable();

            // Approval workflow TTE
            $table->unsignedInteger('approval_level_required')->default(1);
            $table->unsignedInteger('approval_level_current')->default(0);

            // User pembuat record (TTE) - optional jika kamu sudah punya created_by di sistem lama
            $table->foreignId('created_by')->nullable()->constrained('users');

            $table->timestamps();

            // 1 peserta hanya boleh 1 sertifikat per event
            $table->unique(['event_id', 'participant_id'], 'uniq_cert_event_participant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};