<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('indikators', function (Blueprint $table) {
            $table->text('formula_penilaian')
                ->nullable()
                ->after('tipe')
                ->comment('Rumus untuk menghitung skor otomatis dari input LKPS (contoh: (A/B)*100)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indikators', function (Blueprint $table) {
            $table->dropColumn('formula_penilaian');
        });
    }
};
