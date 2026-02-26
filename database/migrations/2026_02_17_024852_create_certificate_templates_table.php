<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->unique(); // contoh: P01, P02, dll

            // INI yang bikin error kamu: wajib ada
            $table->string('file_path')->nullable();

            $table->boolean('is_active')->default(true);

            $table->json('settings')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            // optional FK:
            // $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};
