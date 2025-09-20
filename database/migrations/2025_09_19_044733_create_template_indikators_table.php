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
        Schema::create('template_indikators', function (Blueprint $table) {
            $table->uuid('id')->primary();
			$table->decimal('bobot', 5, 2)->nullable();
			$table->foreignUuid('instrumen_template_id')->nullable()->constrained();
			$table->foreignUuid('indikator_id')->nullable()->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_indikators');
    }
};
