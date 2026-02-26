<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_signatures', function (Blueprint $table) {
            // NOTE: kolom baru untuk tampilan signature (appearance)

            if (!Schema::hasColumn('digital_signatures', 'appearance_mode')) {
                $table->enum('appearance_mode', ['visible', 'hidden'])
                    ->default('visible')
                    ->after('signature_algo');
            }

            if (!Schema::hasColumn('digital_signatures', 'appearance_page')) {
                $table->unsignedInteger('appearance_page')
                    ->default(1) // halaman 1 (1-based)
                    ->after('appearance_mode');
            }

            if (!Schema::hasColumn('digital_signatures', 'appearance_x')) {
                $table->unsignedInteger('appearance_x')
                    ->default(30) // mm/point tergantung engine PDF kamu
                    ->after('appearance_page');
            }

            if (!Schema::hasColumn('digital_signatures', 'appearance_y')) {
                $table->unsignedInteger('appearance_y')
                    ->default(30)
                    ->after('appearance_x');
            }

            if (!Schema::hasColumn('digital_signatures', 'appearance_w')) {
                $table->unsignedInteger('appearance_w')
                    ->default(160)
                    ->after('appearance_y');
            }

            if (!Schema::hasColumn('digital_signatures', 'appearance_h')) {
                $table->unsignedInteger('appearance_h')
                    ->default(50)
                    ->after('appearance_w');
            }

            if (!Schema::hasColumn('digital_signatures', 'appearance_reason')) {
                $table->string('appearance_reason', 255)
                    ->nullable()
                    ->after('appearance_h');
            }

            if (!Schema::hasColumn('digital_signatures', 'appearance_location')) {
                $table->string('appearance_location', 255)
                    ->nullable()
                    ->after('appearance_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('digital_signatures', function (Blueprint $table) {
            $cols = [
                'appearance_mode',
                'appearance_page',
                'appearance_x',
                'appearance_y',
                'appearance_w',
                'appearance_h',
                'appearance_reason',
                'appearance_location',
            ];

            foreach ($cols as $c) {
                if (Schema::hasColumn('digital_signatures', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};