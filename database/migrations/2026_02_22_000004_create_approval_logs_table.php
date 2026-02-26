<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ✅ FIX: certificates.id adalah BIGINT -> gunakan foreignId
            $table->foreignId('certificate_id')
                ->constrained('certificates')
                ->cascadeOnDelete();

            $table->unsignedInteger('level');
            $table->enum('action', ['review', 'approve', 'reject']);
            $table->text('note')->nullable();

            $table->foreignId('acted_by')->constrained('users');

            $table->string('acted_ip', 64)->nullable();
            $table->string('acted_user_agent', 255)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_logs');
    }
};