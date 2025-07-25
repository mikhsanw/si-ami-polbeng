<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateDataAuditInputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_audit_inputs', function (Blueprint $table) {
            $table->uuid('id')->primary();
			$table->string('nilai_variable')->nullable();
			$table->foreignUuid('hasil_audit_id')->nullable()->constrained();
			$table->foreignUuid('indikator_input_id')->nullable()->constrained();
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
        Schema::dropIfExists('data_audit_inputs');
    }
}
