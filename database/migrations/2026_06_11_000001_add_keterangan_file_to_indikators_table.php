<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKeteranganFileToIndikatorsTable extends Migration
{
    public function up()
    {
        Schema::table('indikators', function (Blueprint $table) {
            $table->text('keterangan_file')->nullable()->after('formula_penilaian');
        });
    }

    public function down()
    {
        Schema::table('indikators', function (Blueprint $table) {
            $table->dropColumn('keterangan_file');
        });
    }
}