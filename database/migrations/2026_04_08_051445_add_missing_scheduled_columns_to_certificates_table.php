<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            if (!Schema::hasColumn('certificates', 'scheduled_signer_certificate_id')) {
                $table->foreignId('scheduled_signer_certificate_id')->nullable()->constrained('signer_certificates')->nullOnDelete();
            }
            if (!Schema::hasColumn('certificates', 'scheduled_appearance')) {
                $table->json('scheduled_appearance')->nullable();
            }
            if (!Schema::hasColumn('certificates', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['scheduled_signer_certificate_id']);
            $table->dropColumn([
                'scheduled_signer_certificate_id',
                'scheduled_appearance',
                'scheduled_at'
            ]);
        });
    }
};
