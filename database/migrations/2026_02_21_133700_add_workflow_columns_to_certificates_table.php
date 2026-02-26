<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {

            $table->timestamp('submitted_at')->nullable()->after('status');

            $table->timestamp('approved_at')->nullable()->after('submitted_at');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');

            $table->timestamp('rejected_at')->nullable()->after('approved_by');
            $table->unsignedBigInteger('rejected_by')->nullable()->after('rejected_at');
            $table->text('rejected_note')->nullable()->after('rejected_by');

            $table->string('signed_pdf_path')->nullable()->after('pdf_path');
            $table->timestamp('signed_at')->nullable()->after('signed_pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn([
                'submitted_at',
                'approved_at',
                'approved_by',
                'rejected_at',
                'rejected_by',
                'rejected_note',
                'signed_pdf_path',
                'signed_at',
            ]);
        });
    }
};