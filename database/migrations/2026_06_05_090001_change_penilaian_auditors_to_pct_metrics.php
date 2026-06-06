<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penilaian_auditors', function (Blueprint $table) {
            $table->dropColumn(['skor_kecepatan', 'skor_ketelitian', 'skor_komunikasi', 'skor_kepatuhan']);
            $table->float('pct_responsivitas')->default(0)->after('audit_periode_id');
            $table->float('avg_hari_respon')->nullable()->after('pct_responsivitas');
            $table->float('pct_kecepatan')->default(0)->after('avg_hari_respon');
            $table->float('pct_catatan')->default(0)->after('pct_kecepatan');
            $table->float('skor_keseluruhan')->default(0)->after('pct_catatan');
        });
    }

    public function down(): void
    {
        Schema::table('penilaian_auditors', function (Blueprint $table) {
            $table->dropColumn(['pct_responsivitas', 'avg_hari_respon', 'pct_kecepatan', 'pct_catatan', 'skor_keseluruhan']);
            $table->tinyInteger('skor_kecepatan')->default(1);
            $table->tinyInteger('skor_ketelitian')->default(1);
            $table->tinyInteger('skor_komunikasi')->default(1);
            $table->tinyInteger('skor_kepatuhan')->default(1);
        });
    }
};
