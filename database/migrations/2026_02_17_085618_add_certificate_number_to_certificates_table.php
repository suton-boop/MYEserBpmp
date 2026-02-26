<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            if (!Schema::hasColumn('certificates', 'certificate_number')) {
                $table->string('certificate_number', 100)->nullable()->after('participant_id');
            }

            if (!Schema::hasColumn('certificates', 'year')) {
                $table->integer('year')->nullable()->after('certificate_number');
            }

            if (!Schema::hasColumn('certificates', 'sequence')) {
                $table->integer('sequence')->nullable()->after('year');
            }

            $table->unique(['certificate_number']);
            $table->index(['year', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropUnique(['certificate_number']);
            $table->dropIndex(['year', 'sequence']);

            $table->dropColumn(['certificate_number', 'year', 'sequence']);
        });
    }
};
