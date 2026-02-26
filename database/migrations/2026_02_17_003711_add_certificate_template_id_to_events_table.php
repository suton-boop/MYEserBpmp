<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'certificate_template_id')) {
                $table->foreignId('certificate_template_id')
                    ->nullable()
                    ->after('description')
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
                try { $table->dropForeign(['certificate_template_id']); } catch (\Throwable $e) {}
                $table->dropColumn('certificate_template_id');
            }
        });
    }
};
