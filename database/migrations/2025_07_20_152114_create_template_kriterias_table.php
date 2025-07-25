<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTemplateKriteriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_kriterias', function (Blueprint $table) {
            $table->uuid('id')->primary();
			$table->decimal('bobot', 5, 2)->nullable();
			$table->foreignUuid('instrumen_template_id')->nullable()->constrained();
			$table->foreignUuid('kriteria_id')->nullable()->constrained();
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
        Schema::dropIfExists('template_kriterias');
    }
}
