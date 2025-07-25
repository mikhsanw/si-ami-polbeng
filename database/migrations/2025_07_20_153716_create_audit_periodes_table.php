<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateAuditPeriodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_periodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
			$table->string('tahun_akademik',50)->nullable();
			$table->string('status',20)->nullable();
			$table->foreignUuid('unit_id')->nullable()->constrained();
			$table->foreignUuid('instrument_template_id')->nullable()->constrained();
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
        Schema::dropIfExists('audit_periodes');
    }
}
