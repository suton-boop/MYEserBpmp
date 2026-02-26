<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('event_type');
            $table->uuid('subject_id')->nullable()->index();
            $table->string('subject_type')->nullable();

            $table->foreignId('actor_id')->nullable()->constrained('users');

            $table->string('actor_ip', 64)->nullable();
            $table->string('actor_user_agent', 255)->nullable();

            $table->json('metadata')->nullable();

            // Tamper-evident chain
            $table->char('prev_hash', 64)->nullable();
            $table->char('hash', 64)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};