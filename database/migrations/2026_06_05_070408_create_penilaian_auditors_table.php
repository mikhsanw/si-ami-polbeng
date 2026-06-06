<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_auditors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('auditor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('penilai_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('audit_periode_id')->constrained('audit_periodes')->cascadeOnDelete();
            $table->tinyInteger('skor_kecepatan');
            $table->tinyInteger('skor_ketelitian');
            $table->tinyInteger('skor_komunikasi');
            $table->tinyInteger('skor_kepatuhan');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_auditors');
    }
};
