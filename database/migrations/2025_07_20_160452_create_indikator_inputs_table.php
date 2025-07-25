<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateIndikatorInputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indikator_inputs', function (Blueprint $table) {
            $table->uuid('id')->primary();
			$table->string('nama_variable')->nullable();
			$table->string('label_input')->nullable();
			$table->string('tipe_data',20)->nullable();
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
        Schema::dropIfExists('indikator_inputs');
    }
}
