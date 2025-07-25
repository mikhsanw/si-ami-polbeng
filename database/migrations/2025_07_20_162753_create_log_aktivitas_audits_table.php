<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateLogAktivitasAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_aktivitas_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
			$table->string('tipe_aksi')->nullable();
			$table->text('catatan_aksi')->nullable();
			$table->foreignUuid('hasil_audit_id')->nullable()->constrained();
			$table->foreignUuid('user_id')->nullable()->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_aktivitas_audits');
    }
}
