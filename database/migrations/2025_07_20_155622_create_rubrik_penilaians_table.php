<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateRubrikPenilaiansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rubrik_penilaians', function (Blueprint $table) {
            $table->uuid('id')->primary();
			$table->tinyInteger('skor')->nullable();
			$table->text('deskripsi')->nullable();
			$table->text('formula_kondisi')->nullable();
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
        Schema::dropIfExists('rubrik_penilaians');
    }
}
