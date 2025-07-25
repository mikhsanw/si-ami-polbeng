<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateHasilAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hasil_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
			$table->tinyInteger('skor_auditee')->nullable();
			$table->tinyInteger('skor_final')->nullable();
			$table->text('catatan_final')->nullable();
			$table->string('status_terkini')->nullable();
			$table->foreignUuid('audit_periode_id')->nullable()->constrained();
			$table->foreignUuid('indikator_id')->nullable()->constrained();
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
        Schema::dropIfExists('hasil_audits');
    }
}
