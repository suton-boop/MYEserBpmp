<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('signer_certificates', function (Blueprint $table) {
            // Jika kolom lama 'public_key' ada, ganti ke 'public_key_pem'
            if (Schema::hasColumn('signer_certificates', 'public_key')) {
                $table->renameColumn('public_key', 'public_key_pem');
            }
            else if (!Schema::hasColumn('signer_certificates', 'public_key_pem')) {
                $table->longText('public_key_pem')->after('name')->nullable();
            }

            // Jika kolom lama 'fingerprint' ada, ganti ke 'private_key_fingerprint'
            if (Schema::hasColumn('signer_certificates', 'fingerprint')) {
                $table->renameColumn('fingerprint', 'private_key_fingerprint');
            }
            else if (!Schema::hasColumn('signer_certificates', 'private_key_fingerprint')) {
                $table->string('private_key_fingerprint')->after('public_key_pem')->nullable();
            }

            // Tambahkan kolom audit jika belum ada
            if (!Schema::hasColumn('signer_certificates', 'rotated_from_id')) {
                $table->uuid('rotated_from_id')->nullable()->after('valid_to');
            }
            if (!Schema::hasColumn('signer_certificates', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('rotated_from_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('signer_certificates', function (Blueprint $table) {
            if (Schema::hasColumn('signer_certificates', 'public_key_pem')) {
                $table->renameColumn('public_key_pem', 'public_key');
            }
            if (Schema::hasColumn('signer_certificates', 'private_key_fingerprint')) {
                $table->renameColumn('private_key_fingerprint', 'fingerprint');
            }
            $table->dropColumn(['rotated_from_id', 'created_by']);
        });
    }
};
