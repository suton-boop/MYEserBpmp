<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // jika kolom sudah ada, jangan ditambah lagi (aman)
            if (!Schema::hasColumn('events', 'certificate_template_id')) {
                $table->foreignId('certificate_template_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('certificate_templates')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'certificate_template_id')) {
                // drop FK dulu baru drop kolom
                $table->dropForeign(['certificate_template_id']);
                $table->dropColumn('certificate_template_id');
            }
        });
    }
};
