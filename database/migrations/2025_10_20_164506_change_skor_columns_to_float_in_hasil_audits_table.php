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
        Schema::table('hasil_audits', function (Blueprint $table) {
            $table->float('skor_auditee', 8, 2)->nullable()->change();
            $table->float('skor_final', 8, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_audits', function (Blueprint $table) {
            $table->tinyInteger('skor_auditee')->nullable()->change();
            $table->tinyInteger('skor_final')->nullable()->change();
        });
    }
};
