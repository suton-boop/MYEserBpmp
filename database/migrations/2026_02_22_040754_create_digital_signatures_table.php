<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_signatures', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ✅ certificates.id = bigint
            $table->foreignId('certificate_id')
                ->constrained('certificates')
                ->cascadeOnDelete();

            // ✅ signer_certificates.id = uuid
            $table->uuid('signer_certificate_id')->index();

            $table->char('document_hash', 64);           // SHA256 hex
            $table->longText('signature_base64');        // RSA signature base64
            $table->string('signature_algo')->default('RSA-SHA256');

            // TSA internal (opsional)
            $table->boolean('tsa_enabled')->default(false);
            $table->timestamp('tsa_at')->nullable();
            $table->string('tsa_nonce')->nullable()->index();
            $table->longText('tsa_signature_base64')->nullable();
            $table->string('tsa_signer_code')->nullable();

            // Token publik untuk verifikasi record signature
            $table->string('public_token')->unique();

            $table->timestamp('signed_at');

            $table->foreignId('signed_by')->constrained('users');
            $table->string('signed_ip', 64)->nullable();
            $table->string('signed_user_agent', 255)->nullable();

            $table->timestamps();

            // FK ke signer_certificates (uuid)
            $table->foreign('signer_certificate_id')
                ->references('id')
                ->on('signer_certificates')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_signatures');
    }
};