<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            if (!Schema::hasColumn('certificates', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('status');
                // optional FK kalau mau
                // $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            if (Schema::hasColumn('certificates', 'created_by')) {
                // optional drop FK kalau dibuat
                // $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};
