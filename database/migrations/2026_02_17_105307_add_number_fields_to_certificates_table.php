<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            // kolom untuk nomor berurutan per tahun
            if (!Schema::hasColumn('certificates', 'year')) {
                $table->unsignedSmallInteger('year')->nullable()->after('certificate_number')->index();
            }

            if (!Schema::hasColumn('certificates', 'sequence')) {
                $table->unsignedInteger('sequence')->nullable()->after('year')->index();
            }

            // pencatat siapa yang generate (optional tapi berguna)
            if (!Schema::hasColumn('certificates', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('participant_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            if (Schema::hasColumn('certificates', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('certificates', 'sequence')) {
                $table->dropColumn('sequence');
            }
            if (Schema::hasColumn('certificates', 'year')) {
                $table->dropColumn('year');
            }
        });
    }
};
