<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('email')->nullable();
            $table->string('nik')->nullable();
            $table->string('institution')->nullable();

            // draft | terbit
            $table->string('status')->default('draft')->index();

            $table->timestamps();

            $table->index(['event_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
